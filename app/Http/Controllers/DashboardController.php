<?php

namespace App\Http\Controllers;

use App\Models\RegionOfficer;
use App\Models\Resident;
use App\Models\User;
use App\Services\NavigationMenu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, NavigationMenu $navigationMenu): View
    {
        $user = $request->user();
        $roles = $user->roles()->orderBy('name')->get(['roles.id', 'roles.name']);
        $roleNames = $roles->pluck('name');

        $adminSummary = null;
        if ($user->can('viewAny', User::class)) {
            $adminSummary = [
                'pending_accounts' => User::query()->where('account_status', 'pending')->count(),
                'active_accounts' => User::query()->where('account_status', 'active')->count(),
                'active_assignments' => RegionOfficer::query()->where('is_active', true)->count(),
            ];
        }

        $regionReviewCount = $roleNames->contains('Village Admin')
            ? Resident::query()->whereNotNull('region_id')->whereNull('region_verified_at')->count()
            : 0;

        $assignments = $roleNames->intersect(['RT', 'RW'])->isNotEmpty()
            ? $user->regionOfficerAssignments()
                ->with('region.parent')
                ->where('is_active', true)
                ->orderBy('region_id')
                ->get()
            : collect();

        $residentProfile = $roleNames->contains('Resident')
            ? $user->resident()->with('region')->first()
            : null;
        $navigationGroups = $navigationMenu->for($user);

        return view('dashboard', compact(
            'adminSummary',
            'assignments',
            'navigationGroups',
            'regionReviewCount',
            'residentProfile',
            'roleNames',
            'roles',
        ));
    }
}
