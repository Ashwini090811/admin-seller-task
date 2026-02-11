<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Seller extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name','email','mobile','country','state','skills','password'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'skills' => 'array'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
