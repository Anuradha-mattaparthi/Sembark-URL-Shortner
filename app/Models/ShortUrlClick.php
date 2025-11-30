<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrlClick extends Model
{
    protected $fillable = [
        'short_url_id',
        'company_id',
        'user_id',
        'ip',
        'user_agent',
        'referer',
        'country',
    ];

    public function shortUrl()
    {
        return $this->belongsTo(ShortUrl::class);
    }
}
