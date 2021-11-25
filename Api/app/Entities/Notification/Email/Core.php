<?php


namespace App\Entities\Notification\Email;


use App\Models\EmailLog;
use Exception;
use Illuminate\Support\Facades\Mail;

abstract class Core
{

    /**
     * @var string 邮箱
     */
    protected $to_email;

    /**
     * @var string 验证码
     */
    protected $v_code;

    /**
     * @var string 签名
     */
    protected $sign;

    /**
     * @var
     */
    protected $mail_class;


    public function __construct($to_email)
    {
        $this->to_email = $to_email;
    }

    /**
     * @throws Exception
     */
    protected function action()
    {
        //初始化
        $this->init();

        //参数校验
        $this->checkParam();

        //发送
        Mail::to($this->to_email)->send(new $this->mail_class($this->sign, $this->v_code));

        //增加日志
        $this->addLog();

        //自定义业务
        $this->otherBusiness();
    }


    private function init()
    {
        $this->setMailClass();
        $this->setSign();
        $this->setCode();
    }

    /**
     * @throws Exception
     */
    protected function checkParam()
    {
        if (!class_exists($this->mail_class)) {
            throw new Exception('class not found');
        }
        if (!$this->v_code) {
            throw new Exception('please set v_code');
        }
        if (!$this->sign) {
            throw new Exception('please set sign');
        }
        if (!$this->to_email) {
            throw new Exception('please set to_email');
        }
    }

    protected function otherBusiness()
    {
    }

    private function addLog()
    {
        EmailLog::create([
            'email' => $this->to_email,
            'code'  => $this->v_code,
            'ip'    => request()->ip()
        ]);
    }

    abstract protected function setMailClass();

    abstract protected function setSign();

    abstract protected function setCode();

    abstract protected static function check($email, $v_code);


}
