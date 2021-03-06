<?php
/*
+ ----------------------------------------------------------------------------+
|     Jarm - for PHP 7.1
|
|     https://jarm.com
|     positron@jarm.comm
|
|     $Revision: 3.0.0 $
|     $Date: 2017/02/22 20:58:00 $
|     $Author: Positron $
+-----------------------------------------------------------------------------+
*/
namespace Jarm\Core;

class Load
{
  /**
  * @var array $h
  * object
  */
  private static $h=[];

  /**
  * @var object $core = $this
  */
  public static $core;

  /**
  * @var array $path url split by "/"
  */
  public static $path;

  /**
  * @var array|null $my user data (if logged in)
  */
  public static $my;

  /**
  * @var array $conf // config from file
  */
  public static $conf=[];

  /**
  * @var object $app current app  (Jarm\App\...)
  */
  public static $app;

  /**
  * @var string $sub urrent sub-domain
  */
  public static $sub='';

  /**
  * @var array $map current config for sub-domain
  */
  public static $map=[];

  /**
  * @var array $data assign to template
  */
  public $data=[];

  /**
  * @var int $time current time for cache file
  */
  public static $time;

  /**
  * @var int $expire expire time for cache file
  */
  public static $cache;

  /**
  * @var string $request is strtolower($_SERVER['REQUEST_METHOD'])
  */
  public static $request;

  /**
  * ใช้แทน __construct เพื่อเก็บค่า Load::$core
  * @param array $data ค่าเริ่มต้นของแอพ
  * @return Object $this
  */
  public static function Init(array $data=[])
  {
    self::$time=time();
    self::$conf=require(__CONF.'global.php');
    define('HOST',strtolower($_SERVER['HTTP_HOST']));
    $url=urldecode(parse_url(strtolower($_SERVER['REQUEST_URI']),PHP_URL_PATH));
    if(substr($url,0,3)=='/_/')
    {
      define('HASH',1);
      $url=substr($url,2);
    }
    define('URL',$url);
    define('URH',self::$conf['scheme'].'://'.HOST);
    define('URI',URH.URL);
    self::$path=array_values(array_filter(explode('/',trim(URL,'/'))));
    return self::$core = new self($data);
  }

  /**
  * Magic
  * @param array $data is variable for data
  * @return Object
  */
  public function __construct(array $data=[])
  {
    $this->data=$data;
  }

  /**
  * Magic
  * call class in Jarm\Core namespace
  * example: Load::DB() - new Jarm\Core\DB()
  * @param string $c is class name
  * @param string|array $n is variable for assign to __construct
  * @return Object
  */
  public static function __callStatic($c,$n)
  {
    $_=!empty($n)?md5(serialize($n)):'default';
    if(empty(self::$h[$c.'.'.$_]))
    {
      try
      {
        self::$h[$c.'.'.$_]=(new \ReflectionClass('Jarm\\Core\\'.$c))->newInstanceArgs($n);
      }
      catch(Exception $e)
      {
        var_dump($e->getMessage());
        exit;
      }
    }
    return self::$h[$c.'.'.$_];
  }

  /**
  * set config for all sub-domains
  * @param array $map sub-domain config
  * @return Load $this object, or redirect to main page if this sub-domain set "enable = false";
  */
  public function route(array $map): Load
  {
    $subc=strlen(self::$conf['domain'])*-1;
    if(substr(HOST,$subc)==self::$conf['domain'])
    {
      if(empty(self::$sub=trim(substr(HOST,0,$subc),' .'))) // echo "www."
      {
        self::$sub='www';
      }
      if(isset($map[self::$sub]))
      {
        self::$map=$map[self::$sub];
        if(self::$map['enable']??true)
        {
          return $this;
        }
        self::$sub='';
      }
      else
      {
        self::$sub=(array_slice(explode('.',self::$sub),-1)[0]);
        return $this;
      }
    }
    // domain name does not match
    // redirect to main page
    self::move(self::$conf['scheme'].'://'.self::$sub.self::$conf['domain'],true);
  }

