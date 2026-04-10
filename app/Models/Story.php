<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'title',
        'description',
        'content',
        'image_url',
        'published_at',
        'category_id'
    ];
}
