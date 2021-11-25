<?php

namespace App\Admin\Extensions;

use App\Models\UserEntrusts;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class PositionExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '持仓订单.xlsx';

    protected $columns = [
        'id'        => 'ID',
        'uid'       => '账号',
        'user.phone' => '手机号',
        'user.name' => '姓名',
        'hold_num'    => '订单号',
        'code'      => '币种名称',
        'buyprice'  => '买入价格',
        'buynum'    => '买入数量',
        'leverage'    => '杠杆',
        'otype'     => '方向',
        'stopwin'   => '止盈',
        'stoploss'  => '止损',
        'fee'       => '手续费',
        'dayfee'    => '过夜费',
        'created_at' => '持仓时间',
    ];

    public function map($row): array
    {
        return [
            $row->id,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            data_get($row, 'user.name'),
            $row->hold_num,
            $row->code,
            $row->buyprice,
            $row->buynum,
            $row->leverage,
            $row->otype == 1 ? '买涨' : '买跌',
            $row->stopwin,
            $row->stoploss,
            $row->fee,
            $row->dayfee,
            $row->created_at,
        ];
    }

}