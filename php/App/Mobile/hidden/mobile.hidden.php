<?php

Load::$conf['social']['facebook']['appid']='350338375107984';

define('APP_VERSION','1.0');




$serv=array(
						''=>'home',
						'apps'=>'apps',
						'help'=>'help',
						'top'=>'top',
						'game'=>'game',
						'score'=>'score',
);

$cate=[];

Load::$core->assign('cate',$cate);
if(isset($serv[Load::$path[0]]))
{
	require_once(__DIR__.'/mobile.hidden.'.$serv[Load::$path[0]].'.php');
}
else
{
	require_once(__DIR__.'/mobile.hidden.home.php');
}


echo Load::$core->fetch('hidden');
exit;
?>