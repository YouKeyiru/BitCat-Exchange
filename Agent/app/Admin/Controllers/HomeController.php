<?php

namespace App\Admin\Controllers;

use App\Models\ContractTrans;
use App\Models\FbTrans;
use App\Models\Recharge;
use App\Models\UserWithdraw;
use App\Models\UserWithdrawRecord;
use App\User;
use Carbon\Carbon;
use App\Models\ProfitRebates;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use QrCode;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $admin = Admin::user();

        if($admin->account_type ==1){
//            $url = env('API_URL').'/pc/register.html';
//            $querys = '';
//            $querys .= '?recommend=' . $admin->username;
//            $qrcode = QrCode::format('png')->size(368)->margin(0)
//                ->generate($url . $querys);
//            $data['url'] = $url . $querys;
            $data['account'] = $admin->username;
//            $data['qrcode'] = 'data:image/png;base64,' . base64_encode($qrcode);
            return $content
                ->title('推广页面')
                ->description('推广码')
                ->row(function (Row $row) use ($data) {
                    $row->column(12, function (Column $column) use ($data) {
                        $column->append(view('welcome',['data'=>$data]));
                    });
                });
            } else {
                $start = Carbon::today();
                $end = Carbon::tomorrow();
                $type = 'recommend_id';
                if($admin->account_type == 2){
                    $type = 'center_id';
                }

                if($admin->account_type == 3){
                    $type = 'unit_id';
                }

                if($admin->account_type == 4){
                    $type = 'agent_id';
                }

                if($admin->account_type == 5){
                    $type = 'staff_id';
                }

                $new_users = User::whereBetween('created_at',[$start,$end])
                ->where($type,$admin->id)
                ->count();

//                $new_fee = ProfitRebates::whereBetween('created_at',[$start,$end])
//                ->where($type,$admin->id)
//                ->sum('fee');
                $new_fee = ContractTrans::whereBetween('created_at',[$start,$end])
                    ->whereHas('user',function ($query) use($type,$admin) {
                        $query->where($type,$admin->id)->where('is_moni', 0);
                    })
                    ->sum('profit');

                $today_recharge = Recharge::whereBetween('created_at',[$start,$end])
                    ->where('status',Recharge::PAYED)
                    ->whereHas('user',function ($query) use($type,$admin) {
                        $query->where($type,$admin->id)->where('is_moni', 0);
                    })
                    ->sum('amount');

                $today_withdraw = UserWithdrawRecord::whereBetween('created_at',[$start,$end])
                    ->whereHas('user',function ($query) use($type,$admin) {
                        $query->where($type,$admin->id);
                    })
                    ->sum('amount');

                $fb_get = FbTrans::whereBetween('created_at',[$start,$end])
                    ->where('status', 3)
                    ->where('order_type', 2)
                    ->whereHas('gou',function ($query) use($type,$admin) {
                        $query->where($type,$admin->id);
                    })
                    ->sum('total_num');

                $fb_out = FbTrans::whereBetween('created_at',[$start,$end])
                    ->where('status', 3)
                    ->where('order_type', 1)
                    ->whereHas('chu',function ($query) use($type,$admin) {
                        $query->where($type,$admin->id);
                    })
                    ->sum('total_num');

                return $content
                    ->title('首页')
                    ->description('统计信息')
                    ->row(function (Row $row) use ($new_users,$new_fee,$today_recharge,$today_withdraw,$fb_get,$fb_out){
                        $row->column(1/4, function (Column $column) use ($new_users) {
                            $column->append(new InfoBox('今日注册', 'users', 'purple', null, filterMoney($new_users, 4)));
                        });
                        $row->column(1/4, function (Column $column) use ($new_fee) {
                            $column->append(new InfoBox('今日盈亏', 'money', 'purple', null, filterMoney($new_fee, 4)));
                        });
                        $row->column(1/4, function (Column $column) use ($today_recharge) {
                            $column->append(new InfoBox('今日入金', 'money', 'purple', null, filterMoney($today_recharge, 4)));
                        });
                        $row->column(1/4, function (Column $column) use ($today_withdraw) {
                            $column->append(new InfoBox('今日出金', 'money', 'purple', null, filterMoney($today_withdraw, 4)));
                        });
                        $row->column(1/4, function (Column $column) use ($fb_get) {
                            $column->append(new InfoBox('法币入金', 'money', 'purple', null, filterMoney($fb_get, 4)));
                        });
                        $row->column(1/4, function (Column $column) use ($fb_out) {
                            $column->append(new InfoBox('法币出金', 'money', 'purple', null, filterMoney($fb_out, 4)));
                        });
                    });
            }

    }
}
