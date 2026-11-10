<?php

namespace App\Policies;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ResidentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Village Admin')) {
            return true;
        }

        $assignments = $user->regionOfficerAssignments()->where('is_active', true);

        return ($user->hasRole('RT') && (clone $assignments)->whereHas('region', fn (Builder $query): Builder => $query->where('region_type', 'rt'))->exists())
            || ($user->hasRole('RW') && $assignments->whereHas('region', fn (Builder $query): Builder => $query->where('region_type', 'rw'))->exists());
    }

    public function view(User $user, Resident $resident): bool
    {
        if ((int) $user->getKey() === (int) $resident->user_id) {
            return true;
        }

        if ($resident->region_verified_at === null) {
            return false;
        }

        $residentRegion = $resident->region;

        if ($residentRegion === null) {
            return false;
        }

        $canReviewRtRegion = $user->hasRole('RT')
            && $user->regionOfficerAssignments()
                ->where('is_active', true)
                ->where('region_id', $residentRegion->getKey())
                ->whereHas('region', fn (Builder $query): Builder => $query->where('region_type', 'rt'))
                ->exists();

        $canReviewRwRegion = $user->hasRole('RW')
            && $residentRegion->parent_id !== null
            && $user->regionOfficerAssignments()
                ->where('is_active', true)
                ->where('region_id', $residentRegion->parent_id)
                ->whereHas('region', fn (Builder $query): Builder => $query
                    ->where('region_type', 'rw')
                    ->where('village_id', $residentRegion->village_id))
                ->exists();

        return $canReviewRtRegion || $canReviewRwRegion;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Resident') && ! $user->resident()->exists();
    }

    public function update(User $user, Resident $resident): bool
    {
        return (int) $user->getKey() === (int) $resident->user_id && $user->hasRole('Resident');
    }

    public function delete(User $user, Resident $resident): bool
    {
        return false;
    }

    public function restore(User $user, Resident $resident): bool
    {
        return false;
    }

    public function forceDelete(User $user, Resident $resident): bool
    {
        return false;
    }

    public function verifyRegion(User $user, Resident $resident): bool
    {
        return $user->hasRole('Village Admin') && $resident->region_verified_at === null;
    }
}
