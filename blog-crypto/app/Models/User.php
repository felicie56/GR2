<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /* ================== RELATIONSHIPS ================== */

    // User có nhiều bài blog
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    // User có nhiều comment
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // User có nhiều reaction
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    // User có nhiều role (USER / AUTHOR / ADMIN)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    // Helper: kiểm tra user có role nào đó không
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
}
