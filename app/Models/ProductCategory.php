<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable(['name', 'slug', 'description', 'is_active'])]
class ProductCategory extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (ProductCategory $category): void {
            if ($category->products()->exists()) {
                throw new LogicException('A category used by products cannot be deleted. Deactivate it instead.');
            }
        });
    }
}