  /**
  * execute application
  * @param array $app [directory or file] to execute (if set)
  * @return Load $this object
  */
  public function run(array $app=[]): Load
  {
    if(count($app)>0)
    {
      self::$sub=array_keys($app)[0];
      self::$map=array_values($app)[0];
    }
    $app=ucwords(self::$map['app']??self::$sub,'_');
    $path=ucwords(str_replace(['-','.'],['_','_dot_'],trim(self::$path[0]??'home','_')),'_');
    self::$request=strtolower($_SERVER['REQUEST_METHOD']?:'get');
    if(!empty($_COOKIE[self::$conf['session']['name']]))
    {
      self::Session();
    }
    $func=function()use($app,$path)
    {
      /**
      * check app exists by file name
      * - App/[app-name]/[first-path].php
      * - App/[app-name]/Service.php
      * - App/[app-name].php
      */
      if(is_null($serv=file_exists(__APP.$app.'/'.$path.'.php')?'\\'.$path:
                        (file_exists(__APP.$app.'/Service.php')?'\\Service':
                          (file_exists(__APP.$app.'.php')?'':null))))
      {
        // if don't have app [directory or file]
        // redirect to main page
        return ['move'=>self::$conf['scheme'].'://'.self::$conf['domain']];
      }
      $arg=(self::$map['arg']??[]);
      self::$app=(new \ReflectionClass('\\Jarm\\App\\'.$app.$serv))->newInstanceArgs([$arg]);
      $prefix=self::$request.'_';
      if(method_exists(self::$app,$p=$prefix.$path)||
          method_exists(self::$app,$p='_'.$path)||
            (isset(self::$map['func'])&&
              (method_exists(self::$app,$p=$prefix.self::$map['func'])||
              method_exists(self::$app,$p='_'.self::$map['func']))))
      {
        // load banner
        $this->data['banner']=self::banner(self::$sub);
        // call method by link name
        $data=(array)(self::$app->{$p}($arg));
        // merge data and return
        return array_merge($this->data,$data);
      }
      else
      {
        //redirect to main path if don't have method
        return ($path!='home')?['move'=>'/']:[];
      }
    };

    // key for cache file
    // auto-gen by url
    $data=(self::$request=='get'?$this->get(strtolower(HOST.'/'.$path.'/'.implode('/',array_slice(self::$path,1))),$func):$func());
    if($data['stats'])
    {
      $this->stats($data['stats']);
    }
    if(isset($data['move']))
    {
      self::move($data['move']);
    }
    if(self::$request=='get' || !empty($data['content']))
    {
      if(!empty($data['content']))
      {
        echo $this->assign('data',$data)->fetch('global',true);
      }
      if(!empty($data['echo']))
      {
        echo $data['echo'];
        exit;
      }
      return $this;
    }
    exit;
  }

  /**
  * display included files and memory usage, and exit
  */
  public function exit(): void
  {
    echo "<!--\ninclude/require: ".count($f=get_included_files())." files\n";
    for($i=0;$i<count($f);$i++)
    {
      echo ($i+1).' - '.str_replace(ROOT,'/',$f[$i])."\n";
    }
    echo 'memory (real): '.memory_get_usage(true).' bytes'."\n".
    'memory (emalloc): '.memory_get_usage(false).' bytes'."\n".
    'memory peak (real): '.memory_get_peak_usage(true).' bytes'."\n".
    'memory peak (emalloc): '.memory_get_peak_usage(false).' bytes'."\n".
    'time: '.(microtime(true)-START).' sec.'."\n".'-->';
    exit;
  }

  /**
  * get new sub-domain name for upload server
  * @param string $sv is old name
  * @return string
  */
  public static function getServ(string $sv): string
  {
    switch ($sv)
    {
      case 's1':
        return 'f1';
      case 's2':
        return 'f2';
      case 's3':
        return 'f1';
      case 's4':
        return 'f2';
      case 'f3':
        return 'f1';
      case 'f4':
        return 'f2';
      default:
        return $sv?:'f1';
    }
  }

