<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use App\Models\AddressRecharge;
use App\Models\UserWithdrawRecord;
use App\User;
use Carbon\Carbon;
use App\Models\Authentication;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        //    总人数（注册所有人数）
        //    昨日新增人数（注册所有人数）
        //    已实名人数（审核通过）
        $start = Carbon::today();
        $end = Carbon::tomorrow();
        $new_users = User::whereBetween('created_at',[$start,$end])->count();

        $start_yesterday = Carbon::yesterday()->startOfDay()->toDateTimeString();
        $end_yesterday = Carbon::yesterday()->endOfDay()->toDateTimeString();
        $yestedat_total_reg_number = User::whereBetween('created_at',[$start_yesterday,$end_yesterday])->count();

        $total_reg_number = User::count();

        $real_auth_number = Authentication::where('status',Authentication::ADVANCED_CHECK_AGREE)->count();

        
        return $content
            ->title('首页')
            ->description('统计信息')
            ->row(function (Row $row) use ($total_reg_number,$yestedat_total_reg_number,$real_auth_number,$new_users){
                $row->column(6, function (Column $column)  use ($total_reg_number,$yestedat_total_reg_number,$real_auth_number,$new_users){
                    $column->row(function(Row $row) use ($total_reg_number,$yestedat_total_reg_number,$real_auth_number,$new_users) {
                        $row->column(2/4, function (Column $column)  use ($total_reg_number){
                            $column->append(new InfoBox('总人数', 'envira', 'blue', '/admin/users', $total_reg_number));
                        });
                        $row->column(2/4, function (Column $column)  use ($real_auth_number){
                            $column->append(new InfoBox('已实名人数', 'usdt', 'blue', '/admin/authentications', $real_auth_number));
                        });
                        $row->column(2/4, function (Column $column)  use ($yestedat_total_reg_number){
                            $column->append(new InfoBox('昨日新增人数', 'usdt', 'blue', '/admin/users', $yestedat_total_reg_number));
                        });
                        $row->column(2/4, function (Column $column)  use ($new_users){
                            $column->append(new InfoBox('今日注册', 'users', 'blue', '/admin/users', $new_users));
                        });
                    });
                });
            });
        // return $content
        //     ->title('Dashboard')
        //     ->description('Description...')
        //     ->row(Dashboard::title())
        //     ->row(function (Row $row) {

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::environment());
        //         });

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::extensions());
        //         });

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::dependencies());
        //         });
        //     });
    }
}
