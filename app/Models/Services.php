<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'name',
        'slug',
        'lead',
        'description',
        'features',
        'image',
        'images_quill',
        'price_monthly',
        'price_yearly',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'images_quill' => 'array',
    ];
}
