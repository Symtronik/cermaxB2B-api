<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use \App\Models\User;

class CompanyProfile extends Model
{
    use HasFactory, HasApiTokens, HasRoles;

    protected $fillable = [
        'user_id',
        'company_name',
        'vat_id',
        'regon',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'country',
        'phone',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
