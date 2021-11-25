<?php


namespace App\Http\Middleware;

use App\Exceptions\SecurityException;
use Closure;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiSecurity
{
    protected $input;

    private $config;

    public function __construct(Request $request)
    {
        $this->config = config('api_security');
        $this->input = array_filter($request->input());
    }

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws SecurityException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->config['status']['timeout']) {
            //请求时间戳超时验证
            $this->CheckTimeout($request);
        }

        if ($this->config['status']['sign']) {
            //参数签名验证
            $this->CheckSign();
        }

        if ($this->config['status']['nonce']) {
            //防重放机制
            $this->CheckNonce();
        }

        return $next($request);
    }

    /**
     * @throws SecurityException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function CheckNonce()
    {
        $nonce_str = strtoupper(md5($this->input['nonce_str'] . $this->input['sign']));
        if (Cache::get($nonce_str)) {
            throw new SecurityException('Replay request');
        } else {
            Cache::set($nonce_str, 1, config('api_security.timeout'));
        }
    }

    /**
     * 获取key
     * @return string
     */
    public function GetKey()
    {
        return config('api_security.secret_key');
    }

    /**
     * 获取签名
     * @return mixed
     */
    public function GetSign()
    {
        return $this->input['sign'];
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->input as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return string
     */
    public function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->input);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->GetKey();

        //签名步骤三：HMAC-SHA256
        $string = hash_hmac("sha256", urlencode($string), $this->GetKey());

        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * 判断签名是否存在
     * @return true 或 false
     **/
    public function IsSignSet()
    {
        return array_key_exists('sign', $this->input);
    }

    /**
     * 检测签名
     * @return bool
     * @throws SecurityException
     */
    public function CheckSign()
    {
        if (!$this->IsSignSet()) {
            throw new SecurityException('签名错误！');
        }
        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            //签名正确
            return true;
        }
        throw new SecurityException('签名错误！');
    }

    /**
     * 检测请求超时
     * @param Request $request
     * @throws SecurityException
     */
    protected function CheckTimeout(Request $request)
    {
        $timestamp = $request->header('timestamp');
        if (time() - $timestamp > config('api_security.timeout')) {
            throw new SecurityException('Request timeout！');
        }
    }


}
