<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ResidentRegionScope
{
    public function queryFor(User $user): Builder
    {
        $query = Resident::query()->whereNotNull('region_verified_at');
        $regionIds = $user->regionOfficerAssignments()->where('is_active', true)->pluck('region_id');

        return $query->where(function (Builder $residentQuery) use ($user, $regionIds): void {
            if ($user->hasRole('RT')) {
                $residentQuery->orWhereIn('region_id', function ($assigned) use ($regionIds): void {
                    $assigned->select('id')->from('regions')->whereIn('id', $regionIds)->where('region_type', 'rt');
                });
            }

            if ($user->hasRole('RW')) {
                $residentQuery->orWhereIn('region_id', function ($children) use ($regionIds): void {
                    $children->select('child.id')->from('regions as child')
                        ->join('regions as parent', 'parent.id', '=', 'child.parent_id')
                        ->where('child.region_type', 'rt')
                        ->where('parent.region_type', 'rw')
                        ->whereColumn('child.village_id', 'parent.village_id')
                        ->whereIn('parent.id', $regionIds);
                });
            }
        });
    }
}
