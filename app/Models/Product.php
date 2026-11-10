<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

#[Fillable(['seller_id', 'category_id', 'name', 'slug', 'description', 'unit', 'price', 'stock', 'status', 'moderation_note'])]
class Product extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'moderation_note' => 'encrypted'];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function moderationEvents(): HasMany
    {
        return $this->hasMany(ProductModerationEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function isPubliclyAvailable(): bool
    {
        return $this->isPubliclyVisible()
            && $this->stock > 0;
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, ['active', 'out_of_stock'], true)
            && $this->seller?->verification_status === 'approved'
            && $this->seller?->user?->account_status === 'active'
            && (bool) $this->category?->is_active;
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            if ($product->orderItems()->exists()) {
                throw new LogicException('Products referenced by order history cannot be deleted. Archive the product instead.');
            }
        });
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
