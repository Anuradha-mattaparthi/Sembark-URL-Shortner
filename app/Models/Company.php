<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\shortUrl;

class Company extends Model
{
    protected $fillable = ['name', 'created_by'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function admin()
{
    return $this->hasOne(User::class)->where('role', 'admin');
}
public function shortUrls()
{
    return $this->hasMany(ShortUrl::class);
}

}
