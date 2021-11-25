<?php

namespace App\Admin\Extensions;

use App\Models\UserEntrusts;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntrustExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '委托订单.xlsx';

    protected $columns = [
        'id'         => 'ID',
        'uid'    => '账号',
        'user.phone'    => '手机号',
        'user.name'    => '姓名',
        'user.staff.username'   => '经理',
        'en_num'     => '订单号',
        'code'       => '币种名称',
        'buyprice'   => '买入价格',
        'buynum'     => '买入数量',
        'leverage'     => '买入数量',
        'fee'        => '手续费',
        'otype'      => '方向',
        'status'     => '状态',
        'created_at' => '委托时间',
        'handle_at'  => '成交时间',
    ];

    public function map($row): array
    {
        return [
            $row->id,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            data_get($row, 'user.name'),
            data_get($row, 'user.staff.username'),
            $row->en_num,
            $row->code,
            $row->buyprice,
            $row->buynum,
            $row->leverage,
            $row->fee,
            $row->otype == 1 ? '买涨' : '买跌',
            $row->status = $this->converted($row->status),
            $row->created_at,
            $row->handle_at,

        ];
    }

    function converted($status){
        switch ($status) {
            case UserEntrusts::STATE_ING:
                return '委托中';
                break;
            case UserEntrusts::STATE_OVER:
                return '已完成';
                break;
            case UserEntrusts::STATE_REV:
                return '已取消';
                break;
            default:
                return 'ERROR';
                break;
        }
    }
}