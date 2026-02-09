<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'position',
        'email',
        'password',
        'role',
        'phone',
        'photo',
        'public_visible',
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
        ];
    }

    /**
     * Ensure role is valid.
     * Only allows: staff, admin, developer
     * Developer role cannot be assigned through normal operations.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validate role is one of allowed values
            $validRoles = ['staff', 'admin', 'developer'];
            if (!in_array($model->role, $validRoles)) {
                throw new \InvalidArgumentException("Invalid role: {$model->role}. Allowed roles: " . implode(', ', $validRoles));
            }

            // Prevent non-developer accounts from creating/updating developer roles
            if ($model->isDirty('role') && $model->role === 'developer') {
                // Only allow developer role assignment through seeder or manual database operations
                // Check if current authenticated user is trying to assign it via UI
                if (auth()->check() && auth()->user()->role !== 'developer') {
                    throw new \InvalidArgumentException('Unauthorized: Developer role can only be assigned by database operations.');
                }
            }
        });
    }

    /**
     * Get staff leave records for this user
     */
    public function staffLeaves()
    {
        return $this->hasMany(StaffLeave::class);
    }

    /**
     * Check if staff member is on leave on a specific date
     */
    public function isOnLeave($date): bool
    {
        return $this->staffLeaves()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }
}
