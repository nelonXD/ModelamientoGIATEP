<?php

namespace App\Models;

use Database\Factories\RegistrationRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequest extends Model
{
    /** @use HasFactory<RegistrationRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'rut', 'pending_key', 'name', 'job_title', 'establishment_id', 'requested_role',
        'email', 'phone', 'password', 'status', 'rejection_reason',
        'resolved_by', 'resolved_at',
    ];

    protected $hidden = ['password'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime', 'password' => 'hashed'];
    }
}
