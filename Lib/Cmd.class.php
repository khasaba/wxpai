<?php
class Cmd
{
    public $name;
    public $value;
    public $Ord;
    public $openid;
    public $note = '回复0获取操作说明。';

    public $cmdConfig = array(
        '0' => array('help', '获取帮助信息(直接发送0即可)'),
        '9' => array('createTopic', '创建新的活动(格式:9活动名,如:9公司年会)'),
        '1' => array('joinTopic', '参与一个活动(格式:1活动代码,如:1666)'),
#        '2' => array('queryTopic', '通过关键字查询活动代码(格式:2关键字,如:2年会)'),
#        '3' => array('queryCurrentStatus', '查询自己现在所在的活动里的情况(格式:3活动代码,如:3666)'),

    );

    public function __construct($cmd='', $openid='')
    {
        $this->name = trim(substr($cmd, 0, 1));
        $this->value = trim(substr($cmd, 1));
        $this->openid = $openid;
        $this->Ord = new Ord();
    }

    public function Run()
    {
        foreach ($this->cmdConfig as $k => $v) {
            if ($this->name == $k) {
               # return $this->help();
                w($v[0]);
                return $this->{$v[0]}();
                break;
            }
        }
        return '你说啥了？' . $this->note;
    }

    public function help()
    {
        w('helo');
        $str = "";
        foreach ($this->cmdConfig as $k => $v) {
            $str .= "回复:$k ".PHP_EOL."功能:{$v[1]}" .PHP_EOL . PHP_EOL;
        }
        return $str;
    }

    public function createTopic()
    {
	if(strlen($this->value)<2){return "活动标题太短了。。。比如你可以回复:".PHP_EOL."9年度腐败之Last波".PHP_EOL."就可以【创建年度腐败Last波】的活动了。";}
        $id = $this->Ord->createTopic($this->openid, $this->value);
        $topic = db::singleton()->get_one("select * from topic where topid={$id}");
        $this->joinTopic();
        return "【{$this->value}】创建成功了，活动ID是【{$id}】,快告诉你的小伙伴们关注我，回复【1{$id}】一起来留下些什么吧。该活动会在【".date('Y-m-d H:i:s',$topic['die'])."】过期哦：）";
    }

    public function joinTopic()
    {
        $id = $this->Ord->joinTopic($this->openid, $this->value);
        if ($id == 0) {
            return $this->Ord->error . ', ' . $this->note;
        }
        $topic = db::singleton()->get_one("select * from topic where topid={$this->value}");
        db::singleton()->query("update users set lasttopic='{$this->value}' where openid='{$this->openid}'");
        return "成功加入了【{$topic['tags']}】哦，现在你发送的任何东东都会属于该活动所有哦。该活动会在【".date('Y-m-d H:i:s',$topic['die'])."】过期哦。：）";

    }
}
