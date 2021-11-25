<?php


namespace App\Http\Controllers\Api;


use App\Entities\Notification\Email\VerifyMailHandel;
use App\Http\Traits\ApiResponse;
use App\Models\FbBuying;
use App\Models\FbSell;
use App\Models\User;
use App\Models\UserAsset;
use App\Services\AssetService;
use App\Services\CommissionService;
use App\Services\FbTransService;
use App\Services\GoogleAuthenticatorService;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class IndexController extends BaseController
{
    use ApiResponse;

    public function recharge(Request $request, AssetService $assetService)
    {
        try {
            $user = User::where(['account' => $request->input('user_account', 0)])->first();
            $assetService->writeBalanceLog(
                $user->id,
                0,
                $request->input('wid', 0),
                $request->input('account', 0),
                $request->input('amount', 0),
                '300',
                '测试充值资产'
            );
            return 'ok';
        } catch (\Exception $exception) {

            return $exception->getMessage();
        }


//        for ($j = 1; $j <= 4; $j++){
//            for ($i = 1; $i <= 4; $i++) {
//                UserAsset::create([
//                    'uid'     => 1,
//                    'wid'     => $i,
//                    'account' => $j,
//                    'balance' => 10000,
//                ]);
//            }
//        }


    }


    public function test()
    {

//        $n = FbTransService::getOverNum(11,2);
//        $totalNum = FbBuying::where(['uid' => 11])->sum('trans_num');
//
//        dd(compact('n','totalNum'));

        $user = User::find(36);
        $path = array_filter(explode(',', $user->relationship));
        $path[]=strval($user->id);
        dd($path);
        $data = CommissionService::levelCondition();
        dd($data);
        $data = \DB::table('admin_config')->where('name', 'like', 'grade%')->pluck('value', 'name')->toArray();
        $level = [];
//        for ($i = 1; $i <= 5; $i++) {
//            $level[$i] = [
//                'push_user' => $data['grade.condition_' . $i . '_1'],
//                'team_user' => $data['grade.condition_' . $i . '_2'],
//                'activity_num' => $data['grade.condition_' . $i . '_3'],
//                'team_investment' => $data['grade.condition_' . $i . '_4'],
//                'node_num' => $data['grade.condition_' . $i . '_5'],
//                'super_node_num' => $data['grade.condition_' . $i . '_6'],
//                'trans_rate' => $data['grade.condition_' . $i . '_7'],
//                'mining_rate' => $data['grade.condition_' . $i . '_8'],
//            ];
//        }
        for ($i = 1; $i <= 5; $i++) {
            $level['grade_' . $i . '_push_user'] = $data['grade.condition_' . $i . '_1'];
            $level['grade_' . $i . '_team_user'] = $data['grade.condition_' . $i . '_2'];
            $level['grade_' . $i . '_activity_num'] = $data['grade.condition_' . $i . '_3'];
            $level['grade_' . $i . '_team_investment'] = $data['grade.condition_' . $i . '_4'];
            $level['grade_' . $i . '_node_num'] = $data['grade.condition_' . $i . '_5'];
            $level['grade_' . $i . '_super_node_num'] = $data['grade.condition_' . $i . '_6'];
        }
        dd($level);

        $s = VerifyMailHandel::check('365888920@qq.com', 655540);
        dd($s);
        $mail = new VerifyMailHandel('365888920@qq.com');


        $result = $mail->send();

        if (!$result) {
            dd($mail->getError());
        }

        echo 'ok';
        return;

        $redis = Redis::connection('default');

        $data = [
            'order_no'     => 'order_no',
            'code'         => 'btc/usdt',
            'buy_price'    => 1,
            'stop_win'     => 2,
            'stop_loss'    => 7,
            'market_price' => 5,
            'otype'        => 1,
        ];

//        dd(collect($data));
        $key = 'pos-' . $data['code'];

        $a = $redis->hMset($data['order_no'], $data);

//        $redis->sAdd($key, $data['order_no']);

        dd($a);
        die;
        $data = GoogleAuthenticatorService::CreateSecret();

        return $this->success($data);
        $user = User::all();

        return $this->success();

//        return $this->success($user);

        $data = $this->api->post('register', ['v_code' => 'asdsds']);
        Log::error($data);
        dd($data);
        Log::error(123);
        dd(12);
        \DB::beginTransaction();

        $user = User::find(25);

        $user->name = 123;
        $user->save();
        $user = User::find(25);

        dd($user->name);

        $model = new FbSell();
        $jyOrder = $model->where('order_no', 'FBSELL200525124715741190')->first();
        // 减成交数量
        $dec = $jyOrder->decrement('deals_num', 1);
        dd($dec);

        try {

            $sms = new Chinese(0, 13523925725, 1);
            $sms->action();


        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

    }

    public function run($command)
    {

        \Artisan::call($command);
        echo '执行时间 '.Carbon::now();
    }
}
