<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserWithdrawExporter extends ExcelExporter  implements WithMapping
{
    protected $fileName = '客户提币.xlsx';

    protected $columns = [
        'id'      => 'ID',
        'with_num' => '订单号',
        'uid'   => '用户名',
        'user.phone'   => '手机号',
        'user.name'   => '姓名',
        'user.staff.username'   => '经理',
        'card_name'   =>'开户姓名',
        'card_num'   =>'银行卡号',
        'card_bank'   =>'开户银行',
        'card_branch'   =>'开户支行',
        'money'   =>'金额',
        'handling_fee'   => '手续费',
        'actual' => '实际',
        'status' => '状态',
        'mark' => '说明',
        'created_at'  => '创建时间',
    ];

    public function map($row) : array
    {
        return [
            $row->id,
            $row->with_num,
            data_get($row, 'user.account'),
            data_get($row, 'user.phone'),
            data_get($row, 'user.name'),
            data_get($row, 'user.staff.username'),
            $row->card_name,
            ' '.$row->card_num,
            $row->card_bank,
            $row->card_branch,
            $row->money,
            $row->handling_fee,
            $row->actual,
            $this->getstatus($row->status),
            $row->mark,
            $row->created_at
        ];
    }

    public function getstatus($status){
        switch ($status) {
            case 1:
                return '待审核';
                break;
            case 2:
                return '到账中';
                break;
            case 3:
                return '已拒绝';
                break;
            case 4:
                return '已到账';
                break;
            case 5:
                return '失败';
                break;
            
            default:
                return 'ERROR';
                break;
        }
    }


}