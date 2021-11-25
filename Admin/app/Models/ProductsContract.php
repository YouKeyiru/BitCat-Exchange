<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder whereCode($code)
 * Class ProductsContract
 * @package App\Models
 */
class ProductsContract extends Model
{
    //
    protected $table = 'products_contract';
    public $timestamps = false;

    const HIDE_TYPE = 0; // state 不显示
    const DIS_TYPE = 1; // state 显示

    public function getMaxOrderAttribute($value)
    {
        return floatval($value);
    }
    public function getMinOrderAttribute($value)
    {
        return floatval($value);
    }
    public function getMaxChicangAttribute($value)
    {
        return floatval($value);
    }
    public function getHandlingFeeAttribute($value)
    {
        return floatval($value);
    }
    public function getSpreadAttribute($value)
    {
        return floatval($value);
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
