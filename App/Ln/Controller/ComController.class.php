<?php
namespace Ln\Controller;
/**
 *
 * 版权所有：恰维网络<Ln.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2015-09-17
 * 版    本：1.0.0
 * 功能说明：后台公用控制器。
 *
 **/



use Common\Controller\BaseController;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Staff\Session;
use Think\Auth;

class ComController extends BaseController
{

    //构造方法
    static $qrcode_url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?";
    static $access_token = "HZE443k452F35rK25352ZK1BWEb55FEv";
    static $qrcode_get_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?";

    public function _initialize()
    {
        Session_start();
        C(setting());
		$flag = false;
        if($_SESSION['auth']){
            $auth=$_SESSION['auth'];
        }else{
            $auth = $_COOKIE["auth"];
        }


        if($_SESSION['user_id']){
            $user_id = $_SESSION["user_id"];
        }else {
            $user_id = $_COOKIE["user_id"];
        }
        if($user_id){
            $user = M('member')->field('uid,user,identifier,token,salt')->where(array('uid'=>$user_id))->find();
            $this->USER = $user;
            $flag = true;
        }

		/*list($identifier, $token) = explode(',', $auth);

		if (ctype_alnum($identifier) && ctype_alnum($token)) {
			$user = M('member')->field('uid,user,identifier,token,salt')->where(array('token'=>$token))->find();

			if($user) {
				if($token == $user['token'] && $user['identifier'] == password($user['uid'].md5($user['user'].$user['salt']))){
					$flag = true;
					$this->USER = $user;
				}
			}
		}*/



        $url = U("login/index");
        if (!$flag) {            $domain = ['www.longwincn.com','www.youlandt.com','www.fjtiemen.com'];			if(!in_array($_SERVER['HTTP_HOST'], $domain) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
                $pay_url = M('pay_wechat')->where('status=1 AND web_id=1')->getField('api_url');
                $url = 'http://' .$pay_url . '/PayApi/link';
                header("Location: {$url}");
                exit();
            }
            $this->error('请重新登录!',$url);
//            header("Location: {$url}");
//            exit(0);
        }
        $m = M();
        $prefix = C('DB_PREFIX');
        $UID = $this->USER['uid'];
        $userinfo = $m->query("SELECT * FROM {$prefix}auth_group g left join {$prefix}auth_group_access a on g.id=a.group_id where a.uid='".$UID."'");
        $Auth = new Auth();
        $allow_controller_name = array('Upload');//放行控制器名称
        $allow_action_name = array('generateQrCode','check_wechat','generateqrcode','generalize',"add_domain_log","test_domain");//放行函数名称

        if ($userinfo[0]['group_id'] != 1 && !$Auth->check(CONTROLLER_NAME . '/' . ACTION_NAME, $UID) && !in_array(CONTROLLER_NAME, $allow_controller_name) && !in_array(ACTION_NAME, $allow_action_name)) {
            $this->error('没有权限访问本页面!');
        }

        $user = member(intval($UID));
        $gid = $this->getuserid();
        $user['gid']=$gid['gid'];
        $this->assign('user', $user);


        $current_action_name = ACTION_NAME == 'edit' ? "index" : ACTION_NAME;
        $current = $m->query("SELECT s.id,s.title,s.name,s.tips,s.pid,p.pid as ppid,p.title as ptitle FROM {$prefix}auth_rule s left join {$prefix}auth_rule p on p.id=s.pid where s.name='" . CONTROLLER_NAME . '/' . $current_action_name . "'");
        $this->assign('current', $current[0]);


        $menu_access_id = $userinfo[0]['rules'];

        if ($userinfo[0]['group_id'] != 1) {

            $menu_where = "AND id in ($menu_access_id)";

        } else {

            $menu_where = '';
        }
        $menu = M('auth_rule')->field('id,title,pid,name,icon')->where("islink=1 $menu_where ")->order('o ASC')->select();
        $menu = $this->getMenu($menu);
		
		
		/**
		* 域名管理列表只保留   微信群域名  微信群域名分组 两项
		* @ zhanghr   2016.11.16 - 15.32
		* start
		**/
		foreach($menu as $key=>$value){
			if($value['id']==95){
				foreach($value['children'] as $key_link=>$value_link){
					if($value_link['id']!=97 && $value_link['id']!=99){
						unset($menu[$key]['children'][$key_link]);
					}
				}
				break;
			}
		}
		/**
		** 域名管理列表只保留   微信群域名  微信群域名分组 两项
		* @ zhanghr   2016.11.16 - 15.32
		* end
		**/
		
        $this->assign('menu', $menu);
		
		//获取公告
		$notice = M('article')->field('aid,content')->where('sid=15000')->order('aid desc')->limit(5)->select();
		$this->assign('notice_list_bool', count($notice)>0?true:false);
        $this->assign('notice_list', $notice);

    }


