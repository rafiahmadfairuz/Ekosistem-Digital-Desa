<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['village_id', 'parent_id', 'region_type', 'region_code', 'name', 'description'])]
class Region extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Region $region): void {
            $region->code_scope_id = $region->region_type === 'rt'
                ? (int) $region->parent_id
                : 0;
        });
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function officerAssignments(): HasMany
    {
        return $this->hasMany(RegionOfficer::class);
    }
}
