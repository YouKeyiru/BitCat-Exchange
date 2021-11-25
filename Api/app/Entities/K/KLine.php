<?php

namespace App\Entities\K;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class KLine
{

    /**
     * @var Client
     */
    public $client;

    public $baseUrl;

    public $pageSize = 20;

    public $type = 1;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10.0]);

        $this->baseUrl = config('k.baseUrl');
    }

    public function setType($type = 1)
    {
        $this->type = $type;
    }

    public function setPageSize($pageSize = 20)
    {
        $this->pageSize = $pageSize;
    }

    public function handle($code)
    {
        $url = $this->baseUrl . '/api/ngetdata/' . $this->pageSize . '/' . $code . '/' . $this->type;
        $httpClientRequest = $this->client->request('get', $url, [
            'headers' => [
                'X-E-O-S-Access-Tstamp' => time()
            ]
        ]);
        $result = json_decode($httpClientRequest->getBody()->getContents());
        if ($result->flag != 1) {
            return [false, $result->err];
        }
        return [true, $result];
    }
}
