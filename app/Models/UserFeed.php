<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeed extends Model
{
    use HasFactory;
    protected $fillable = [
        'userId',
        'sources',
        'categories',
        'author',
    ];
}
