<?php

namespace App\Admin\Extensions;

use App\Models\UserEntrusts;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '平仓订单.xlsx';

    protected $columns = [
        'id'        => 'ID',
        'uid'       => '账号',
        'user.phone'       => '手机号',
        'user.name'       => '姓名',
        'user.staff.username'   => '经理',
        'tran_num'    => '订单号',
        'code'      => '币种名称',
        'buyprice'  => '买入价格',
        'buynum'    => '买入数量',
        'leverage'    => '杠杆',
        'otype'     => '方向',
        'stopwin'   => '止盈',
        'stoploss'  => '止损',
        'sellprice'  => '平仓价格',
        'profit'  => '盈亏',
        'fee'       => '手续费',
        'dayfee'    => '过夜费',
        'pc_type'    => '平仓类型',
        'jiancang_at' => '持仓时间',
        'created_at' => '平仓时间',
    ];

    public function map($row): array
    {
        return [
            $row->id,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            data_get($row, 'user.name'),
            data_get($row, 'user.staff.username'),
            $row->tran_num,
            $row->code,
            $row->buyprice,
            $row->buynum,
            $row->leverage,
            $row->otype == 1 ? '买涨' : '买跌',
            $row->stopwin,
            $row->stoploss,
            $row->sellprice,
            $row->profit,
            $row->fee,
            $row->dayfee,
            $this->converted($row->pc_type),
            $row->jiancang_at,
            $row->created_at,
        ];
    }

    function converted($status){
        switch ($status) {
            case 1:
                return '手动平仓';
                break;
            case 2:
                return '止盈平仓';
                break;
            case 3:
                return '止损平仓';
                break;
            case 4:
                return '系统爆仓';
                break;
            default:
                return 'ERROR';
                break;
        }
    }

}