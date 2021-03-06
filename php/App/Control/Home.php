<?php
namespace Jarm\App\Control;
use Jarm\Core\Load;

class Home extends Service
{
  public function _home()
  {
    Load::Session()->logged();
    if(!Load::$my['am'])
    {
      Load::$core->data['content']=Load::$core->fetch('control/permission');
      return;
    }
    Load::Ajax()->register(['clearcache','delfriend','delfriends']);
    $db=Load::DB();
    $days=['อา','จ','อ','พ','พฤ','ศ','ส'];
    $view=$db->find('logs',['ty'=>'news'],[],['sort'=>['_id'=>-1],'limit'=>14]);
    $pageview=['x'=>[],'do'=>[],'is'=>[],'tb'=>[],'mb'=>[],'dt'=>[],'all'=>[]];
    $j=0;
    for($i=count($view)-1;$i>=0;$i--)
    {
      $p=$view[$i];
      $d=''.$p['date'];
      $d2 = date('w',strtotime(substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2).' 00:00:00'));
      $pageview['x'][]=[$j, $days[$d2].'.'];//substr($d,6,2).'/'.substr($d,4,2));
      $pageview['do'][]=[$j,$p['do']];
      $pageview['is'][]=[$j,$p['is']];
      $pageview['tb'][]=[$j,$p['tb']];
      $pageview['mb'][]=[$j,$p['mb']];
      $pageview['dt'][]=[$j,$p['dt']];
      $pageview['all'][]=[$j,intval($p['is'])+intval($p['do'])];
      $j++;
    }

    $diff=['today'=>[],'yesterday'=>[],'yesterday2'=>[]];
    if($view[0])
    {
      for($i=0;$i<24;$i++)
      {
        $diff['today'][]=[$i,$view[0]['hour'][$i]??null];
      }
    }
    if($view[1])
    {
      for($i=0;$i<24;$i++)
      {
        $diff['yesterday'][]=[$i,$view[1]['hour'][$i]];
      }
    }
    if($view[2])
    {
      for($i=0;$i<24;$i++)
      {
        $diff['yesterday2'][]=[$i,$view[2]['hour'][$i]];
      }
    }
    $now=Load::Time()->now();
    $ads=['banner'=>0,'advertorial'=>0];
    $ads['banner']=$db->count('ads',['dd'=>['$exists'=>false],'pl'=>1,'ty'=>'ads','boxza'=>['$exists'=>true],'dt1'=>['$lte'=>$now],'dt2'=>['$gte'=>$now]]);
    $ads['advertorial']=$db->count('ads',['dd'=>['$exists'=>false],'pl'=>1,'ty'=>'advertorial','boxza'=>['$exists'=>true],'dt1'=>['$lte'=>$now],'dt2'=>['$gte'=>$now]]);

    $member=[];
    $member['active']=$db->count('user',['st'=>1]);
    $member['wait']=$db->count('user',['st'=>0]);
    $member['ban']=$db->count('user',['st'=>['$lt'=>0]]);
    $member['hold']=$db->count('user',['st'=>['$gt'=>1]]);

    Load::$core->data['content']=Load::$core
      ->assign('user',Load::User())
      ->assign('ads',$ads)
      ->assign('diff',$diff)
      ->assign('member',$member)
      ->assign('pageview',$pageview)
      ->assign('admin',$db->find('user',['am'=>['$gte'=>1]],['_id'=>1,'if.am'=>1,'am'=>1,'du'=>1,'em'=>1],['sort'=>['du'=>-1]]))
      ->assign('friend',$db->find('msn',['dd'=>['$exists'=>false],'da'=>['$gte'=>Load::Time()->now(-3600*24*30)],'sd'=>['$exists'=>true]],[],['sort'=>['sd'=>-1]]))
      ->assign('logs',$db->find('logs',['ty'=>'cache'],[],['sort'=>['_id'=>-1],'limit'=>100]))
      ->fetch('control/home');
  }

  public function delfriend($id)
  {
    $ajax=Load::Ajax();
    Load::DB()->update('msn',['_id'=>intval($id)],['$set'=>['dd'=>Load::Time()->now()]]);
    //$ajax->alert('ลบเรียบร้อยแล้ว');
    $ajax->script('$("#friend'.$id.'").remove();');
    $ajax->script('$("#friendcount").html($(".table-friend tr").length)');
  }

  public function delfriends()
  {
    $ajax=Load::Ajax();
    Load::DB()->update('msn',['dd'=>['$exists'=>false],'da'=>['$gte'=>Load::Time()->now(-3600*24*30)],'sd'=>['$exists'=>true]],['$set'=>['dd'=>Load::Time()->now()]],['multiple'=>true]);
    $ajax->alert('ลบเรียบร้อยแล้ว');
    $ajax->script('$(".table-friend").remove();');
    $ajax->script('$("#friendcount").html(0)');
  }
}
?>
