<?php

namespace App\Policies;

use App\Models\LetterDocument;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;

class LetterRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Resident')
            || (($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && $user->bumdesStaff()->where('employment_status', 'active')->exists())
            || $user->can('viewAny', Resident::class)
            || $user->hasRole('Village Head');
    }

    public function view(User $user, LetterRequest $letterRequest): bool
    {
        if ((int) $letterRequest->resident?->user_id === (int) $user->getKey()) {
            return $user->hasRole('Resident');
        }

        if (($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager'))
            && $user->bumdesStaff()->where('employment_status', 'active')->exists()
            && (int) $letterRequest->created_by === (int) $user->getKey()
            && $letterRequest->submission_channel === 'assisted_bumdes') {
            return true;
        }

        if ($user->hasRole('Village Admin') && in_array($letterRequest->status, ['village_review', 'signature_pending', 'completed', 'rejected'], true)) {
            return $letterRequest->resident?->region_verified_at !== null;
        }

        if ($user->hasRole('Village Head') && in_array($letterRequest->status, ['signature_pending', 'completed', 'rejected'], true)) {
            return true;
        }

        if ($letterRequest->status === 'rt_review' && $user->hasRole('RT')) {
            return $user->can('view', $letterRequest->resident);
        }

        if ($letterRequest->status === 'rw_review' && $user->hasRole('RW')) {
            return $user->can('view', $letterRequest->resident);
        }

        if ($user->hasRole('RT') && $user->can('view', $letterRequest->resident)
            && $letterRequest->approvals()->where('reviewer_id', $user->getKey())->where('approval_level', 'rt')->exists()) {
            return true;
        }

        if ($user->hasRole('RW') && $user->can('view', $letterRequest->resident)
            && $letterRequest->approvals()->where('reviewer_id', $user->getKey())->where('approval_level', 'rw')->exists()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Resident')
            || (($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && $user->bumdesStaff()->where('employment_status', 'active')->exists());
    }

    public function update(User $user, LetterRequest $letterRequest): bool
    {
        return $user->hasRole('Resident')
            && (int) $letterRequest->resident?->user_id === (int) $user->getKey()
            && $letterRequest->status === 'revision_required';
    }

    public function review(User $user, LetterRequest $letterRequest): bool
    {
        return $this->view($user, $letterRequest)
            && (($letterRequest->status === 'rt_review' && $user->hasRole('RT'))
                || ($letterRequest->status === 'rw_review' && $user->hasRole('RW'))
                || ($letterRequest->status === 'village_review' && $user->hasRole('Village Admin')));
    }

    public function sign(User $user, LetterRequest $letterRequest): bool
    {
        return $user->hasRole('Village Head')
            && $letterRequest->status === 'signature_pending';
    }

    public function manageTypes(User $user): bool
    {
        return $user->hasRole('Village Admin');
    }

    public function download(User $user, LetterRequest $letterRequest): bool
    {
        if (! $this->view($user, $letterRequest)) {
            return false;
        }

        if ($user->hasRole('Resident')) {
            return $letterRequest->resident?->user_id === $user->getKey()
                && $letterRequest->status === 'completed';
        }

        return true;
    }

    public function downloadDocument(User $user, LetterRequest $letterRequest, LetterDocument $document): bool
    {
        if ((int) $document->letter_request_id !== (int) $letterRequest->getKey() || ! $this->view($user, $letterRequest)) {
            return false;
        }

        if ($user->hasRole('Village Head') && in_array($letterRequest->status, ['signature_pending', 'completed'], true)) {
            return true;
        }

        if ((int) $letterRequest->resident?->user_id === (int) $user->getKey() && $user->hasRole('Resident')) {
            return $letterRequest->status === 'completed'
                && $document->signed_at !== null
                && $document->signature_type === 'prototype';
        }

        return $letterRequest->status === 'completed';
    }
}
