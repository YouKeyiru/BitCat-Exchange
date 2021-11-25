<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AssetService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserAsset;
use App\Models\UserMoneyLog;

class MemberRebate extends Command
{
    protected $signature = 'member:rebate';
    protected $description = '用户返佣';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){

        $start_yesterday = Carbon::yesterday()->startOfDay()->toDateTimeString();
        $end_yesterday = Carbon::yesterday()->endOfDay()->toDateTimeString();
        $where = [$start_yesterday, $end_yesterday];

        $sub = DB::table('address_recharges')->select('uid','wid')->selectRaw('max(amount) as amount')->whereBetween('created_at', $where)->groupBy('uid','wid');
        DB::table('address_recharges as a')->rightJoin(DB::raw("({$sub->toSql()}) as b"), function ($join){
            $join->on('b.uid', '=', 'a.uid')->on('b.amount', '=', 'a.amount');
        })->mergeBindings($sub)->orderBy('id')->chunk(50, function ($lists){
            foreach ($lists as $item){
                self::dealRebate($item);
            }
        });
    }


    /**
     * 处理推荐分佣
     * @param $order
     * @throws \Exception
     */
    private static function dealRebate($recharges){

        $relationship = User::whereId($recharges->uid)->value('relationship');
        $temp = explode(',', $relationship);
        $relationship_arr = array_reverse($temp);

        Log::debug('relationship', [$relationship_arr, $relationship, $recharges->uid]);

        //获取返佣比例
        $where = ['fy.first', 'fy.second', 'fy.third', 'fy.fourth'];
        $rebate_rate = DB::table('admin_config')->whereIn('name', $where)->orderByRaw("field(name, 'fy.first','fy.second','fy.third','fy.fourth')")->get();

        $asset_service = new AssetService();

        foreach ($relationship_arr as $key=>$item){
            if ($key < 4) {
                $temp_rebate_rate = str_replace('%', '', $rebate_rate[$key]->value) / 100;
                $rebate = bcmul($recharges->amount, $temp_rebate_rate, 6);
                Log::debug('rebate', [$item, $rebate]);
                $asset_service->writeBalanceLog($item, $recharges->uid, $recharges->wid, UserAsset::ACCOUNT_CURRENCY, $rebate,
                    UserMoneyLog::RECHARGE_REBATE, '充值返佣');
            }
        }
    }
}

















































