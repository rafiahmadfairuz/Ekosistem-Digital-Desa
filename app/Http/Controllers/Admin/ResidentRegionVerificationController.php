<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResidentRegionVerificationController extends Controller
{
    public function index(AuditLogger $audit): View
    {
        abort_unless(request()->user()->hasRole('Village Admin'), 403);
        $audit->record('resident.region_verification_list_viewed', 'resident_collection');

        return view('admin.resident-region-verification.index', [
            'residents' => Resident::query()->whereNull('region_verified_at')->with(['user:id,username', 'region.parent'])->orderBy('id')->simplePaginate(30),
        ]);
    }

    public function verify(Resident $resident, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('verifyRegion', $resident);
        DB::transaction(function () use ($resident, $audit): void {
            $locked = Resident::query()->whereKey($resident->getKey())->lockForUpdate()->firstOrFail();
            abort_if($locked->region_verified_at !== null, 409);
            $locked->forceFill(['region_verified_at' => now()])->save();
            $audit->record('resident.region_verified', 'resident', (int) $locked->getKey(), ['region_id' => (int) $locked->region_id]);
        });

        return back()->with('status', 'Wilayah diverifikasi. Petugas RT/RW yang ditugaskan dapat melihat data terbatas sesuai cakupan.');
    }
}
