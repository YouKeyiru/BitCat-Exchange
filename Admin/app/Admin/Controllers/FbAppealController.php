<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Appeal\Agree;
use App\Admin\Actions\Appeal\Refuse;
use App\Models\FbAppeal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class FbAppealController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '法币申诉';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FbAppeal);

        $grid->disableExport(false);
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('order_no', __('Order no'));
                $filter->equal('command', __('Command'));
                $filter->equal('refer', __('Refer'));

            });

            $filter->column(1 / 2, function ($filter) {
                //订单状态1待付款 2已付款 3已确认完成 4 申诉中 5取消 6冻结
                $filter->equal('order_status', __('Order status'))->select([
                    1 => '待付款',
                    2 => '已付款',
                    3 => '已确认完成',
                    4 => '申诉中',
                    5 => '取消',
                    6 => '冻结',
                ]);
                //申述状态  1进行中 2完成 3取消
                $filter->equal('status', __('Pan status'))->select([
                    1 => '进行中',
                    2 => '完成',
                    3 => '取消',
                ]);
                $filter->between('created_at', '创建时间')->datetime();

//                $filter->equal('appeal.account', __('Appeal'));
//                $filter->equal('appeal.phone', __('Appeal').__('Phone'));
//                $filter->equal('beappeal.account', __('Be appeal').__('Account'));
//                $filter->equal('beappeal.phone', __('Be appeal').__('Phone'));
//                $filter->equal('win.account', __('Win appeal').__('Account'));
//                $filter->equal('win.phone', __('Win appeal').__('Phone'));
            });
        });
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('order_no', __('Ordnum'));
        $grid->column('command', __('Command'));
        $grid->column('refer', __('Refer'));
        $grid->column('appeal.account', __('Appeal'));
        $grid->column('beappeal.account', __('Be appeal'));
        $grid->column('win.account', __('Win appeal'));
        $grid->column('type', __('Type'))->using([
            1 => '出售',
            2 => '购买',
        ], '未知')->label([
            1 => 'danger ',
            2 => 'success',
        ], 'warning');
//        $grid->column('reason', __('Reason'));
        //1待付款 2已付款 3已确认完成 4 申诉中 5取消 6冻结
        $grid->column('order_status', __('Order status'))->using([
            1 => '待付款',
            2 => '已付款',
            3 => '已确认完成',
            4 => '申诉中',
            5 => '取消',
            6 => '冻结',
        ], '未知')->label([
            1 => 'info',
            2 => 'primary',
            3 => 'success',
            4 => 'warning',
            5 => 'danger',
            6 => 'warning',
        ], 'warning');

        $grid->column('status', __('Pan status'))->using([
            1 => '进行中',
            2 => '完成',
            3 => '取消',
        ], '未知')->label([
            1 => 'info ',
            2 => 'success',
            3 => 'danger',
        ], 'warning');
        $grid->column('pan_reason', __('Pan reason'));
        $grid->column('created_at', __('Created at'));
        $grid->column('pan_at', __('Pan at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
            if ($actions->row->status == 1) {
                $actions->add(new Agree);
                $actions->add(new Refuse);
            }
        });

        return $grid;
    }


}
