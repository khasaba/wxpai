<?php

class Ord
{
    var $db;

    var $error;

    public function __construct()
    {
        $this->db = Db::singleton();

    }



    public function createTopic($openid, $name, $pwd = '', $die_day = '')
    {
        if ($die_day == '') {
            $die_day = 1;
        }
        $default_die = time() + $die_day * 24 * 60 * 60; // 1day
        $id= $this->db->insert('topic', array(
            'owner' => $openid,
            'tags' => $name,
            'pwd' => $pwd,
            'die' => $default_die,
            'CreateTime' => time()
        ));
        return $id;
    }

    public function joinTopic($openid, $topicid)
    {
        $id = 0;
        $exist = $this->db->get_one("select * from topic where topid='{$topicid}' limit 1");
        if (!$exist) {
            $this->error = '这个活动不存在';
            return false;
        }
        if($exist['die']<time()){
            $this->error = '这个活动已于'.date('Y-m-d H:i:s',$exist['die'])."过期了。";
            return false;
        }
        $id = $this->db->insert('usertopic', array(
            'openid' => $openid,
            'topicid' => $topicid,
            'jointime' => time()
        ));
        if ($id == 0) {
            $this->error = '插不进去';
        };
        return $id;
    }

    public function queryTopic($key, $topn = 10)
    {
        #return $this->db->get_all("select * from topic t join users u on t.owner=u.openid where t.die>" . time() . " t.tag like '%${key}%' order by topic.CreateTime desc limit 10; ");
        return $this->db->get_all("select tags,CreateTime,die,nickname,headimgurl from topic t join users u on t.owner=u.openid where t.die>" . time() . " and t.tags like '%{$key}%' order by t.CreateTime desc limit $topn;");
    }

    public function queryTopicStatus($topicid)
    {
        $sql = "select FromUserName,MsgType,count(1) n from msg where topic='{$topicid}' group by MsgType;";
        $re = $this->db->get_all($sql);
        $status = array();
        foreach ($re as $v) {
            if ($status[$v['FromUserName']]) $status[$v['FromUserName']]++; else $status[$v['FromUserName']] = 1;
            if ($status[$v['MsgType']]) $status[$v['MsgType']]++; else $status[$v['MsgType']] = 1;
            if ($status['total']) $status['total'] += $v['n']; else $status['total'] = $v['n'];
        }
        return $status;
    }
}