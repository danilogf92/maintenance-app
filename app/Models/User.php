<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles, Notifiable;

    protected $attributes = ['is_active' => true, 'can_access_admin' => false];

    protected $fillable = ['institution_id', 'name', 'email', 'identification', 'phone', 'job_title', 'password', 'is_active', 'can_access_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'can_access_admin' => 'boolean', 'email_verified_at' => 'datetime'];
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_active && ($this->hasRole('SuperAdmin') || ($this->can_access_admin && $this->can('panel.access')));
    }
}
