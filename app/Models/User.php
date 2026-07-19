<?php

namespace App\Models;

// Huwag kalimutang i-import ang Carbon kung gagamit ka ng advanced date manipulation sa ibang lugar
// use Illuminate\Support\Carbon; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Idadagdag ang mga bagong columns dito.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'course',
        'location_address',
        'latitude',
        'longitude',
        'last_seen_at',
        // --- IDAGDAG ANG MGA BAGO MONG FIELDS ---
        'last_login_at',
        'last_login_ip',
        'session_id',
        'status',
        // ----------------------------------------
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
     * Ise-set natin ang mga bagong timestamp fields para maging Carbon instances.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            // --- IDAGDAG ANG BAGONG CASTS ---
            'last_login_at' => 'datetime', // Para sa Last Login
            // 'last_login_ip' ay string na kaya hindi na kailangan
            // 'session_id' ay string na kaya hindi na kailangan
            // ----------------------------------
        ];
    }

    // Gagamitin ang Last Activity (last_seen_at) para matukoy ang "Online" status.
    public function isOnline()
    {
        // Kung ang huling aktibidad ay HINDI tataas sa 5 minuto ang nakalipas, siya ay Online.
        return $this->last_seen_at && ($this->last_seen_at->diffInMinutes(now()) < 5);
        // Ang existing mo na $this->last_seen_at->gt(now()->subMinutes(5)) ay tama rin, pero mas standard itong diffInMinutes.
    }
}