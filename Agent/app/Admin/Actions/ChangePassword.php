<?php
namespace App\Admin\Actions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class ChangePassword extends Action
{
    protected $selector = '.change-password';
    public function handle(Request $request)
    {
        $admin = Admin::user();
        $admin->password = bcrypt($request->get('password'));
        $encrypt_password = base64_encode($request->get('password'));
        $admin->encrypt_password = $encrypt_password;
        $admin->save();

        return $this->response()->success('修改成功')->topCenter();
    }
    public function form()
    {
        $this->password('password','密码')->rules('required');
    }
    public function html()
    {
        return <<<HTML
<li>
    <a class="change-password" href="javascript:void(0);">
      <i class="fa fa-send-o"></i>
      <span>修改密码</span>
    </a>
</li>
HTML;
    }
}