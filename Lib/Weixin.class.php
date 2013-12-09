<?php
class Weixin
{

    private $appID = APPID;
    private $appsecret = SECRET;


    public function __construct()
    {
        $this->_getToken();
    }

    public function _calls($url, $p, $m = "GET")
    {
        $tk = array('access_token' => "{$this->token}");
        $ch = curl_init();
        if ($m == "GET") {
            $url = "$url?" . http_build_query(array_merge($p, $tk));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        } else {
            $url = "$url?" . http_build_query($tk);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($p));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        }
        echo 'U:' . $url . "<br>";
        echo 'P:' . http_build_query($p) . "<br>";

        echo '<br />';
        echo '<br />';
        $result = curl_exec($ch);
        return $result;
    }

    public function _call($url, $p, $m = 'GET')
    {
        $tk = array('access_token' => "{$this->token}");
        $postdata = http_build_query(array_merge($p, $tk));
        if ($m == 'GET') {
            w("get:$url?$postdata");
            return file_get_contents("$url?$postdata");
        } else {
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type:application/x-www-form-urlencoded',
                    'content' => $postdata,
                    'timeout' => 15 * 60 // 超时时间（单位:s）
                )
            );
            $context = stream_context_create($options);
            $result = file_get_contents("$url?".http_build_query($tk), false, $context);
            return $result;
        }
    }

    private function _getToken()
    {
        $temp = $this->_call("https://api.weixin.qq.com/cgi-bin/token", array("grant_type" => "client_credential", "appid" => $this->appID, "secret" => $this->appsecret));
        $tempObj = json_decode($temp);
        $this->token = $tempObj->access_token;
    }


    /**
     * 基础支持 - 获取access_token接口 /token
     * @param string $grant_type 获取access_token填写client_credential
     * @param string $appid 填写appid
     * @param string $secret 填写appsecret
     * @return json
     **/
    public function token($appid, $secret, $grant_type = 'client_credential')
    {
        $fp = array('grant_type' => $grant_type, 'appid' => $appid, 'secret' => $secret);
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 基础支持 - 多媒体文件上传接口 /media/upload
     * @param selector $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param file $media form-data中媒体文件标识，有filename、filelength、content-type等信息
     * @return json
     **/
    public function media_upload($media, $type = 'image,voice,video,thumb')
    {
        $fp = array('type' => $type, 'media' => $media);
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 基础支持 - 下载多媒体文件接口 /media/get
     * @param string $media_id 媒体文件id
     * @return json
     **/
    public function media_get($media_id)
    {
        $fp = array('media_id' => $media_id);
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 向用户发送消息 - 发送客服消息接口 /message/custom/send
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function message_custom_send($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 用户管理 - 获取关注者列表接口 /user/get
     * @param string $next_openid 获取关注用户列表偏移量，不填默认从头开始拉取
     * @return json
     **/
    public function user_get($next_openid)
    {
        $fp = array('next_openid' => $next_openid);
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 用户管理 - 获取用户基本信息接口 /user/info
     * @param string $openid 目标用户的OPNEID
     * @return json
     **/
    public function user_info($openid)
    {
        $fp = array('openid' => $openid);
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 用户管理 - 查询分组接口 /groups/get
     * @return json
     **/
    public function groups_get()
    {
        $fp = array('access_token' => $this->token);
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/get';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 用户管理 - 创建分组接口 /groups/create
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function groups_create($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/create';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 用户管理 - 修改分组名接口 /groups/update
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function groups_update($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/update';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 用户管理 - 移动用户分组接口 /groups/members/update
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function groups_members_update($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/members/update';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 自定义菜单 - 自定义菜单创建接口 /menu/create
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function menu_create($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 自定义菜单 - 自定义菜单查询接口 /menu/get
     * @return json
     **/
    public function menu_get()
    {
        $fp = array('access_token' => $this->token);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 自定义菜单 - 自定义菜单删除接口 /menu/delete
     * @return json
     **/
    public function menu_delete()
    {
        $fp = array('access_token' => $this->token);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete';
        return $this->_call($url, $fp, 'GET');
    }


    /**
     * 推广支持 - 创建二维码ticket接口 /qrcode/create
     * @param content $body 调用接口的数据json包
     * @return json
     **/
    public function qrcode_create($body)
    {
        $fp = array('body' => $body);
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
        return $this->_call($url, $fp, 'POST');
    }


    /**
     * 推广支持 - 换取二维码 /showqrcode
     * @param string $ticket 获取的二维码ticket
     * @return json
     **/
    public function showqrcode($ticket)
    {
        $fp = array('ticket' => $ticket);
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
        return $this->_call($url, $fp, 'GET');
    }


}

;
/*
$w=new Weixin();
echo $w->user_info("o3lP-tgSkgMMFluWtL59h_7TXX8M");
?>