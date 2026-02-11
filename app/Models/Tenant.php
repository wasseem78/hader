<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'domain',
        'subdomain',
        'plan_id',
        'stripe_customer_id',
        'db_name',
        'db_host',
        'db_port',
        'db_username_enc',
        'db_password_enc',
        'trial_ends_at',
        'status',
    ];

    protected $hidden = [
        'db_username_enc',
        'db_password_enc',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the decrypted database username.
     */
    public function getDbUsernameAttribute()
    {
        return $this->db_username_enc ? Crypt::decryptString($this->db_username_enc) : null;
    }

    /**
     * Set the encrypted database username.
     */
    public function setDbUsernameAttribute($value)
    {
        $this->attributes['db_username_enc'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted database password.
     */
    public function getDbPasswordAttribute()
    {
        return $this->db_password_enc ? Crypt::decryptString($this->db_password_enc) : null;
    }

    /**
     * Set the encrypted database password.
     */
    public function setDbPasswordAttribute($value)
    {
        $this->attributes['db_password_enc'] = $value ? Crypt::encryptString($value) : null;
    }
}
