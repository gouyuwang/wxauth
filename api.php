<?php

class Controller
{
    const APPID = "wx552593a8e7c4b0de";
    const SECRET = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

    /**
     * 微信授权回调，原来是作为redirect_url使用的，现在修改下返回值即可满足新需求
     * @param array $request
     * @return array
     */
    public function wxCall(array $request)
    {
        try {
            // code->access_token
            $ret = json_decode(file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::APPID . "&secret=" . self::SECRET . "&code={$request['code']}&grant_type=authorization_code"), true);
            // access_token->profile
            if (1 == $request['state']) {
                $ret = json_decode(file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token={$ret['access_token']}&openid={$ret['openid']}&lang=zh_CN"), true);
            }
            return $this->response(0, 'success', $ret);
        } catch (\Exception $e) {
            return $this->response(4000, $e->getMessage());
        }
    }

    /**
     * 获取授权地址
     * @param array $request
     * @return array
     */
    public function wxAuthUrl(array $request)
    {
        $callback = urlencode($request['callback']);
        $state = @$request['type'];
        if (1 == $state) {
            $scope = "snsapi_userinfo";
        } else {
            $scope = "snsapi_base";
        }
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".self::APPID."&redirect_uri={$callback}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
        return $this->response(0, 'success', $url);
    }

    /**
     * 返回数据
     * @param int $code
     * @param string $msg
     * @param string $data
     * @return array
     */
    protected function response($code = 4000, $msg = 'fail', $data = '')
    {
        return [
            'code' => is_numeric($code) ? intval($code) : 4000,
            'msg' => empty($msg) ? (intval($code) === 0 ? 'success' : 'fail') : $msg,
            'data' => $data
        ];
    }
}

$controller = new Controller();
$action = @$_REQUEST['action'];
if (method_exists($controller, $action)) {
    header("Content-Type: application/json,charset=utf8;");
    $ret = call_user_func_array([$controller, $action], [$_REQUEST]);
    echo is_array($ret) ? json_encode($ret) : $ret;
    exit;
}