  /**
  * get banner for current sub-domain
  * @param string $type sub-domain name
  * @return array data of banner
  */
  public static function banner(string $type): array
  {
    $domain='boxza';
    $file='bin/cache/banner/'.$domain.'.'.$type.'.txt';
    if(file_exists(_FILES.$file))
    {
      return (array)unserialize(file_get_contents(_FILES.$file));
    }
    $_b=[];
    if($banner=self::DB()->find('ads',['dd'=>['$exists'=>false],'pl'=>1,'ty'=>'ads',$domain.'.'.$type=>['$exists'=>true],'dt1'=>['$lte'=>self::Time()->now()],'dt2'=>['$gte'=>self::Time()->now()]],[],['sort'=>['so'=>1,'_id'=>1]]))
    {
      foreach($banner as $v)
      {
        if(is_array($v[$domain][$type]))
        {
          $j=$v[$domain][$type];
          foreach($j as $k)
          {
            if(empty($_b[$k]))
            {
              if($v['tyc']=='1')
              {
                $_b[$k]='<script type="text/javascript">document.write(\'\x3Cscript type="text/javascript" src="https://code.jarm.com/impression/?key='.substr(md5(self::$conf['ads']['key'].'-'.md5(HOST)),10,16).'&slot='.$domain.'-'.$type.'-'.$k.'&width=\'+window.screen.width+\'&height=\'+window.screen.height+\'">\x3C/script>\');</script>';//'<div class="jarm-ads" data-slot="racing-'.$type.'-'.$k.'"></div>';
              }
              else
              {
                $_b[$k]='<span id="jarm_b_'.$k.'"></span><script type="text/javascript">document.write(\'\x3Cscript type="text/javascript" src="https://code.jarm.com/impression/?key='.substr(md5(self::$conf['ads']['key'].'-'.md5(HOST)),10,16).'&slot='.$domain.'-'.$type.'-'.$k.'&width=\'+window.screen.width+\'&height=\'+window.screen.height+\'&outer=jarm_b_'.$k.'" async>\x3C/script>\');</script>';//'<div class="jarm-ads" data-slot="racing-'.$type.'-'.$k.'"></div>';
              }
            }
          }
        }
      }
    }
    self::Folder()->save($file,serialize($_b));
    return $_b;
  }

  /**
  * redirect to new page
  * @param string|array $u url of new page
  * @param bool $m use 301 header
  */
  public static function move($u=['/'],bool $m=false): void
  {
    while(@ob_end_clean());
    $u=self::uri($u);
    if(isset($_POST['ajax']))
    {
      header('Content-type: application/json');
      echo json_encode(['f'=>[["a"=>"js",'v'=>'window.location.href="'.$u.'";']]]);
    }
    elseif(defined('IS_AJAX'))
    {
      echo '<html><body><script type="text/javascript">parent.location.href=\''.$u.'\';</script></body></html>';
    }
    else
    {
      if($m)header('HTTP/1.1 301 Moved Permanently');
      header('Location: '.$u);
    }
    exit;
  }

  /**
  * convert array to string (url)
  * @param array $arg url
  * @param string $path if !is_array($arg)
  * @return string new url
  */
  public static function uri($arg=['/']): string
  {
    if(is_array($arg))
    {
      if(count($arg)==2)
      {
        return self::$conf['scheme'].'://'.($arg[0]?$arg[0].'.':'').self::$conf['domain'].$arg[1];
      }
      $arg=$arg[0];
    }
    if(strpos($arg,'://')===false && !in_array(substr($arg,0,1),['?','/']))
    {
      return self::$conf['scheme'].'://'.($arg?$arg.'.':'').self::$conf['domain'].'/';
    }
    return $arg;
  }

  /**
  * Template
  * assign variable to template
  * @param string|array $s is variable in template
  * @return Load $this object
  */
  public function assign($s): Load
  {
    if(is_string($s))
    {
      $this->$s=@func_get_arg(1);
    }
    elseif(is_array($s))
    {
      foreach($s as $k=>$v) $this->$k=$v;
    }
    return $this;
  }

  /**
  * Template
  * get html from template
  * @param string $f is path for get template file
  * @return string html
  */
  public function fetch(string $f)
  {
    ob_start();
    /*
    if($_GET['theme'] && file_exists($fl=__TPL.$_GET['theme'].'/'.$f.'.tpl'))
    {
      include($fl);
    }
    else
    {*/
      include(__TPL.self::$conf['theme'].'/'.$f.'.tpl');
    //}
    return ob_get_clean();
  }

