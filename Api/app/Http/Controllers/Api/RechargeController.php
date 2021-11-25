<?php


namespace App\Http\Controllers\Api;

use App\Entities\Wallet\BtcInterface;
use App\Models\AddrRecharge;
use App\Models\UserAddress;
use App\Models\WalletCode;
use Dingo\Api\Http\Request;
use App\Entities\Wallet\EthInterface;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RechargeController extends BaseController
{
    /**
     * RechargeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 充币币种列表
     * @return mixed
     */
    public function codeList()
    {
        $result = WalletCode::where('start_c', 1)->select('code', 'id as wid')->get();
        return $this->success($result);
    }

    /**
     * 充币记录
     * @param Request $request
     * @return mixed
     */
    public function record(Request $request)
    {
        $user = \Auth::user();
        $model = new AddrRecharge();
        $result = $model->select('address', 'amount', 'status', 'created_at')
            ->where('uid', $user->id)
            ->where('status', AddrRecharge::PAYED)
            ->orderByDesc('id')
            ->paginate(15);

//        $result = $user->recharge()->select('order_no', 'address', 'amount', 'status', 'created_at')
//            ->orderByDesc('id')
//            ->paginate($request->input('page_size', 15));

        return $this->success($result);
    }

    /**
     * 客户钱包充值
     * @param Request $request
     * @return mixed
     */
    public function walletRecharge(Request $request)
    {

        $wallet = WalletCode::find($request->wid);
        if (!$wallet){
            return $this->failed('充币币种不存在');
        }
        $user = \Auth::user();
        $wall_type = $wallet->c_type;

        //查询地址是否存在
        $userAddr = $user->userAddress()->where('wid', $wallet->id)->first();
        //如果地址为空就创建
        if (empty($userAddr)) {
            if ($wall_type == 2){
                $eth_obj = new EthInterface();
                $addr_arr = $eth_obj->createWallet();
                $address = $addr_arr['data']['address'];
                $salt = $addr_arr['data']['privateKey'];
                $type = 2;
            } else {
                //其他系列
                $btc_obj = new BtcInterface();
                $address = $btc_obj->createWallet($user->account);
//                $address = '1x234567890123456789';
                $salt = '';
                $type = 1;
//                return $this->failed('暂不支持');
            }

            if ($address) {
                //组装创建数据
                $saveData['uid'] = $user->id;
                $saveData['address'] = $address;
                $saveData['private_key'] = $salt;
//                $saveData['zjc']     = $seed;
                $saveData['type'] = $type;
                $saveData['wid'] = $wallet->id;
                UserAddress::create($saveData);
            }
        } else {
            //地址不为空直接返回地址
            $address = $userAddr->address;
        }
        if (!$address) {
            return $this->failed('创建账户失败');
        }

        $qrcode = QrCode::format('png')->size(368)->margin(0)
            ->generate($address);
        $data['address'] = $address;
        $data['qrcode'] = 'data:image/png;base64,' . base64_encode($qrcode);

        return $this->success($data);
    }

}
