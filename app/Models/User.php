<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'provinsi_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if user has super admin role
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user has admin vip role (can view all but not edit)
     */
    public function isAdminVip(): bool
    {
        return $this->role === 'admin_vip';
    }

    /**
     * Check if user has admin role (can edit within their region)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has any admin level access
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_vip', 'admin']);
    }

    /**
     * Check if user can edit data (not just view)
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the provinsi that owns the user
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    /**
     * Check if user can access data from specific province
     */
    public function canAccessProvinsi(?int $provinsiId): bool
    {
        // Super admin can access all provinces
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin VIP can view all provinces but not edit
        if ($this->isAdminVip()) {
            return true;
        }

        // Admin can only access their own province
        if ($this->isAdmin()) {
            return $this->provinsi_id === $provinsiId;
        }

        return false;
    }

    /**
     * Check if user can edit data from specific province
     */
    public function canEditProvinsi(?int $provinsiId): bool
    {
        // Super admin can edit all provinces
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin VIP cannot edit anything
        if ($this->isAdminVip()) {
            return false;
        }

        // Admin can only edit their own province
        if ($this->isAdmin()) {
            return $this->provinsi_id === $provinsiId;
        }

        return false;
    }
}
