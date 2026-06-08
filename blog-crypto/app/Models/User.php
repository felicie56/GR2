<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AuthorApplication;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
    'name',
    'username',
    'email',
    'password',
    'avatar',
    'bio',
    'headline',
    'occupation',
    'organization',
    'location',
    'website_url',
    'linkedin_url',
    'x_url',
    'experience_years',
    'expertise_areas',
    'profile_completed_at',
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
        'expertise_areas' => 'array',
        'profile_completed_at' => 'datetime',
    ];

    /* ================== RELATIONSHIPS ================== */

    // User có nhiều bài blog
    public function blogPosts(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\BlogPost::class);
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

    public function authorApplications(): HasMany
{
    return $this->hasMany(AuthorApplication::class);
}

public function latestAuthorApplication()
{
    return $this->hasOne(AuthorApplication::class)->latestOfMany();
}

public function pendingAuthorApplication()
{
    return $this->hasOne(AuthorApplication::class)->where('status', 'pending');
}

    public function hasCompletedAuthorProfile(): bool
{
    return ! empty($this->name)
        && ! empty($this->bio)
        && ! empty($this->headline)
        && ! empty($this->experience_years)
        && ! empty($this->expertise_areas);
}
}
