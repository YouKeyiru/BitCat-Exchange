<?php

namespace App\Entities\Wallet;

use Illuminate\Support\Facades\Log;

class Base
{
    protected $config;
    protected $url;

    public function __construct()
    {
        $this->config = config('eth');
        $this->url = $this->config[$this->config['mode']];
    }

    /**
     * get curl 请求
     * @param $api
     * @param array $data
     * @param bool $debug
     * @return mixed
     */
    public function curl_get($api, $data = [], $debug = false)
    {
        $api = $this->config['url'][$api];

        $url = $this->url . $api . '?' . http_build_query($data);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证 证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $res = curl_exec($ch);
        Log::info($url);

        if (curl_errno($ch) && $debug) {
            curl_close($ch);
            die;
        }
        curl_close($ch);
        return json_decode($res, true);
    }

    /**
     * curl get请求
     * @param $api
     * @param array $data
     * @param bool $debug
     * @return mixed
     */
    function curl_post($api, $data = [], $debug = false)
    {
        $api = $this->config['url'][$api];

        $url = $this->url . '/' . trim($api, '/');
        /*        echo $url;
                print_r($data);
                die;*/
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证 证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //请求方式
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //传递参数
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        //以json 传值时 设置
        /*            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($data))
                ));*/
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $res = curl_exec($ch);


        if (curl_errno($ch) && $debug) {
            curl_close($ch);
            die;
        }
        curl_close($ch);

        return json_decode($res, true);
    }

}
