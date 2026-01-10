<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ReportedBugs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chatbot_id',
        'name',
        'email',
        'issueDescription',
        'stepsToReproduce',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