    protected function getMenu($items, $id = 'id', $pid = 'pid', $son = 'children')
    {
        if($_SESSION['user_id']){
            $user_id = $_SESSION["user_id"];
        }else {
            $user_id = $_COOKIE["user_id"];
        }
        $group = M('auth_group_access')->field('group_id')->where( array('uid'=>$user_id))->find();

        $tree = array();
        $tmpMap = array();

        foreach ($items as $index => $item) {
            $tmpMap[$item[$id]] = $item;
        }

        foreach ($items  as  $item) {
            $name_arr = explode('/',$item['name']);
            if($group['group_id'] !=1 && $name_arr[0] == 'Packet'){
                continue;
            }

            if (isset($tmpMap[$item[$pid]])) {
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }
	
	/*
	*@获取用户当前用户ID，权级ID
	*@return array;
	*/
	protected function getuserid(){
		
        if($_SESSION['user_id']){
            $user_id = $_SESSION["user_id"];
        }else {
            $user_id = $_COOKIE["user_id"];
        }
		$uid = $user_id;

		$usergroupaccess = M('auth_group_access')->field('uid,group_id')->where("uid=$uid")->find();
		return array('uid'=>$uid,'gid'=>$usergroupaccess['group_id']);
			
	}

    public function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    public function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
 
  //生成二维码
  public function getEwm($fqid, $wechat){
      $options = [
          'app_id' => $wechat['app_id'],
          'secret' => $wechat['secret'],
          'token' => $wechat['token'],
          'debug' => true,
          'log' => [
              'level' => 'debug',
              'file'  => './easywechat.log', // XXX: 绝对路径！！！！
          ]
      ];

      //获取微信二维码
      $app = new Application($options);
      $qrcode = $app->qrcode;
      $result = $qrcode->forever($fqid);// 或者 $qrcode->forever("foo");
      $ticket = $result->ticket; // 或者 $result['ticket']
//      $url = $result->url;
      $url = $qrcode->url($ticket);

      $file_name = $wechat['gid'] . '/' . $fqid . '_' . $wechat['app_id'] . '.jpg';
      $locahosturl = $this->DownLoadQr($url, $file_name);

      return $locahosturl;

  }
  //下载二维码到服务器
  protected function DownLoadQr($url,$file_name){
    if($url == ""){
      return false;
    }
    ob_start();
    readfile($url);
    $img=ob_get_contents();
    ob_end_clean();
    $size=strlen($img);
    $path = dirname(APP_PATH) . '/Public/Ln/images/qrcode/'.$file_name;

    if(!is_dir(dirname($path))){

        if(!@mkdir(iconv("UTF-8", "GBK", dirname($path)),0777,true)){
            $this->Error('dolwload image falied. Error Info: 无法创建目录');
            exit();
        };
    }

    $fp2=fopen('./Public/Ln/images/qrcode/'.$file_name,"a");
    if(fwrite($fp2,$img) === false){
      $this->Error('dolwload image falied. Error Info: 无法写入图片');
      exit();
    }
    fclose($fp2);
    return '/Public/Ln/images/qrcode/'.$file_name;
  }

    public function curl_get($url, $params = '')
    {
        if(is_array($params)) {
            $arr = array();
            foreach ($params as $k => $v) {
                $arr[] = $k . '=' . $v;
            }
            $params =  "&" . implode('&', $arr);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . $params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $ret = curl_exec($curl);

        curl_close($curl);

        return $ret;
    }
    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public function TestnewQrCode($id, $domain){

        vendor('phpqrcode.phpqrcode');

        $code=md5($id."+".$domain['host']."test");

        // 二维码数据
        // 生成的文件名
        $filename = './Public/Ln/images/paytestqrcode/'.$id.'/' . $id . '_' . $domain['host'] . '.png';
        $url = 'http://'. $domain['host'] . '/index.php?m=Ln&c=PayApi&a=link&test='.$id.'&tokencode='.$code;

        if(!is_dir(dirname($filename))){
            if(!@mkdir(iconv("UTF-8", "GBK", dirname($filename)),0777,true)){
                $this->Error('dolwload image falied. Error Info: 无法创建目录');
                exit();
            };
        }

        $errorCorrectionLevel =intval(3) ;//容错级别
        $matrixPointSize = intval(5);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        $object::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        return $filename;
    }


    /**
     * 生成二维码
     * @param $filename 二维码图片保存地址
     * @param $url 二维码Url地址
     * @return string
     */
    public function QrCode($filename, $url){
        vendor('phpqrcode.phpqrcode');
        if(empty($filename) || empty($url)){
            return false;
        }


        $path = './Public/Ln/images/';

        // 二维码数据
        // 生成的文件名
        $file_name = $path . $filename;

        if(!is_dir(dirname($file_name))){
            if(!@mkdir(iconv("UTF-8", "GBK", dirname($file_name)),0777,true)){
                return false;
            };
        }

        $errorCorrectionLevel =intval(3) ;//容错级别
        $matrixPointSize = intval(5);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        $object::png($url, $file_name, $errorCorrectionLevel, $matrixPointSize, 2);

        return '/Public/Ln/images/'.$filename;
    }
}