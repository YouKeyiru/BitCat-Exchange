<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\UserConfig;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new User);
        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('account', __('Account'));
                $filter->equal('phone', __('Phone'));
//                $filter->equal('email', __('Email'));
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('recommend.account', __('Recommend'));
                $filter->between('created_at', __('Created at'))->datetime();
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('ext.grade', __('Grade'));
            });
        });
        $grid->column('id', __('Id'));
        $grid->column('account', __('Account'));

        $grid->column('Phone Email', __('Phone Email'))->display(function () {
            $value = $this->phone . '/' . $this->email;
            return trim($value,'/');
        });

        $grid->column('name', __('User name'));
        $grid->column('nickname', __('Nickname'));

        $grid->column('recommend.name', __('Recommend name'))->label();

        $grid->column('recommend.account', __('Recommend account'))->label();

//        $grid->column('ext.grade', __('Grade'));
//        $grid->column('ext.push_user', __('push_user'));
//        $grid->column('ext.team_user', __('team_user'));
//        $grid->column('ext.market_investment', __('market_investment'));
//        $grid->column('ext.node_num', __('node_num'));
//        $grid->column('ext.super_node_num', __('super_node_num'));

        $grid->column('stoped', __('User status'))->switch([
            'on'  => ['value' => 0, 'text' => '正常', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '冻结', 'color' => 'default'],
        ]);
        $grid->column('created_at', __('Created at'));
        // 全部关闭
//        $grid->disableActions();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            $actions->disableView();
//            $actions->disableEdit();

        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('account', __('Account'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('name', __('User name'));
        $show->field('deep', __('Deep'));
        $show->field('stoped', __('Stoped'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        $show->recommend(__('Recommend'), function ($recommend) {
            $recommend->setResource('/admin/users');
            $recommend->username(__('Username'));
            $recommend->name(__('User name'));
            $recommend->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });

        $show->staff(__('Staff'), function ($staff) {
            $staff->setResource('/admin/users');
            $staff->username(__('Username'));
            $staff->name(__('User name'));
            $staff->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });

        $show->agent(__('Agent'), function ($agent) {
            $agent->setResource('/admin/users');
            $agent->username(__('Username'));
            $agent->name(__('User name'));
            $agent->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });

        $show->unit(__('Unit'), function ($unit) {
            $unit->setResource('/admin/users');
            $unit->username(__('Username'));
            $unit->name(__('User name'));
            $unit->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });

        $show->center(__('Center'), function ($center) {
            $center->setResource('/admin/users');
            $center->username(__('Username'));
            $center->name(__('User name'));
            $center->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableDelete();
            });

        return $show;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User);

        $form->text('account', __('Account'))->readonly();

//        $form->mobile('phone', __('Phone'))
//            ->updateRules(['required', "regex:/^1[3456789][0-9]{9}$/", "unique:users,phone,{{id}}"],
//                ['regex' => '手机号格式不正确', 'unique' => '该手机号已被使用'])
//            ->help('请输入客户手机号');

        $form->text('phone', __('Phone'))
            ->updateRules(['required', "unique:users,phone,{{id}}"],
                ['unique' => '该手机号已被使用'])
            ->help('请输入客户手机号');

//        $form->email('email', __('Email'))
//            ->updateRules(['required', "unique:users,email,{{id}}"],
//                ['unique' => '该邮箱已被使用'])
//            ->help('请输入客户邮箱');
        $form->text('name', __('User name'));
        $form->password('password', __('Password'));
        $form->password('payment_password', __('Payment password'));
        $states = [
            'on'  => ['value' => 0, 'text' => '激活', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '冻结', 'color' => 'default'],
        ];
        $form->switch('stoped', __('Status'))->states($states);

//        $states = [
//                'on'  => ['value' => 0, 'text' => '允许', 'color' => 'primary'],
//                'off' => ['value' => 1, 'text' => '禁止', 'color' => 'default'],
//        ];
//        $form->switch('fbtrans', __('Fb status'))->states($states);

        $states = [
            'on'  => ['value' => 1, 'text' => '开启', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
        ];

        $form->switch('userconfig.google_verify', '谷歌验证码开启')->states($states);
        $form->switch('userconfig.google_bind', '谷歌验证码绑定')->states($states);


        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($this->do_password($form->password));
            }

            if ($form->payment_password && $form->model()->payment_password != $form->payment_password) {
                $form->payment_password = bcrypt($this->do_password($form->payment_password));
            }

            if ($form->email && $form->model()->email != $form->email) {
                UserConfig::where('uid', $form->model()->id)
                    ->update(['email_bind' => 1, 'email_verify_at' => now()]);
            }

        });

        return $form;
    }

    private function do_password($password)
    {
        $newPwd = $password;
        for ($i = 1; $i <= 5; $i++) {
            $newPwd = md5($newPwd);
        }
        return $newPwd;
    }
}
