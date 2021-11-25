<?php

namespace App\Http\Controllers\Api;

use App\Models\Authentication;
use App\Services\AuthenticationService;
use Dingo\Api\Http\Request;
use Exception;
use Validator;

/**
 * @Resource("Authentication")
 * Class AuthenticationController
 * @package App\Http\Controllers\Api
 */
class AuthenticationController extends BaseController
{
    protected $title = '用户身份认证';

    /**
     * 认证信息
     * @Post("/authentication/info")
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $info = $user->auth()
            ->select('name', 'card_id', 'front_img', 'back_img', 'handheld_img', 'status', 'refuse_reason')
            ->where('uid', $user->id)
            ->orderBy('id', 'desc')
            ->first();
        return $this->success($info);
    }

    /**
     * 初级身份认证
     * @Post("/authentication/primary_certification")
     * @Request({"name": "foo","card_id": "4567895613"})
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function primaryCertification(Request $request)
    {
        $user = \Auth::user();

        $validator = Validator::make(
            $request->all(),
            [
                'name'    => 'required',
                'card_id' => 'required|max:32',
            ],
            [
                'name.required'    => trans('user.input_username'),
                'card_id.required' => trans('user.input_card_id'),
            ]
        );
        if ($validator->fails()) {
            return $this->failed($validator->errors()->all()[0]);
        }
        $input = $request->input();
        $card = Authentication::where('card_id', $input['card_id'])
            ->first();

        if (!empty($card)) {
            return $this->failed(trans('user.card_id_registered'));
        }

        $log = Authentication::where('uid', $user->id)
            ->where('status', '>=', Authentication::PRIMARY_CHECK)
            ->first();
        if ($log) {
            return $this->failed(trans('user.auth_success'));
        }

        \DB::beginTransaction();
        try {
            //三方认证验证
            $check_result = AuthenticationService::check_auth($input['name'], $input['card_id']);

            Authentication::create([
                'uid'              => $user->id,
                'name'             => $input['name'],
                'card_id'          => $input['card_id'],
                'status'           => Authentication::PRIMARY_CHECK,
                'real_name_result' => $check_result,
            ]);

            $user->authentication = Authentication::PRIMARY_CHECK;
            $user->name = $input['name'];
            $user->save();
//            $user->config->security_level += 1;
            $user->config->save();

            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }

    /**
     * 高级身份认证
     * @Post("/authentication/primary_certification")
     * @Request({"front_img": "/image/a.jpg","card_id": "/image/a.jpg","handheld_img":"/image/a.jpg"})
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function advancedCertification(Request $request)
    {
        $user = \Auth::user();
        $validator = Validator::make(
            $request->all(),
            [
                'front_img'    => 'required',
                'back_img'     => 'required',
                'handheld_img' => 'required',
            ],
            [
                'front_img.required'    => trans('user.input_front_img'),
                'back_img.required'     => trans('user.input_back_img'),
                'handheld_img.required' => trans('user.input_handheld_img'),
            ]
        );
        if ($validator->fails()) {
            return $this->failed($validator->errors()->all()[0]);
        }

        $input = $request->input();

        \DB::beginTransaction();
        try {
            $primary = Authentication::where('uid', $user->id)
                ->where('status', '>=', Authentication::PRIMARY_CHECK)
                ->first();
            if (empty($primary)) {
                throw new Exception(trans('user.primary_certification'));
            }
            $advanced = Authentication::where('uid', $user->id)
                ->where('status', Authentication::ADVANCED_CHECK_AGREE)
                ->first();
            if (!empty($advanced)) {
                throw new Exception(trans('user.auth_success'));
            }
            //更新认证表和会员表状态
            $primary->status = Authentication::ADVANCED_WAIT_CHECK;
            $primary->front_img = $input['front_img'];
            $primary->back_img = $input['back_img'];
            $primary->handheld_img = $input['handheld_img'];
            $primary->save();
            $user->authentication = Authentication::ADVANCED_WAIT_CHECK;
            $user->save();
            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            return $this->failed($exception->getMessage());
        }
        return $this->success();
    }



}
