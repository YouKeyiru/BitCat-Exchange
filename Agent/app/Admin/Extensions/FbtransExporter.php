<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class FbTransExporter extends ExcelExporter  implements WithMapping
{
    protected $fileName = '法币交易.xlsx';

    protected $columns = [
        'id'      => 'ID',
        'order_no' => '订单号',
        'jy_order'=>'交易单号',
        'chu_uid'   => '出售人用户名',
        'chu.phone'   => '出售人手机号',
        'chu.name'   => '出售人姓名',
        'gou_uid'   => '购买人用户名',
        'gou.phone'   => '购买人手机号',
        'gou.name'   => '购买人姓名',
        'price'   =>'单价',
        'total_num' => '数量',
        'total_price'   =>'总价格',
        'refer'   => '付款参考号',
        'sxfee' => '手续费',
        'min_price' => '最小限额',
        'max_price' => '最大限额',
        'pay_bank' => '银行卡',
        'pay_alipay' => '支付宝',
        'pay_wx' => '微信',
        'status' => '状态',
        'type' => '类型',
        'created_at'  => '创建时间',
    ];

    public function map($row) : array
    {
        return [
            $row->id,
            $row->order_no,
            $row->jy_order,
            data_get($row, 'chu.account'),
            data_get($row, 'chu.phone'),
            data_get($row, 'chu.name'),
            data_get($row, 'gou.account'),
            data_get($row, 'gou.phone'),
            data_get($row, 'gou.name'),
            $row->price,
            $row->total_num,
            $row->total_price,
            $row->refer,
            $row->sxfee,
            $row->min_price,
            $row->max_price,
            $row->pay_bank ? '支持':'不支持',
            $row->pay_alipay ? '支持':'不支持',
            $row->pay_wx ? '支持':'不支持',
            $this->getstatus($row->status),
            $row->type == 1 ? '出售':'购买',
            $row->created_at
        ];
    }

    public function getstatus($status){
        switch ($status) {
            case 1:
                return '待付款';
                break;
            case 2:
                return '已付款';
                break;
            case 3:
                return '已确认完成';
                break;
            case 4:
                return '申诉中';
                break;
            case 5:
                return '取消';
                break;
            case 6:
                return '冻结';
                break;

            default:
                return 'ERROR';
                break;
        }
    }


}
