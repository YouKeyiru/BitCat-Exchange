<?php

namespace App\Services;

use GuzzleHttp\Client;

class MatchEngineService
{

    /**
     * 撮合引擎入口
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function run($params)
    {
        $uri = config('match.uri');
        $sign = config('match.sign');

        try {
            //请求平仓接口
            $client = new Client([
                'timeout' => 5,
                'verify'  => false
            ]);

            $nowTime = time();
            $headers = [
                'timestamp' => $nowTime,
                'sign'      => strtoupper(sha1($nowTime . $sign))
            ];
            $response = $client->get($uri, [
                'query'   => [
                    'params' => $params,
                ],
                'headers' => $headers
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
            //dd($exception->getMessage());
        }
    }

}
