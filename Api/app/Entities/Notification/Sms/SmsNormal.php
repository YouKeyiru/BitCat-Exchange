<?php


namespace App\Entities\Notification\Sms;

use App\Models\SmsLog;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsNormal implements Core
{
    protected $baseUrl = 'http://sms.106jiekou.com/utf8/sms.aspx';
    protected $externalUrl = 'http://sms.106jiekou.com/utf8/worldapi.aspx';
    public $area;
    public $phone;
    public $content;
    public $code;
    public $errCode;
    private $key;
    private $uid;
    private $signature;
    private $ttl;
    private $prefix = '106-sms-';

    const ERROR_CODE = [
        100   => 'OK',
        101  => '验证失败',
        102  => '手机号码格式不正确',
        103  => '会员级别不够',
        104 => '内容未审核',
        105 => '内容过多',
        106 => '账户余额不足',
        107 => 'Ip受限',
        108 => '手机号码发送太频繁，请换号或隔天再发',
        109 => '帐号被锁定',
        110 => '手机号发送频率持续过高，黑名单屏蔽数日',
        120 => '系统升级',
    ];


    public function __construct($config)
    {
        $this->key = $config['key'];
        $this->uid = $config['uid'];
        $this->ttl = $config['ttl'];
//        $this->signature = $config['signature'];

    }

    /**
     * @param $phone
     * @param $contentType
     * @param int $area
     * @return bool
     * @throws Exception
     */
    public function send($phone, $contentType = 1, $area = 86): bool
    {
        $this->init($area, $phone, $contentType);
        $this->checkParam();

        //测试内容
//        $this->content = '您的验证码是：【变量】。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
//        $this->content = '尊敬的用户您已经注册成功，用户名：{0} 密码：{1} 感谢您的注册！';
//        $this->content = '456123。如需帮助请联系客服。';
        if ($area == 86)
            $url = $this->baseUrl;
        else{
            $url = $this->externalUrl;
            $mobile = $area . $this->phone;
        }

        $this->errCode = self::jsonPost($url, [
            'account'     => $this->uid,
            'password'  => $this->key,
            'mobile'  => $area == '86' ? $this->phone : $mobile,
            'content' => urlencode($this->content),
        ]);
        if ($this->errCode < 0) {
            Log::error('send_error', [$this->errCode]);
            return false;
        }
        $this->otherBusiness();
        return true;
    }

    /**
     * 校验
     * @param $phone
     * @param $code
     * @return bool
     * @throws Exception
     */
    public function check($phone, $code): bool
    {
        $v_code = Cache::get($this->prefix . $phone);
        if (!$v_code || $v_code != $code) {
            return false;
        }
        Cache::delete($this->prefix . $phone);
        return true;
    }

    /**
     * 校验数据
     * @throws Exception
     */
    public function checkParam()
    {
        if (!$this->phone) {
            throw new Exception('未设置发送手机号');
        }
        if (!$this->content) {
            throw new Exception('未设置发送内容');
        }
        if (!$this->uid) {
            throw new Exception('未设置UID');
        }
        if (!$this->key) {
            throw new Exception('未设置密钥');
        }
//        if (!$this->signature) {
//            throw new Exception('未设置签名');
//        }
        if (!$this->ttl) {
            throw new Exception('未设置过期时间');
        }
    }

    /**
     * 增加日志
     */
    public function addLog()
    {
        SmsLog::query()->create([
            'area_code' => $this->area,
            'phone'     => $this->phone,
            'code'      => $this->code,
            'content'   => $this->content,
            'ip'        => request()->ip(),
            'result'    => self::ERROR_CODE[$this->errCode]
        ]);
    }

    /**
     * 初始化数据
     * @param $area
     * @param $phone
     * @param $contentType
     */
    public function init($area, $phone, $contentType)
    {
        $this->code = mt_rand(100000, 999999);
        $this->area = $area;
        $this->phone = $phone;
        $lang = request()->header('lang');

        $this->content = sprintf('您的验证码是：%s。请不要把验证码泄露给其他人。如非本人操作，可不用理会！',$this->code);

//        $this->content = Content::getMsg($contentType, $this->code, $lang);
    }

    /**
     * 自定义业务
     */
    public function otherBusiness()
    {
        $this->addLog();
        Cache::set($this->prefix . $this->phone, $this->code, $this->ttl);
    }

    protected static function jsonPost($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);

//        $header[] = "Content-Type: application/x-www-form-urlencoded";
//        $header[] = "Bi-Gi-Jsp-Cross-W: MEeP8JwTFH52yEpUgJERRKchYRMjq07O";
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        return $response;
    }

    /**
     * get请求
     * @param $account
     * @param $password
     * @param $mobile
     * @param $content
     * @param $area_code
     * @return string
     */
    protected  function jsonGet($account, $password, $mobile, $content, $area_code){

        $url = '';
        $client = new Client();
        if ($area_code == '+86')
            $url = $this->baseUrl;
        else
            $url = $this->externalUrl;

        $other_info = 'account=' . $account . '&password=' . $password . '&mobile=' . $mobile .'&content=' . urlencode($content);
        $url = $url . '?'. $other_info;

        $result = $client->get($url);
        return $result->getBody()->getContents();
    }

}
