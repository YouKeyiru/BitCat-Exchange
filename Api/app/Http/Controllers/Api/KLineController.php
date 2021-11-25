<?php

namespace App\Http\Controllers\Api;

use App\Entities\K\KLine;
//use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KLineController extends BaseController
{

    /**
     * 获取K线数据
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        //$pageSize = $request->input('pageSize', 200);
	    $pageSize = $request->input('page_size', 200);
        $type = $request->input('goodsType', 'minute');
        $code = $request->input('code', 'btcusdt');

        if(!in_array($code, ['btc/usdt','eth/usdt','xrp/usdt','ltc/usdt','eos/usdt','bch/usdt','etc/usdt'])){

            switch ($type) {
                case 'minute':
                    $table = '1';
                    $name = 'xy_1min_info';
                    break;
                case 'minute5':
                    $table = '5';
                    $name = 'xy_5min_info';
                    break;
                case 'minute15':
                    $table = '15';
                    $name = 'xy_15min_info';
                    break;
                case 'minute30':
                    $table = '30';
                    $name = 'xy_30min_info';
                    break;
                case 'minute60':
                    $table = '60';
                    $name = 'xy_60min_info';
                    break;
                case 'minute240':
                    $table = '240';
                    $name = 'xy_4hour_info';
                    break;
                case 'day':
                    $table = '1440';
                    $name = 'xy_dayk_info';
                    break;
                case 'month':
                    $table = '43200';
                    $name = 'xy_month_info';
                    break;
                case 'week':
                    $table = '10080';
                    $name = 'xy_week_info';
                    break;
                default:
                    $table = "1";
                    $name = 'xy_1min_info';
                    break;
            }

            $data = \DB::table($name)->where('code', $code)->limit($pageSize)->orderBy('timestamp', 'desc')->get();//->toArray();
            // print_r($data);die();
            $resultss = [];
            if($data){
                foreach ($data as $k => $v){
                    $resultss[] = [
                        'timestamp' => $v->timestamp,//time(),
                        'open' => $v->openingPrice,
                        'close' => $v->closingPrice,
                        'high' => $v->highestPrice,
                        'low' => $v->lowestPrice,
                        'volume' => $v->volume,
                    ];
                }
            }else{
                $resultss[] = [
                    'timestamp' => time(),
                    'open' => '0',
                    'close' => '0',
                    'high' => '0',
                    'low' => '0',
                    'volume' => '0',
                ];
            }
            return $this->success($resultss, trans('common.operation_success'));
        }

        $code = str_replace('/', '', $code);
        if (!$code) {
            //return __return($this->errStatus, '币种不存在');
            return $this->failed(trans('exchange.code_no_existent'));
        }
        try {
            $kLine = new KLine();
            $kLine->setType($this->format_type($type));
            $kLine->setPageSize($pageSize);
            list($status, $result) = $kLine->handle($code);
            if (!$status || !$result) {
                //throw new \ErrorException(trans('common.operation_failed'));
                //查询失败默认给一个空
                $resultss[] = [
                    'timestamp' => time(),
                    'open' => '0',
                    'close' => '0',
                    'high' => '0',
                    'low' => '0',
                    'volume' => '0',
                ];
                return $this->success($resultss, trans('common.operation_success'));
            }
            return $this->success($this->format_data($result->data), trans('common.operation_success'));
        } catch (\ErrorException $errorException) {
            return $this->failed($errorException->getMessage());
        }
    }

    /**
     * 数据类型字段映射
     * @param $type
     * @return string
     */
    private function format_type($type)
    {
        // 1/5/15/30/60/240/1440/10080/43200
        $types = [
            'minute'    => '1',
            'minute5'   => '5',
            'minute15'  => '15',
            'minute30'  => '30',
            'minute60'  => '60',
            'minute240' => '240',
            'day'       => '1440',
            'week'      => '10080',
            'month'     => '43200',
        ];
        if (array_key_exists($type, $types)) {
            return $types[$type];
        } else {
            return $types['minute'];
        }
    }

    /**
     * 格式化输出
     * @param $data
     * @return array
     */
    private function format_data($data)
    {
        $result = [];
        foreach ($data as $datum) {
            $result[] = [
                'timestamp' => $datum->t,
                'open'      => $datum->o,
                'close'     => $datum->c,
                'high'      => $datum->h,
                'low'       => $datum->l,
                'volume'    => $datum->v,
            ];
        }
        return $result;
    }

}
