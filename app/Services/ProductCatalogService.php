<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductModerationEvent;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductCatalogService
{
    public function __construct(private readonly AuditLogger $audit, private readonly MarketplaceNotifier $notifier) {}

    /** @param array{name: string, description: string, category_id: int, unit: string, price: string|int, stock: int} $data
     * @param  list<UploadedFile>  $images
     */
    public function create(User $actor, array $data, array $images): Product
    {
        abort_unless($actor->can('create', Product::class), 403);
        $seller = $actor->seller;
        abort_unless($seller instanceof Seller && $seller->verification_status === 'approved', 403);
        $category = ProductCategory::query()->whereKey($data['category_id'])->where('is_active', true)->firstOrFail();

        return DB::transaction(function () use ($actor, $seller, $category, $data, $images): Product {
            $product = Product::query()->create([
                'seller_id' => $seller->getKey(), 'category_id' => $category->getKey(),
                'name' => trim($data['name']), 'slug' => $this->uniqueSlug($data['name']),
                'description' => trim($data['description']), 'unit' => trim($data['unit']),
                'price' => $data['price'], 'stock' => (int) $data['stock'], 'status' => 'pending_review',
            ]);
            $this->storeImages($product, $images);
            ProductModerationEvent::query()->create([
                'product_id' => $product->getKey(), 'actor_id' => $actor->getKey(),
                'from_status' => null, 'to_status' => 'pending_review', 'created_at' => now(),
            ]);
            $this->audit->record('marketplace.product_submitted', 'product', (int) $product->getKey());
            $this->notifier->toActiveMarketplaceReviewers('Produk menunggu pemeriksaan', 'Ada produk baru yang menunggu moderasi.', 'product', (int) $product->getKey());

            return $product;
        });
    }

    /** @param array{name: string, description: string, category_id: int, unit: string, price: string|int, stock: int} $data
     * @param  list<UploadedFile>  $images
     */
    public function update(User $actor, Product $product, array $data, array $images): Product
    {
        abort_unless($actor->can('update', $product), 403);
        $category = ProductCategory::query()->whereKey($data['category_id'])->where('is_active', true)->firstOrFail();

        return DB::transaction(function () use ($actor, $product, $category, $data, $images): Product {
            $locked = Product::query()->whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->seller?->user_id === (int) $actor->getKey(), 403);
            $fromStatus = $locked->status;
            $locked->forceFill([
                'category_id' => $category->getKey(), 'name' => trim($data['name']),
                'slug' => $locked->name === trim($data['name']) ? $locked->slug : $this->uniqueSlug($data['name'], (int) $locked->getKey()),
                'description' => trim($data['description']), 'unit' => trim($data['unit']),
                'price' => $data['price'], 'stock' => (int) $data['stock'], 'status' => 'pending_review',
                'moderation_note' => null,
            ])->save();
            $this->storeImages($locked, $images);
            ProductModerationEvent::query()->create([
                'product_id' => $locked->getKey(), 'actor_id' => $actor->getKey(),
                'from_status' => $fromStatus, 'to_status' => 'pending_review', 'created_at' => now(),
            ]);
            $this->audit->record('marketplace.product_resubmitted', 'product', (int) $locked->getKey());

            return $locked;
        });
    }

    public function moderate(User $actor, Product $product, string $status, ?string $reason): Product
    {
        abort_unless($actor->can('moderate', Product::class), 403);
        if ($status === 'rejected' && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan produk wajib diisi.']);
        }

        return DB::transaction(function () use ($actor, $product, $status, $reason): Product {
            $locked = Product::query()->whereKey($product->getKey())->lockForUpdate()->with(['seller', 'category'])->firstOrFail();
            $from = $locked->status;
            $allowed = match ($from) {
                'pending_review' => ['active', 'rejected', 'inactive'],
                'active', 'out_of_stock' => ['inactive'],
                'inactive', 'rejected' => ['pending_review'],
                default => [],
            };
            abort_unless(in_array($status, $allowed, true), 409, 'Perubahan status produk tidak diizinkan.');
            if ($status === 'active') {
                abort_unless($locked->seller->verification_status === 'approved' && $locked->category->is_active, 409, 'Penjual dan kategori harus aktif sebelum produk diterbitkan.');
                if ((int) $locked->stock < 1) {
                    throw ValidationException::withMessages(['status' => 'Tambahkan stok sebelum menerbitkan produk.']);
                }
            }
            $locked->forceFill(['status' => $status, 'moderation_note' => $status === 'rejected' ? trim((string) $reason) : null])->save();
            ProductModerationEvent::query()->create([
                'product_id' => $locked->getKey(), 'actor_id' => $actor->getKey(),
                'from_status' => $from, 'to_status' => $status,
                'reason' => $reason === null ? null : trim($reason), 'created_at' => now(),
            ]);
            $this->audit->record('marketplace.product_moderated', 'product', (int) $locked->getKey(), ['from_status' => $from, 'to_status' => $status]);
            $this->notifier->toUser($locked->seller->user, 'Pembaruan produk', $status === 'active'
                ? 'Produk Anda telah disetujui dan diterbitkan.'
                : ($status === 'rejected' ? 'Produk Anda belum disetujui. Periksa catatan moderasi.' : 'Status produk Anda diperbarui.'), 'product', (int) $locked->getKey());

            return $locked;
        });
    }

    public function deleteImage(User $actor, ProductImage $image): void
    {
        $path = DB::transaction(function () use ($actor, $image): string {
            $product = Product::query()->whereKey($image->product_id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->can('update', $product), 403);
            abort_unless($product->status !== 'archived', 409, 'Produk arsip tidak dapat diubah.');
            $lockedImage = ProductImage::query()->whereKey($image->getKey())->where('product_id', $product->getKey())->lockForUpdate()->firstOrFail();
            $path = $lockedImage->private_file_path;
            if (in_array($product->status, ['active', 'out_of_stock'], true)) {
                $oldStatus = $product->status;
                $product->forceFill(['status' => 'pending_review'])->save();
                ProductModerationEvent::query()->create([
                    'product_id' => $product->getKey(), 'actor_id' => $actor->getKey(),
                    'from_status' => $oldStatus, 'to_status' => 'pending_review', 'created_at' => now(),
                ]);
                $this->notifier->toActiveMarketplaceReviewers('Perubahan produk menunggu pemeriksaan', 'Penjual mengubah gambar produk yang telah diterbitkan.', 'product', (int) $product->getKey());
            }
            $lockedImage->delete();
            $this->audit->record('marketplace.product_image_removed', 'product', (int) $product->getKey());

            return $path;
        });
        Storage::disk('local')->delete($path);
    }

    /** @param list<UploadedFile> $images */
    private function storeImages(Product $product, array $images): void
    {
        if ($product->images()->count() + count($images) > 5) {
            throw ValidationException::withMessages(['images' => 'Produk dapat memiliki paling banyak lima gambar.']);
        }
        foreach ($images as $index => $image) {
            if (! $image->isValid() || $image->getSize() > 5 * 1024 * 1024) {
                throw ValidationException::withMessages(["images.{$index}" => 'Ukuran setiap gambar maksimal 5 MB.']);
            }
            $contents = file_get_contents($image->getRealPath());
            $mime = $image->getMimeType();
            if (! is_string($contents) || ! is_string($mime) || ! str_starts_with($mime, 'image/')) {
                throw ValidationException::withMessages(['images' => 'Berkas harus berupa gambar yang valid.']);
            }
            $extension = match ($mime) {
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
                default => throw ValidationException::withMessages(['images' => 'Format gambar tidak didukung.']),
            };
            $path = 'marketplace/products/'.$product->getKey().'/'.Str::random(48).'.'.$extension;
            if (! Storage::disk('local')->put($path, $contents)) {
                throw new RuntimeException('Gambar produk tidak dapat disimpan secara privat.');
            }
            $product->images()->create([
                'private_file_path' => $path,
                'alt_text' => mb_substr($product->name, 0, 180),
                'mime_type' => $mime,
                'file_size' => strlen($contents),
                'file_hash' => hash('sha256', $contents),
                'sort_order' => $product->images()->count() + $index,
            ]);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($name), 150, '').'-'.Str::lower(Str::random(8));
        $query = Product::query()->where('slug', $base);
        if ($ignoreId !== null) {
            $query->where('id', '<>', $ignoreId);
        }

        return $query->exists() ? $this->uniqueSlug($name, $ignoreId) : $base;
    }
}
