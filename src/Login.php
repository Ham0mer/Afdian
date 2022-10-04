<?php
/**
 * 爱发电登录类
 * @author : hammer <oio_qwq@proton.me>
 * Licensed ( https://lsls.me )
 * Copyright (c) 2022~2099 https://lsls.me All rights reserved.
 */
namespace Ham0mer\Afdian;
class Login{
    const GET_AUTH_CODE_URL = "https://afdian.net/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://afdian.net/api/oauth2/access_token";

    private $client_id ,$client_secret,$siteurl,$type;
    /**
     * @var string
     */

    public function __construct($client_id ,$client_secret,$siteurl,$type)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->callback = $siteurl.'return.php';
        $this->type = $type;
        $this->http = new HttpRequest();
    }
    public function login()
    {
        $state = md5(uniqid(rand(), TRUE));
        if($this->type =='tp'){
            session('afdianuser.state', $state);
        }else{
            $_SESSION['afdianuser.state'] = $state;
        }
        $param = [
            "client_id" => $this->client_id,
            "redirect_uri" => urlencode($this->callback),
            "state" => $state,
            "response_type" => "code",
            "scope" => "basic"
        ];

        $url =  self::GET_AUTH_CODE_URL.'?'.http_build_query($param);

        header("Location:$url");
    }
    public function callback($code,$state)
    {
        if($this->type =='tp'){
            $state2 = session('afdianuser.state');
        }else{
            $state2 = $_SESSION['afdianuser.state'];
        }
        if ($state != $state2) {
            return "验证过期，请重新操作";
        }
        $param = [
            "code" => $code,
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->callback,
            "grant_type" => "authorization_code"
        ];
        $url = self::GET_ACCESS_TOKEN_URL.'?'.http_build_query($param);
        $result = json_decode($this->http->query($url),true);
        return $result['user_id'];
    }
}