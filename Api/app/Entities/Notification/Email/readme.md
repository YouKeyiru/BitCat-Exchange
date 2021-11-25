```php
    use App\Entities\Notification\Email\VerifyMailHandel;

    //发送邮件
    $mail = new VerifyMailHandel('365888920@qq.com');
    $result = $mail->send();
    if (!$result) {
        dd($mail->getError());
    }

    //验证
    VerifyMailHandel::check('365888920@qq.com', 655540);

```

