<?php
class Cb
{
    public $wxid = WXID;
    public $db;
    public $msg;

    public $isOrder = false;
    public $isNewUser = 0; //0 no 1yes 2back
    public $isMsgDie = false;

    public $error;
    public $Event;
    public $MsgId;
    public $openid;
    public $MsgType;
    public $Content;
    public $MediaId;

    public $lastTopicId;

    public $topic;
    public $user;

    public function __construct()
    {
        $this->db = Db::singleton();
        if (!$this->getMsg()) {
            exit();
        };
        w($this->msg);
        $this->testIsOrder();
        $this->initDb();
    }

    private function msg_replace($text, $varArray)
    {
        if (!is_array($varArray)) return $text;
        return str_replace(array_keys($varArray), array_values($varArray), $text);
    }


    public function getMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            $this->msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->MsgType = strtolower($this->msg['MsgType']); //获取用户信息的类型
            $this->openid = $this->msg['FromUserName'];
            $this->MsgId = $this->msg['MsgId'];
            if ($this->MsgType == 'location') {
                $this->Content = $this->msg['Label'];
                $this->MediaId = $this->msg['Location_X'] . ',' . $this->msg['Location_Y'];
            } elseif ($this->MsgType == 'voice') {
                $this->Content = $this->msg['Recognition'];
                $this->MediaId = $this->msg['MediaId'];
            } elseif ($this->MsgType == 'image') {
                $this->Content = $this->msg['PicUrl'];
                $this->MediaId = $this->msg['MediaId'];
            } elseif ($this->MsgType == 'text') {
                $this->Content = $this->msg['Content'];
            }
            return true;
        } else {
            $this->error = '没有Post信息';
            return false;
        }
    }

    public function testIsOrder()
    {
        if ($this->MsgType == 'text') {
            $t = $this->Content;
            $o = substr($t, 0, 1);
            if (is_numeric($o)) {
                $this->isOrder = true;
            }
        }
    }


    public function get_text($text, $varArray = array())
    {
        $t = $this->msg_replace($text, $varArray);
        return "<xml>
<ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
<FromUserName><![CDATA[{$this->wxid}]]></FromUserName>
<CreateTime>" . time() . "</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$t}]]></Content>
</xml>";
    }

    public function get_list(array $list)
    {
        $str = "<xml>
<ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
<FromUserName><![CDATA[{$this->wxid}]]></FromUserName>
<CreateTime>" . time() . "</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>2</ArticleCount>
<Articles>";
        foreach ($list as $v) {
            $str .= "<item>
<Title><![CDATA[{$v['title']}]]></Title>
<Description><![CDATA[{$v['description']}]]></Description>
<PicUrl><![CDATA[{$v['picurl']}]]></PicUrl>
<Url><![CDATA[{$v['url']}]]></Url></item>" . "";
        }
        $str .= "" . "</Articles></xml>";
        return $str;
    }

    private function get_image()
    {
        return false;
        /*
        //doing...
        return "<xml>
<ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
<FromUserName><![CDATA[{$this->wxid}]]></FromUserName>
<CreateTime>" . time() . "</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[lEeeUp-Rf_qjSTP_CXUscHMYn7snvpF6S53m0QzosMwj0zZbjLGY9qfjAot1aN]]></MediaId>
</Image>
</xml>";
        */
    }

    public function initDb()
    {
        $data = array();
        if (!$this->isOrder) {
            if ($this->MsgType == 'event') {
                $data['subscribe'] = 1;
                $data['subscribe_time'] = time();
                $data['openid'] = $this->openid;
                $wx = new Weixin();
                if($js=json_decode($wx->user_info($this->openid))){
                       if($js->errcode>0){
                           die('获取用户错误！');
                       }else{
                           $data=(array)$js;
                       }
                };
                $this->Event = $this->msg['Event'];
                if ($this->msg['Event'] == 'subscribe') {
                    $exist = $this->db->get_one("select * from users where openid='{$this->openid}'");
                    if (!$exist['openid']) {
                        $this->db->insert('users', $data);
                        $this->isNewUser = 1;
                    } else {
                        $data['subscribe'] = 1;
                        $this->db->update('users', $data, "openid='{$this->openid}'");
                        $this->isNewUser = 2;
                    }
                } elseif ($this->msg['Event'] == 'unsubscribe') {
                    $data['subscribe'] = 0;
                    $this->db->update("users", $data, "openid='{$this->openid}'");
                } else {
                };
            } else {
                $this->user = $this->db->get_one("select * from users where openid='{$this->openid}'");
                $this->topic = $this->db->get_one("select * from topic where topid='{$this->user['lasttopic']}'");
                $topid = $this->topic['topid'];
                if ($this->topic['die'] < time()) {
                    $topid = 0;
                    $this->isMsgDie = true;
                }
                $f = array(
                    'MsgId' => $this->MsgId,
                    'FromUserName' => $this->openid,
                    'MsgType' => $this->MsgType,
                    'MediaId' => $this->MediaId,
                    'da' => $this->Content,
                    'CreateTime' => time(),
                    'topic' => $topid,
                );
                $this->lastTopicId = $topid;
                if ($topid > 0) {
                    $this->db->query("update topic set {$this->MsgType}s = {$this->MsgType}s + 1 where topid={$topid}");
                    $this->topic["{$this->MsgType}s"]++;
                }
                $this->db->insert('msg', $f);
            }
        }
    }
}
