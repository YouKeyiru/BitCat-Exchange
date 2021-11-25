<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExporter extends ExcelExporter implements WithMapping
{
    protected $fileName = '客户管理.xlsx';

    protected $columns = [
        'id'           => 'ID',
        'account'      => '账号',
        'phone'        => '手机号',
        'email'        => '邮箱',
        'name'         => '姓名',
        'stoped'       => '状态',
        'recommend_id' => '推荐人',
        'staff_id'     => '经理',
        'agent_id'     => '代理商',
        'unit_id'      => '会员单位',
        'center_id'    => '运营中心',
        'created_at'   => '创建时间',

    ];

    public function map($row): array
    {
        return [
            $row->id,
            $row->account,
            $row->phone,
            $row->email,
            $row->name,
            $row->stoped ? '冻结' : '正常',
            data_get($row, 'recommend.account'),
            data_get($row, 'staff.username'),
            data_get($row, 'agent.username'),
            data_get($row, 'unit.username'),
            data_get($row, 'center.username'),
            $row->created_at
        ];
    }
}