<?php


namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AddWithdrawAddress;
use App\Models\UserWithdrawAddress;
use App\Models\WalletCode;
use Dingo\Api\Http\Request;

/**
 * 提币地址管理
 * Class WithdrawAddressController
 * @package App\Http\Controllers\Api
 */
class WithdrawAddressController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 地址列表
     * @return mixed
     */
    public function show()
    {
        $user = \Auth::user();

        // $lists = $user->withdrawAddress()->select('id as address_id', 'address', 'notes', 'type')
        //     ->orderByDesc('id')
        //     ->limit(15)
        //     ->get();

        $result = WalletCode::where('start_t', 1)->select('code', 'id as wid','c_type')->get();

        foreach ($result as $key => $value) {
            $lists = $user->withdrawAddress()->where('wid',$value->wid)->select('id as address_id', 'address', 'notes', 'wid' ,'type')
                ->orderByDesc('id')
                ->limit(15)
                ->get();
            $result[$key]['list'] = $lists;
        }

        // $result[] = [
        //     'code' => 'USDT',
        //     'wid' => 1,
        //     'c_type' => 2,
        //     'list' => $lists
        // ];

//        $btc = $eth = [];
//        foreach ($lists as $value) {
//            if ($value->type == UserWithdrawAddress::BTC_SERIES) {
//                array_push($btc, $value);
//            } else {
//                array_push($eth, $value);
//            }
//        }
//        $data = [
//            'eth' => $eth,
////            'btc' => $btc
//        ];
        return $this->success($result);
    }

    /**
     * 添加提币地址
     * @param AddWithdrawAddress $request
     * @return mixed
     */
    public function add(AddWithdrawAddress $request)
    {
        $user = \Auth::user();

        $input = $request->only(['address', 'notes', 'wid']);

        $code = WalletCode::where('id', $input['wid'])->select('code', 'id as wid','c_type')->first();
        if(!$code){
            return $this->failed('币种不存在');
        }
        if (!(preg_match('/^(1|3)[a-zA-Z\d]{24,33}$/', $input['address']) &&
            preg_match('/^[^0OlI]{25,34}$/', $input['address']))) {
            //return $this->failed(trans('address.illegal_address'));
        }
        $user->withdrawAddress()->firstOrcreate(['address' => $input['address'], 'wid' => $input['wid'], 'type' =>$code->c_type], $input);

        return $this->success();
    }

    /**
     * 删除地址
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $user = \Auth::user();
        $address_id = $request->post('address_id', 0);
        $ret = $user->withdrawAddress()->where('id', $address_id)->delete();
        if (!$ret) {
            return $this->failed(trans('common.operation_failed'));
        }
        return $this->success();
    }


}
