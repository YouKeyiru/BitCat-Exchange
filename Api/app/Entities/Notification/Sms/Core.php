<?php


namespace App\Entities\Notification\Sms;


interface Core
{
    public function send($phone, $contentType, $area);

    public function check($phone, $code);

    public function addLog();

    public function checkParam();

}
