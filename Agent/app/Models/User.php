<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


/**
 * Class User
 * @method static Builder wherePhone($phone)
 * @method static Builder whereEmail($email)
 * @method static Builder whereAccount($account)
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'phone',
        'email',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'payment_password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * 修改器，密码加密存储
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if (strlen($value) != 60) {
            // 不等于 60，做密码加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    /**
     * 修改器，密码加密存储
     * @param $value
     */
    public function setPaymentPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if (strlen($value) != 60) {
            // 不等于 60，做密码加密处理
            $value = bcrypt($value);
        }

        $this->attributes['payment_password'] = $value;
    }

    public function getAvatarAttribute($value)
    {
        if ($value) {
            $value = ImageService::setHost() . $value;
        }
        return $value;
    }

    public function recommend()
    {
        return $this->hasOne(User::class, 'recommend_id', 'id');
    }

    public function asset()
    {
        return $this->hasMany(UserAsset::class, 'uid', 'id')->select('wid', 'account', 'balance', 'frost', 'total_recharge', 'total_withdraw');
    }

    public function auth()
    {
        return $this->hasOne(Authentication::class, 'uid', 'id');
    }

    public function transfer()
    {
        return $this->hasMany(Transfer::class, 'uid', 'id');
    }

    public function position()
    {
        return $this->hasMany(UserPosition::class, 'uid', 'id');
    }

    public function moneyLog()
    {
        return $this->hasMany(UserMoneyLog::class, 'uid', 'id');
    }

    public function config()
    {
        return $this->hasOne(UserConfig::class, 'uid', 'id');
    }

    public function payment()
    {
        return $this->hasMany(FbPay::class, 'uid', 'id');
    }

    public function userPositions()
    {
        return $this->hasMany(ContractPosition::class, 'uid', 'id');
    }

    public function userEntrusts()
    {
        return $this->hasMany(ContractEntrust::class, 'uid', 'id');
    }

    public function userTrans()
    {
        return $this->hasMany(ContractTrans::class, 'uid', 'id');
    }

    public function userExchange()
    {
        return $this->hasMany(ExchangeOrder::class, 'uid', 'id');
    }

    public function userAddress()
    {
        return $this->hasMany(UserAddress::class, 'uid', 'id');
    }

    public function withdrawRecord()
    {
        return $this->hasMany(UserWithdrawRecord::class, 'uid', 'id');
    }

    public function withdrawAddress()
    {
        return $this->hasMany(UserWithdrawAddress::class, 'uid', 'id');
    }

}
