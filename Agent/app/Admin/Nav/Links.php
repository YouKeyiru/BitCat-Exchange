<?php

namespace App\Admin\Extensions\Nav;

class Links
{
    public function __toString()
    {
        return <<<HTML

<li>
<a class="feedback" href="javascript:void(0);" modal="app-admin-actions-feedback">
      <i class="fa fa-send-o"></i>
      <span>修改密码</span>
    </a>
</li>

HTML;
    }
}