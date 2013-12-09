<?php
require_once 'comm.php';

$wx=new Weixin();


;
$ord=new Ord('');
w($wx,'');

$topicid=$ord->createTopic('o3lP-tgSkgMMFluWtL59h_7TXX8M','The Fire Fire');

echo "CreateTopic :".$topicid ."<br />";

$joinID=$ord->joinTopic('o3lP-tgSkgMMFluWtL59h_7TXX8M',$topicid);

echo "join Topic ".$joinID ."<br />";


$topicList=$ord->queryTopic('The',2);

w($topicList,'');

$status=$ord->queryTopicStatus(1);

w($status,'');



