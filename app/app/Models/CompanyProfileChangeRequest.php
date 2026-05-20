<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfileChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'company_profile_id',
        'current_data',
        'requested_data',
        'status',
        'admin_note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'current_data' => 'array',
        'requested_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class);
    }
}
