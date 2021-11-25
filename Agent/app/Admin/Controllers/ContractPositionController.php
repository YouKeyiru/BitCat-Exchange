<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\PositionExporter;
use App\Models\ContractPosition;
use App\Models\UserAsset;
use App\Models\UserPositions;
use App\Models\AgentUser;
use App\Services\AssetService;
use App\Services\ContractTransService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Client;

class ContractPositionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员持仓单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContractPosition);
        $grid->disableExport(false);
        $grid->disableCreateButton();
        $admin = Admin::user();
        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) use ($admin) {

                $filter->equal('user.account', __('Account'));
                $filter->equal('user.phone', __('Phone'));
                $filter->between('created_at','创建时间')->datetime();

            });
            $filter->column(1/2, function ($filter) use ($admin) {
                if($admin->account_type == 4){
                    $filter->equal('user.unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',3)->pluck('username', 'id'))
                        ->load('user.agent_id','/api/agent');
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select()
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();

                }

                if($admin->account_type == 3){
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',2)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

                if($admin->account_type == 2){
                    $filter->equal('user.agent_id', __('Agent'))
                        ->select(AgentUser::where('id',$admin->id)->pluck('username', 'id'))
                        ->load('user.staff_id','/api/agent');
                    $filter->equal('user.staff_id', __('Staff'))
                        ->select();
                }

            });

        });

        $user = Admin::user();
        $types = [
            2 => 'center_id',
            3 => 'unit_id',
            4 => 'agent_id',
            5 => 'staff_id'];
        $grid->model()->whereHas('user',function ($query) use ($user,$types)
            {
                $query->where($types[$user->account_type],$user->id);
            });
        $grid->model()->orderBy('id','desc');
//        $grid->column('id', __('Id'));
        $grid->column('user.account', __('Account'));
        $grid->column('user.phone', __('Phone'));
        $grid->column('user.name', __('User name'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('code', __('Pname'))->label();
        $grid->column('buy_price', __('Buy price'));
        $grid->column('buy_num', __('Buy num'))->totalRow(function ($buynum) {
            return "<span class='text-danger text-bold'>{$buynum} </span>";
        });
        $grid->column('otype', __('Otype'))->display(function ($otype) {
            return $otype == 1 ? '买涨' : '买跌';
        });
        $grid->column('buy_num', __('Buy num'));
        $grid->column('total_price', __('Deposit'))->totalRow(function ($total_price) {
            return "<span class='text-danger text-bold'>{$total_price} </span>";
        });
//        $grid->column('deposit', __('Total price'))->display(function () {
//            return bcmul($this->buy_num, $this->buy_price, 6);
//        });
//        $grid->column('price', __('Newprice'))->display(function () {
//            return 0;
////            $client = new Client([
////                'timeout'  => 10.0,
////                'verify' => false
////            ]);
////
////            $response = $client->get(env('API_URL').'/api/contract/getNewPrice', [
////                'query' => [
////                    'auth' => sha1('ahsd98y12hqhda%*'),
////                    'code' => $this->code,
////                ]
////            ]);
////            $body = json_decode($response->getBody(),true);
////            if ($body['status_code'] != 200) {
////                $newprice = 0;
////            } else {
////                $newprice = $body['data']['newprice'];
////            }
////
////            return $newprice;
//        });
//
//        $grid->column('risk', __('Risk'))->display(function () {
//            $client = new Client([
//                'timeout'  => 10.0,
//                'verify' => false
//            ]);
//
//            $response = $client->get(env('API_URL').'/api/contract/getNewPrice', [
//                'query' => [
//                    'auth' => sha1('ahsd98y12hqhda%*'),
//                    'code' => $this->code,
//                ]
//            ]);
//            $body = json_decode($response->getBody(),true);
//            if ($body['status_code'] != 200) {
//                $newprice = 0;
//            } else {
//                $newprice = $body['data']['newprice'];
//            }
//
//            $asset = AssetService::_getBalance($this->user->id, ContractTransService::WID, UserAsset::ACCOUNT_CONTRACT);
//
//            //余额
//            $balance = $asset->balance;
//            //保证金余额
//            $deposit = ContractTransService::getDeposit($this->user);
//            //浮动盈亏
//            $data = ContractPosition::query()->where('uid', $this->user->id)->select('code', 'buy_num', 'buy_price', 'otype')->get()->toArray();
//            $total_profit = 0;
//            foreach ($data as $k => $v) {
//                if ($v['otype'] == 1) {
//                    $profit = ($newprice - $v['buy_price']) * $v['buy_num'];
//                } else {
//                    $profit = ($v['buy_price'] - $newprice) * $v['buy_num'];
//                }
//                $total_profit += $profit;
//            }
//            //动态权益
//            $equity = $total_profit + $balance + $deposit;
//
//            //风险率
//            if ($deposit > 0) {
//                $risk = bcdiv($equity,$deposit,4) * 100;
//            } else {
//                $risk = 0;
//            }
//            $risk .= '%';
//            return $risk;
//        });
//
//        $grid->column('profit', __('Profit'))->display(function () {
//            $client = new Client([
//                'timeout'  => 10.0,
//                'verify' => false
//            ]);
//
//            $response = $client->get(env('API_URL').'/api/contract/getNewPrice', [
//                'query' => [
//                    'auth' => sha1('ahsd98y12hqhda%*'),
//                    'code' => $this->code,
//                ]
//            ]);
//            $body = json_decode($response->getBody(),true);
//            if ($body['status_code'] != 200) {
//                $newprice = 0;
//            } else {
//                $newprice = $body['data']['newprice'];
//            }
//
//            if ($this->otype == 1){
//                $profit = ($newprice - $this->buy_price) * $this->buy_num;
//            }else{
//                $profit = ($this->buy_price - $newprice) * $this->buy_num;
//            }
//            return round($profit,4);
//        });
        $grid->column('stop_win', __('Stopwin'));
        $grid->column('stop_loss', __('Stoploss'));
        $grid->column('fee', __('Fee'))->totalRow(function ($fee) {
            return "<span class='text-danger text-bold'>{$fee} </span>";
        });
//        $grid->column('dayfee', __('Day fee'))->totalRow(function ($dayfee) {
//            return "<span class='text-danger text-bold'>{$dayfee} </span>";
//        });
        $grid->column('leverage', __('Leverage'));
        $grid->column('created_at', __('持仓时间'));

//        $grid->exporter(new PositionExporter());

        // 全部关闭
        $grid->disableActions();

        return $grid;
    }


}
