<?php

namespace App\Http\Traits;

use Response;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @return mixed
     */
    public function success($data = [], $message = '', $code = FoundationResponse::HTTP_OK)
    {
        if ($message == '') {
            $message = trans('common.operation_success');
        }
        return $this->setStatusCode($code)->status($message, compact('data'));
    }

    /**
     * @param string $message
     * @param int $code
     * @return mixed
     */
    public function failed($message = '', $code = FoundationResponse::HTTP_INTERNAL_SERVER_ERROR)
    {
        if ($message == '') {
            $message = trans('common.operation_failed');
        }
        return $this->setStatusCode($code)->status($message);
    }

    /**
     * @param $message
     * @param array $data
     * @param array $header
     * @return mixed
     */
    protected function status($message, array $data = [], array $header = [])
    {
        $data = array_merge([
            'status_code' => $this->statusCode,
            'message'     => $message,
        ], $data);
        return $this->respond($data, $header);

    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    protected function respond($data, $header = [])
    {
        return Response::json($data, 200, $header);
//        return Response::json($data, $this->getStatusCode(), $header);
    }

    /**
     * @return mixed
     */
    protected function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

}
