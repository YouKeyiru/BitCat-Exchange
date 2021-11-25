<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereCode($code)
 * Class ProductsExchange
 * @package App\Models
 */
class ProductsExchange extends Model
{
    //
    protected $table = 'products_exchange';
    public $timestamps = false;

    const HIDE_TYPE = 0; // state 不显示
    const DIS_TYPE = 1; // state 显示

    /**
     * 币种截取
     * @param $code
     * @return array|bool|string
     */
    public static function coinCut($code)
    {
        return explode('/', $code);
    }

    public function getImageAttribute($value)
    {
        //image
        if ($value) {
            $value = ImageService::setHost() . 'storage/' . $value;
        }
        return $value;
    }

}
