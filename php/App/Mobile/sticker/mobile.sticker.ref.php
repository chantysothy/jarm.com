<?php
define('HIDE_REQUEST',1);

$pp=50;
extract(Load::Split()->get('/sticker/ref/'.Load::$path[1],1,['page']));

$db=Load::DB();
if($count=$db->count('sticker',['pl'=>1,'ref'=>Load::$path[1],'dd'=>['$exists'=>false]]))
{
	list($pg,$skip)=Load::Pager()->navigation($pp,$count,[$url,'page-'],$page);
	$app=$db->find('sticker',['pl'=>1,'ref'=>Load::$path[1],'dd'=>['$exists'=>false]],['t'=>1,'fd'=>1,'do'=>1,'f'=>1],['sort'=>['_id'=>-1],'skip'=>$skip,'limit'=>$pp]);
}

if(!$page || $page<1)$page=1;


Load::$core->assign('c',Load::$path[1]);
Load::$core->assign('parent','/sticker');
Load::$core->assign('page',$page);
Load::$core->assign('maxpage',ceil($count/$pp));
Load::$core->assign('app',$app);
Load::$core->assign('cur','?parent='.urlencode(URL));
Load::$core->data['content']=Load::$core->fetch('sticker.ref');

?>