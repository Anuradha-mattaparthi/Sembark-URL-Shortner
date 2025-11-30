<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\User;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property int|null $company_id
 * @property string $token
 * @property string $status
 * @property int|null $sent_by
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $accepted_at
 *
 * @property-read Company $company
 * @property-read User $sender
 */
class Invitation extends Model
{
    protected $fillable = [
        'name','email','role','company_id','token','status','sent_by','sent_at','accepted_at'
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function sender() { return $this->belongsTo(User::class,'sent_by'); }
}
