<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Agent\AgentCreate;
use App\Models\AgentUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use QrCode;
use Intervention\Image\Facades\Image;

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
        $admin = Admin::user();
        $grid = new Grid(new AgentUser);
        $grid->disableCreateButton();

        $grid->filter(function ($filter) use ($admin) {
            $filter->disableIdFilter();

            $filter->column(1 / 2, function ($filter) use ($admin) {
                if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
                    $filter->equal('unit_id', __('Unit'))
                        ->select(AgentUser::where('account_type',AgentUser::ACCOUNT_UNIT)->where('center_id', $admin->id)->pluck('username', 'id'))
                        ->load('agent_id','/api/agent');
                    $filter->equal('agent_id', __('Agent'))
                        ->select()
                        ->load('id','/api/agent');
                    $filter->equal('id', __('Staff'))
                        ->select();
//                    $filter->equal('staff.staff_id', __('Staff'))
//                        ->select();
                    $filter->equal('recommend.username', __('Recommend'));
                }

                if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
                    $filter->equal('agent_id', __('Agent'))
                        ->select(AgentUser::where('account_type',AgentUser::ACCOUNT_AGENT)->where('unit_id', $admin->id)->pluck('username', 'id'))
                        ->load('id','/api/agent');
                    $filter->equal('id', __('Staff'))
                        ->select();
                    $filter->equal('recommend.username', __('Recommend'));
                }

                if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
                    $filter->equal('id', __('Staff'))
                        ->select(AgentUser::where('account_type',AgentUser::ACCOUNT_PARTNER)->where('agent_id', $admin->id)->pluck('username', 'id'));
//                    $filter->equal('staff.staff_id', __('Staff'))
//                        ->select();
                    $filter->equal('recommend.username', __('Recommend'));
                }

                $filter->equal('username', __('Username'));

            });
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '创建时间')->datetime();
            });
        });


        if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
            $grid->model()->where('center_id', $admin->id);
        }
        if ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
            $grid->model()->where('unit_id', $admin->id);
        }
        if ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
            $grid->model()->where('agent_id', $admin->id);
        }
        if ($admin->account_type == AgentUser::ACCOUNT_PARTNER) {
            $grid->model()->where('staff_id', $admin->id);
        }

        $grid->model()->orderBy('id', 'desc');
//        $grid->column('id', __('Id'));
        $grid->column('username', __('Username'));
        $grid->column('name', __('User name'));

//        $grid->column('invite_code', __('Invite link'))->display(function($invite_code){
//            if ($this->account_type == AgentUser::ACCOUNT_PARTNER) {
//                return env('APP_LINK').'web/h5/index.html#/reg?inviteCode=' . $invite_code;
//            } else {
//                return '无';
//            }
//        });

//        $grid->image(__('Image'))->display(function(){
//            $link = env('APP_LINK').'web/h5/index.html#/reg?inviteCode='.$this->invite_code;
//            $result = self::userExtendLink($this->id, $link);
//            if ($this->account_type == AgentUser::ACCOUNT_PARTNER) {
//                return $result;
//            } else {
//                return '';
//            }
//        })->lightbox(['width' => 50, 'height' => 50]);

        $grid->column('account_type', __('Account type'))->display(function ($account_type) {
            return AgentUser::ACCOUNT_TYPE[$account_type];
        });
        $grid->column('recommend.username', __('Recommend'));
        if ($admin->account_type == AgentUser::ACCOUNT_CENTER) {
            $grid->column('agent.username', __('Agent'));
            $grid->column('unit.username', __('Unit'));
            $grid->column('center.username', __('Center'));
        } elseif ($admin->account_type == AgentUser::ACCOUNT_UNIT) {
            $grid->column('agent.username', __('Agent'));
            $grid->column('unit.username', __('Unit'));
        } elseif ($admin->account_type == AgentUser::ACCOUNT_AGENT) {
            $grid->column('agent.username', __('Agent'));
        }

        $grid->column('created_at', __('Created at'));
        $grid->disableActions();

        if ($admin->account_type < AgentUser::ACCOUNT_PARTNER) {
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new AgentCreate());
            });
        }

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

        $admin = Admin::user();
        $form->hidden('id', 'ID');
        $form->hidden('account_type', 'account_type');
        $form->hidden('avatar')->default('images/avatar.jpg');
        $form->text('username', __('Username'))
            ->updateRules(['required', "regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{5,20}$/", "min:6", "max:16", "unique:agent_users,username,{{id}}"],
                ['regex' => '必须是字母+数字', 'min' => '必须大于6位', 'max' => '必须小于16位',])
            ->help('登录时使用，请使用字母+数字');

        $form->password('password', __('Password'))->rules('required');
        $form->text('name', __('User name'))->rules('required');
//        if ($admin->account_type > 2) {
//            $form->rate('profit_ratio', __('Profit ratio'))
//                ->rules('required|min:0|max:100')
//                ->default(0);
//            $form->rate('fee_ratio', __('Fee rate'))
//                ->rules('required|min:0|max:100')
//                ->default(0);
//        }

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
                $form->password = bcrypt($form->password);
            }

//            if ($form->account_type > 2) {
//                AgentUser::where('account_type', $form->account_type - 1)
//                    ->where('recommend_id', $form->id)
//                    ->where('profit_ratio', '>', $form->profit_ratio)
//                    ->update(['profit_ratio' => $form->profit_ratio]);
//
//                AgentUser::where('account_type', $form->account_type - 1)
//                    ->where('recommend_id', $form->id)
//                    ->where('fee_ratio', '>', $form->fee_ratio)
//                    ->update(['fee_ratio' => $form->fee_ratio]);
//            }
        });

        return $form;
    }

    /**
     * 推广链接
     * @param $uid
     * @param $link
     * @param string $disk
     * @return string
     */
    public static function userExtendLink($uid, $link, $disk = 'oss')
    {
        return '123';
        $relPath = 'extend';
        $fileName = $relPath . '/' .'extend-link-'.$uid.'.png';
        $qrcode = 'https://psex-images.oss-cn-hongkong.aliyuncs.com/' .$fileName;

        $userInfo = AgentUser::find($uid);
        if($userInfo && $userInfo->extend_img != ''){
            return $userInfo->extend_img;
        }

        if (!file_exists(public_path($relPath))) {
            mkdir(public_path($relPath), 777, true);
        }

        $png = QrCode::format('png')->size(390)->margin(0)
            ->generate($link);

        Storage::disk($disk)->put($fileName,$png);

        $img = Image::make('https://psex-images.oss-cn-hongkong.aliyuncs.com/share/share-extend.png')->resize(1500,2688);

        // 放置二维码图片, 水印位置在原图片的左下角, 距离下边距 10 像素, 距离右边距 15 像素
        $img->insert($qrcode, 'bottom-right', 560, 470);

        // 将处理后的图片重新保存到其他路径
        $img->save($fileName);

        Storage::disk($disk)->put($fileName,file_get_contents($fileName));

        if(file_exists($fileName)){
            unlink ($fileName);
        }

        AgentUser::query()->where('id', $uid)->update(['extend_img'=> $qrcode]);

        return $qrcode;
    }

}
