<?php


namespace App\Entities\Notification\Sms;

use App\Models\SmsLog;
use Exception;
use Illuminate\Support\Facades\Cache;

class Chinese implements Core
{
    protected $baseUrl = 'http://utf8.api.smschinese.cn/';
    public $area;
    public $phone;
    public $content;
    public $code;
    public $errCode;
    private $key;
    private $uid;
    private $signature;
    private $ttl;
    private $prefix = 'Chinese-sms-';

    const ERROR_CODE = [
        1   => 'OK',
        -3  => '短信数量不足',
        -4  => '手机号格式不正确',
        -6  => 'IP限制',
        -11 => '该用户被禁用',
        -14 => '短信内容出现非法字符',
        -41 => '手机号码为空',
        -42 => '短信内容为空',
        -51 => '短信签名格式不正确',
        -52 => '短信签名太长',
    ];

    public function __construct($config)
    {
        $this->key = $config['key'];
        $this->uid = $config['uid'];
        $this->ttl = $config['ttl'];
        $this->signature = $config['signature'];
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
        $this->errCode = self::jsonPost($this->baseUrl, [
            'Uid'     => $this->uid,
            'KeyMD5'  => strtoupper(md5($this->key)),
            'smsMob'  => $this->phone,
            'smsText' => $this->content,
        ]);
        if ($this->errCode < 0) {
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
        $this->content = $this->signature . Content::getMsg($contentType, $this->code, \App::getLocale());
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

        $header[] = "Content-Type: application/x-www-form-urlencoded";
//        $header[] = "Bi-Gi-Jsp-Cross-W: MEeP8JwTFH52yEpUgJERRKchYRMjq07O";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        return $response;
    }

}
