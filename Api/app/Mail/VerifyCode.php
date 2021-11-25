<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyCode extends Mailable
{
    use Queueable, SerializesModels;

    protected $sign;
    protected $code;

    /**
     * VerifyCode constructor.
     * @param $sign
     * @param $code
     */
    public function __construct($sign, $code)
    {
        $this->sign = $sign;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.verify', ['code' => $this->code, 'sign' => $this->sign]);
    }
}
