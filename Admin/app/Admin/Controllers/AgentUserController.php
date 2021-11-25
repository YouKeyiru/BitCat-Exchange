<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Agent\AgentCreate;
use App\Admin\Actions\Agent\Recharge;
use Illuminate\Support\MessageBag;
use App\Admin\Extensions\AgentUserExporter;
use App\Models\AgentUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '代理商';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentUser);
        $grid->disableCreateButton();
        $grid->disableExport(false);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('username', __('Username'));
                $account_type = config('system.account_type');
                unset($account_type[0]);
                $filter->equal('account_type', __('Account type'))->select($account_type);
                $filter->between('created_at', __('Created at'))->datetime();
                $filter->between('updated_at', __('Updated at'))->datetime();
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('center_id', __('Center'))
                    ->select(AgentUser::where('account_type',4)->pluck('username', 'id'))
                    ->load('unit_id','/api/agent');
                $filter->equal('unit_id', __('Unit'))
                    ->select()
                    ->load('agent_id','/api/agent');
                $filter->equal('agent_id', __('Agent'))
                    ->select();
            });

        });
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('username', __('Username'))->modal('显示密码', function ($model) {
            if(!$this->encrypt_password) {
                return '无';
            }
            return base64_decode($this->encrypt_password);
        });
        $grid->column('name', __('User name'));
        $grid->column('account_type', __('Account type'))->using(AgentUser::ACCOUNT_TYPE)->label();
        $grid->column('recommend.username', __('Recommend'));
        $grid->column('agent.username', __('Agent'));
        $grid->column('unit.username', __('Unit'));
        $grid->column('center.username', __('Center'));
//        $grid->column('profit_ratio', __('Profit ratio'));
//        $grid->column('fee_ratio', __('Fee rate'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();

//            $actions->add(new Recharge);
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new AgentCreate());
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
        $show = new Show(AgentUser::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('name', __('User name'));
        $show->field('profit_ratio', __('Profit ratio'));
        $show->field('account_type', __('Account type'))->as(function ($account_type) {
            return config('system.account_type')[$account_type];
        });
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

        $form = new Form(new AgentUser);

        $form->hidden('avatar')->default('images/avatar.jpg');
        $form->hidden('id', 'ID');
        $form->hidden('recommend_id', 'recommend_id');
        $form->hidden('encrypt_password', 'encrypt_password');
        $form->hidden('account_type', 'account_type');
        $form->text('username', __('Username'))
            ->updateRules(['required', "regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{5,20}$/", "min:6", "max:16", "unique:agent_users,username,{{id}}"],
                ['regex' => '必须是字母+数字', 'min' => '必须大于6位', 'max' => '必须小于16位',])
            ->help('登录时使用，请使用字母+数字');

        $form->password('password', __('Password'))->rules('required');
        $form->text('name', __('User name'))->rules('required');

//        $form->rate('profit_ratio', __('Profit ratio'))
//                ->rules('required|min:0|max:100')
//                ->default(0);
//        $form->rate('fee_ratio', __('Fee rate'))
//                ->rules('required|min:0|max:100')
//                ->default(0);


        //毙掉一些按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->saving(function (Form $form) {
            $recommend = AgentUser::find($form->recommend_id);
            if(!empty($recommend)){
                if($form->profit_ratio > $recommend->profit_ratio){
                    $error = new MessageBag([
                        'title' => '不能大于上级盈亏比例',
                        'message' => '上级'.$recommend->username.'盈亏比例为'.$recommend->profit_ratio.'%',
                    ]);
                    return back()->with(compact('error'));
                }
                if($form->fee_ratio > $recommend->fee_ratio){
                    $error = new MessageBag([
                        'title' => '不能大于上级返佣比例',
                        'message' => '上级'.$recommend->username.'返佣比例为'.$recommend->fee_ratio.'%',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            if ($form->account_type > 2) {

                AgentUser::where('account_type', $form->account_type - 1)
                    ->where('recommend_id', $form->id)
                    ->where('profit_ratio', '>', $form->profit_ratio)
                    ->update(['profit_ratio' => $form->profit_ratio]);

                AgentUser::where('account_type', $form->account_type - 1)
                    ->where('recommend_id', $form->id)
                    ->where('fee_ratio', '>', $form->fee_ratio)
                    ->update(['fee_ratio' => $form->fee_ratio]);
            }

            if ($form->password && $form->model()->password != $form->password) {
                $form->encrypt_password = base64_encode($form->password);
                $form->password = bcrypt($form->password);
            }


        });

        return $form;
    }
}
