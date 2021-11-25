<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class RechargesExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '客户充值.xlsx';

    protected $columns = [
        'id'           => 'ID',
        'uid' => '账号',
        'user.phone'   => '手机号',
        'user.name'    => '姓名',
        'user.staff.username' => '经理',
        'ordnum'       => '订单号',
        'usdt'         => '金额',
        'status'       => '状态',
        'mark'         => '说明',
        'created_at'   => '创建时间',

    ];

    public function map($row): array
    {
        return [
            $row->id,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            data_get($row, 'user.name'),
            data_get($row, 'user.staff.username'),
            $row->ordnum,
            $row->usdt,
            $row->status == 1 ? '未支付' : '已支付',
            $row->mark,
            $row->created_at
        ];
    }
}