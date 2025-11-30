<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'long_url',
        'short_code',
        'hits',
    ];

    protected $appends = ['short_url'];

    public function getShortUrlAttribute()
    {
        return rtrim(config('app.url'), '/') . '/r/' . $this->short_code;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
