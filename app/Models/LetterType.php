<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'required_fields', 'required_attachments', 'template_path', 'is_active'])]
class LetterType extends Model
{
    protected function casts(): array
    {
        return ['required_fields' => 'array', 'required_attachments' => 'array', 'is_active' => 'boolean'];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }
}
