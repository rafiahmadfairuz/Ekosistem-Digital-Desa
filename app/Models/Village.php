<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'address', 'postal_code', 'phone', 'email', 'is_active'])]
class Village extends Model
{
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
