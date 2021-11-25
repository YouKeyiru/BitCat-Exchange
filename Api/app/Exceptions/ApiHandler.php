<?php

namespace App\Exceptions;

use Dingo\Api\Exception\Handler as DingoHandler;
use Exception;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ApiHandler extends DingoHandler
{
    public function handle(Exception $exception)
    {
        $debug = config('app.debug');
        //Throttle
        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'status_code' => 429,
                'message'     => $exception->getMessage()
            ]);
        }

        if ($exception instanceof SecurityException) {
            return response()->json([
                'status_code' => 500,
                'message'     => $exception->getMessage()
            ]);
        }

        //token
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            return response()->json([
                'status_code' => 401,
                'message'     => 'Unauthorized'
            ]);
        }
        //request
        if ($exception instanceof \Dingo\Api\Exception\ValidationHttpException) {
            $errors = $exception->getErrors();
            return response()->json([
                'status_code' => 500,
                'message'     => trans('common.params_error') . ':' . $errors->first(),
            ]);
        }

        //404
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
            $exception instanceof MethodNotAllowedHttpException
        ) {
            return response()->json([
                'status_code' => 404,
                'message'     => '404 Not Found',
            ], 404);
        }

        if ($exception instanceof FatalThrowableError) {
            $response = [];
            $response['status_code'] = 500;
            $response['message'] = $exception->getMessage();
            if ($debug) {
                $response['trace'] = $exception->getTraceAsString();
            }
            return response()->json($response);
        }

        if ($exception instanceof Exception) {
            $response = [];
            $response['status_code'] = 500;
            $response['message'] = $exception->getMessage();
            if ($debug) {
                $response['trace'] = $exception->getTraceAsString();
            }
            return response()->json($response);
        }

        return parent::handle($exception);
    }
}
