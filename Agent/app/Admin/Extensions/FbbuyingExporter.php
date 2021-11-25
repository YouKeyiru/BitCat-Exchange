<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class FbBuyingExporter extends ExcelExporter  implements WithMapping
{
    protected $fileName = '法币购买.xlsx';

    protected $columns = [
        'id'      => 'ID',
        'order_no' => '订单号',
        'uid'   => '用户名',
        'user.phone'   => '手机号',
        'uid'   => '用户名',
        'trans_num'   =>'交易数量',
        'deals_num'   =>'成交数量',
        'price'   =>'单价',
        'totalprice'   => '总价',
        'sxfee' => '手续费',
        'min_price' => '最小限额',
        'max_price' => '最大限额',
        'pay_bank' => '银行卡',
        'pay_alipay' => '支付宝',
        'pay_wx' => '微信',
        'status' => '状态',
        'notes' => '卖家备注',
        'created_at'  => '创建时间',
    ];

    public function map($row) : array
    {
        return [
            $row->id,
            $row->order_no,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            $row->trans_num,
            $row->deals_num,
            $row->price,
            $row->totalprice,
            $row->sxfee,
            $row->min_price,
            $row->max_price,
            $row->pay_bank ? '支持':'不支持',
            $row->pay_alipay ? '支持':'不支持',
            $row->pay_wx ? '支持':'不支持',
            $this->getstatus($row->status),
            $row->notes,
            $row->created_at
        ];
    }

    public function getstatus($status){
        switch ($status) {
            case 1:
                return '进行中';
                break;
            case 2:
                return '完成';
                break;
            case 3:
                return '撤单';
                break;

            default:
                return 'ERROR';
                break;
        }
    }


}
