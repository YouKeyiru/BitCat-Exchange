<?php

/**
 * 生成唯一的单号
 * @param string $prefix 前缀
 * @return string
 */
function buildNo($prefix = 'E') {
    /* 选择一个随机的方案 */
    $yCode = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K','L',
        'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    return  $prefix.
        $yCode[intval(date('Y'))%34] .
        strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) .
        substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
}

/**
 * 生成唯一数字号码
 * @param int $len
 * @return string
 */
function createUniqueNum($len = 6){
    $str = '012345678901234567890123456789';
    $idx = '123456789';

    $index      = mt_rand(0,9);
    $num        = $idx[$index];
    $maxIndex   = strlen($str) - 1;

    for($i = 1;$i < $len;$i ++){
        $index  = mt_rand(0,$maxIndex);
        $num   .= $str[$index];
    }
    return $num;
}

/**
 * 生成唯一字符串(数字、字母)
 * @param int $len
 * @return string
 */
function createUniqueStr($len = 6){
    $str        = '0123456789';
    $letter     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str       .= $letter;
    $str       .= strtolower($letter);

    $maxIndex   = strlen($str) - 1;
    $index      = mt_rand(0,$maxIndex);
    $num        = $maxIndex[$index];

    for($i = 1;$i < $len;$i ++){
        $index  = mt_rand(0,$maxIndex);
        $num   .= $str[$index];
    }
    return $num;
}

/**
 * 数据返回
 * @param $status
 * @param $info
 * @param array $data
 * @return array
 */
function getResult($status,$info,$data = []){
    return [
        'status'    => $status,
        'info'      => $info,
        'data'      => $data
    ];
}

/**
 * 图片兼容处理
 * @param $imageUrl
 * @return string
 */
function compatImage($imageUrl)
{
    if (!$imageUrl) {
        return '';
    }
    if (stripos($imageUrl, 'http') !== false) {
        $picture = $imageUrl;
    } else {
        $picture = request()->root() . $imageUrl;
    }
    return $picture;
}

function format_price($price,$code = 'usdt')
{
    $code = strtoupper($code);
    $config = config('system.decimal_places');
    if (isset($config[$code])) {
        $fix = $config[$code];
    } else {
        $fix = 8;
    }

    return number_format($price, $fix, '.', '');

}

/**
 * 高精度计算
 * @param $first
 * @param $second
 * @param string $type
 * @param int $pointNum
 * @return int|string|null
 */
function bcMath($first,$second,$type = '-',$pointNum = 6)
{
    switch ($type) {
        case '-':
            return bcsub($first,$second,$pointNum);
            break;
        case '+':
            return bcadd($first,$second,$pointNum);
            break;
        case '/':
            return bcdiv($first,$second,$pointNum);
            break;
        case '*':
            return bcmul($first,$second,$pointNum);
            break;
    }
    return 0;
}


function returnResult($status = true,$message = '',$data = []) {
    return [
        'status'    => $status,
        'message'   => $message,
        'data'      => $data
    ];
}

function filterMoney($money,$accuracy=2){
    $str_ret = 0;
    if (empty($money) === false) {
        $str_ret = sprintf("%.".$accuracy."f", substr(sprintf("%.".($accuracy+1)."f", floatval($money)), 0, -1));
    }

    return floatval($str_ret);
}
