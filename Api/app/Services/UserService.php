<?php


namespace App\Services;


use App\Entities\Notification\Email\VerifyMailHandel;
use App\Entities\Notification\SmsHandel;
use App\Models\AgentUser;
use App\Models\User;
use App\Models\UserPosition;

class UserService
{

    /**
     * 检测用户名是否存在
     * @param $field
     * @param $username
     * @return mixed
     */
    public function isRegister($field, $username)
    {
        return User::where($field, $username)->first();
    }

    /**
     * 创建UID
     */
    public static function createUID()
    {
        $prefix = mt_rand(1, 9);
        $other = mt_rand(100000, 999999);
        $account = $prefix . $other;
        if (User::where('account', $account)->first()) {
            return static::createUID();
        }
        return $account;
    }

    /**
     * 注册后业务
     * @param User $user
     * @throws \Exception
     */
    public function afterRegister(User $user)
    {
        self::setRelationship($user);
        self::setConfig($user);
    }

    /**
     * 设置手机号
     * @param User $user
     * @param $phone
     */
    public static function setPhone(User $user, $phone)
    {
        $user->phone = $phone;
        $user->config->phone_verify_at = now();
        $user->config->phone_bind = 1;
        $user->config->security_level += 1;
        $user->save();
        $user->config->save();
    }

    /**
     * 设置邮箱
     * @param User $user
     * @param $email
     */
    public static function setEmail(User $user, $email)
    {
        $user->email = $email;
        $user->config->email_verify_at = now();
        $user->config->email_bind = 1;
        $user->config->security_level += 1;
        $user->save();
        $user->config->save();
    }

    /**
     * 绑定谷歌认证
     * @param User $user
     * @param $google_secret
     */
    public static function setGoogle(User $user, $google_secret)
    {
        $user->config->google_secret = $google_secret;
        $user->config->google_bind = 1;
        $user->config->google_verify = 1;
        $user->config->security_level += 1;
        $user->config->save();
    }

    /**
     * 更新头像
     * @param User $user
     * @param $avatar
     */
    public static function setAvatar(User $user, $avatar)
    {
        $user->avatar = $avatar;
        $user->save();
    }

    /**
     * 设置用户 config
     * @param $user
     */
    protected static function setConfig($user)
    {
        $config = $user->config()->create(['uid' => $user->id]);
        if ($user->email) {
            $config->email_verify_at = now();
            $config->email_bind = 1;
        }
        if ($user->phone) {
            $config->phone_verify_at = now();
            $config->phone_bind = 1;
        }
        $config->save();
    }

    /**
     * 设置推荐关系
     * @param $user
     * @throws \Exception
     */
    protected static function setRelationship($user)
    {
        $deep = 0;
        $recommend_id = 0;
        $relationship = 0;
        $center_id = 0;
        $unit_id = 0;
        $agent_id = 0;
        $staff_id = 0;

        $inviteCode = request()->input('invite_code','');
        if($inviteCode){
            if ($inviteCode == '666888') {
                //万能邀请码
                $deep = 0;
                $recommend_id = 0;
                $relationship = 0;
                $center_id = 0;
                $unit_id = 0;
                $agent_id = 0;
                $staff_id = 0;
            } else {
                $inviteInfo = User::where('account', $inviteCode)->first();
                if ($inviteInfo) {
                    //推荐人进入
                    $deep = $inviteInfo->deep + 1;
                    $recommend_id = $inviteInfo->id;
                    $relationship = $inviteInfo->relationship . ',' . $inviteInfo->id;
                    $center_id = $inviteInfo->center_id;
                    $unit_id = $inviteInfo->unit_id;
                    $agent_id = $inviteInfo->agent_id;
                    $staff_id = $inviteInfo->staff_id;
                } else {
                    //代理进入
                    $inviteInfo = AgentUser::where('username', $inviteCode)
                        ->where('account_type', AgentUser::ACCOUNT_STAFF)
                        ->first();
                    if (!$inviteInfo) {
                        throw new \Exception(trans('user.invite_code_error'));
                    }
                    $deep = 0;
                    $recommend_id = 0;
                    $relationship = 0;
                    $center_id = $inviteInfo->center_id;
                    $unit_id = $inviteInfo->unit_id;
                    $agent_id = $inviteInfo->agent_id;
                    $staff_id = $inviteInfo->id;
                }
            }
        }

        $user->recommend_id = $recommend_id;
        $user->relationship = $relationship;
        $user->center_id = $center_id;
        $user->unit_id = $unit_id;
        $user->agent_id = $agent_id;
        $user->staff_id = $staff_id;
        $user->deep = $deep;

        $user->save();

        self::setPosition($user->recommend_id, $user->id);
    }

    public static function setPosition($pid, $uid)
    {
        $list = UserPosition::where('uid', $pid)->get()->toArray();
        //$time = date('Y-m-d H:i:s');
        $newList = [];
        $newList[0]['uid'] = $uid;
        $newList[0]['pid'] = $pid;
        $newList[0]['lay'] = 1;
//        $newList[0]['created_at'] = $time;
        if ($list) {
            foreach ($list as $k => $v) {
                $newList[$k + 1]['uid'] = $uid;
                $newList[$k + 1]['pid'] = $v['pid'];
                $newList[$k + 1]['lay'] = $v['lay'] + 1;
//                $newList[$k + 1]['created_at'] = $time;
            }
        }
        // 可能存在内存溢出
        $chunk_result = array_chunk($newList, 20);
        foreach ($chunk_result as $val) {
            UserPosition::insert($val);
        }
    }


    //校验
    public function checkStore($username, $v_code)
    {
        $type = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        // 验证验证码
        if ($type == 'email') {
            $verify_result = VerifyMailHandel::check($username, $v_code);
        } else {
            $verify_result = SmsHandel::check($username, $v_code);
        }

        return $verify_result;
    }

}
