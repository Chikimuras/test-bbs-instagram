<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public mixed $instagram_username;
    public ?string $instagram_user_id = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instagram_username',
        'instagram_token',
        'instagram_token_expires_at',
        'instagram_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'instagram_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'instagram_token_expires_at' => 'datetime',
    ];
    private mixed $instagram_token_expires_at;

    /**
     * Automatically set the instagram_token_expires_at attribute to the current time plus 1 hour.
     *
     * @param string $value
     */
    public function setInstagramTokenAttribute(string $value): void
    {
        $this->attributes['instagram_token'] = $value;
        $this->attributes['instagram_token_expires_at'] = now()->addHour();
    }

    /**
     * Check if the instagram_token is expired.
     *
     * @return bool
     */
    public function instagramTokenExpired(): bool
    {
        return now()->gt($this->instagram_token_expires_at);
    }

    /**
     * Check if user exists in database.
     *
     */
    public function userExists($instagram_user_id): bool
    {
        return self::where('instagram_user_id', $instagram_user_id)->first();
    }
}