  /**
  * Cache
  * generate cache file. key by url
  * @param string $key key name
  * @param function $function to generate new data and save to cache file (if set)
  * @return array|string|null of data
  */
  public static function cache(int $expire=3600,int $maxlv=2,bool $redirect=true)
  {
    self::$cache['expire']=$expire;
    if($redirect&&count(self::$path)>$maxlv)
    {
      self::move('/'.implode('/',array_slice(self::$path,0,$maxlv)));
    }
  }

  /**
  * Cache
  * get data from cache file
  * @param string $key key name
  * @param function $function to generate new data and save to cache file (if set)
  * @return array|string|null of data
  */
  public function get(string $key,$func=null)
  {
    $file=_FILES.'bin/cache/'.trim($key,'/').'.php';
    if(file_exists($file))
    {
      $_=include($file);
      if(!empty($_['expire']) && $_['expire']>self::$time)
      {
        return $_['data'];
      }
    }
    if(!is_null($func))
    {
      self::$cache=['key'=>$key,'expire'=>-1];
      if($data=$func())
      {
        if(self::$cache['expire']>0)
        {
          $this->set(self::$cache['key'],$data,self::$cache['expire']);
        }
        return $data;
      }
    }
    return null;
  }

  /**
  * Cache
  * set data to cache file
  * @param string $key key name
  * @param array|string $data data
  * @param int $expire second for expire time
  */
  public function set(string $key,$data,int $expire=3600): void
  {
    self::Folder()->save('bin/cache/'.trim($key,'/').'.php',"<?php\nreturn ".var_export(['create'=>self::$time,'expire'=>self::$time+$expire,'data'=>$data],true)."\n?>");
  }

  /**
  * Cache
  * delete cache file
  * @param string $key key name
  */
  public function delete(string $key)
  {
    return $this->clean($key,false);
  }

  /**
  * Cache
  * delete cache folder
  * @param string $key folder name
  */
  public function clear(string $folder='')
  {
    self::Folder()->clean('bin/cache/'.$folder);
    return $this;
  }

  /**
  * Cache
  * api for send command to all servers
  * @param string $key folder name
  */
  public function clean(string $key='',bool $folder=true,string $domain='jarm.com')
  {
    foreach(self::$conf['server']['php'] as $k=>$v)
    {
      self::Http()->get('http://'.$v.':82/clear-cache.php?key='.urlencode($key).'&domain='.urlencode($domain).($folder?'&folder=1':''));
    }
    return $this;
  }

  /**
  * Stats
  * save browser info to file
  * @param string $key name
  */
  public function stats(string $key='')
  {
    list($type,$id,$view)=explode(':',$key);
    $cur=0;
    if(stripos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit') !== false)
    {
      if(Load::$core->data['image_cache'])
       {
        Load::$core->data['image']=Load::$core->data['image_cache'];
       }
     }
    if(!Load::$my || !Load::$my['am'])
    {
       if(stripos($_SERVER['HTTP_USER_AGENT'], 'bot') === false )
       {
        $file='bin/'.$type.'-view/'.date('Y-m-d').'/'.date('H').'/'.$id.'/'.substr('000'.rand(1,200),-3).'.txt';
        if(file_exists(_FILES.$file))
        {
          $log=unserialize(file_get_contents(_FILES.$file));
        }
        else
        {
          $log=['do'=>0,'is'=>0,'mb'=>0,'tb'=>0,'dt'=>0];
        }
        if($view=='do')
        {
          $browser = new \Browser();
          if($browser->isTablet())
          {
            $log['tb']=($log['tb']??0)+1;
          }
          elseif($browser->isMobile())
          {
            $log['mb']=($log['mb']??0)+1;
          }
          else
          {
            $log['dt']=($log['dt']??0)+1;
          }
        }
        if($log[$view])
        {
          $log[$view]++;
        }
        else
        {
          $log[$view]=1;
        }
        $cur = $log[$view];
        Load::Folder()->save($file,serialize($log));
      }
    }
  }
}

?>
