<?php

require_once 'comm.php';
/*
$cmd = new Cmd(0,'o3lP-tgSkgMMFluWtL59h_7TXX8M');
$str = $cmd->Run();
echo $str;
*/
$cb = new Cb();

w($cb);

$str = 'nothing?';


if ($cb->MsgType == 'event') {
    if ($cb->Event == 'subscribe') {
        //关注事件
        if ($cb->isNewUser == 2) {
            $str = '欢迎回来!';
        } elseif ($cb->isNewUser == 1) {
            $str = '您终于来了!';
        } else {

        }
    } elseif($cb->Event=='lookup') {
        $str='lookup';
    } elseif($cb->Event=='newtopic') {
        //自定义事件
        $str='new topic ';
    }
} else {
    // 回复信息上来
    if ($cb->isOrder) {
        //发送命令上来
        #w('1');
        #w($cb->Content.$cb->openid);
        $cmd = new Cmd("$cb->Content", $cb->openid);
        #w(cmd);
        $str = $cmd->Run();
        # w($str);
    } else {
        if ($cb->lastTopicId == 0) {
            //如果活动过期
            #w('2');
            if ($cb->isMsgDie) {
                $str = "活动【{$cb->topic['tags']}】过期了，回复0获取操作说明。";
            } else {
                //如果有活动
                $str = "还没有活动，回复0获取操作说明。";
            }
        } else {
            //OK 活动
            $st = array(
                '用户' => array($cb->topic['users'], '位'),
                '图片' => array($cb->topic['images'], '张'),
                '文字' => array($cb->topic['texts'], '条'),
                '语音' => array($cb->topic['voices'], '条'),
                '位置' => array($cb->topic['locations'], '个')
            );
            $str = "【{$cb->topic['tags']}】目前有:" . PHP_EOL;
            foreach ($st as $k => $v) {
                if ($v[0] != 0) {
                    $str .= "$k:{$v[0]}{$v[1]}" . PHP_EOL;
                }
            }
            $str .= "该活动于" . date('m月d日H点i分', $cb->topic['die']) . "过期";

        }
    }
}
echo $cb->get_text($str);