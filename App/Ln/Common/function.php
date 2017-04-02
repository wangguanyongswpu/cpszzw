<?php
/**
*
* ??????У???????<Ln.qiawei.com>
* ??    ???????<hanchuan@qiawei.com>
* ??    ???2015-09-17
* ??    ????1.0.0
* ?????????????????????
*
**/

/**
*
* ????????????
* @param  string $log   ????????
* @param  string $name ??????????????
*
**/
function addlog($log,$name=false,$log_type = 0){
	$Model = M('log');
    if($_SESSION['user_id']){
        $user_id = $_SESSION["user_id"];
    }else {
        $user_id = $_COOKIE["user_id"];
    }
    $data['uid'] = intval($user_id);
	if(!$name){
        $user = M('member')->field('user')->where(array('uid'=>$user_id))->find();
        $data['name'] = $user['user'];

		/*if($_SESSION['auth']){
			$auth=$_SESSION['auth'];
		}else{
			$auth = $_COOKIE["auth"];
		}

		list($identifier, $token) = explode(',', $auth);
		if (ctype_alnum($identifier) && ctype_alnum($token)) {
			$user = M('member')->field('user')->where(array('identifier'=>$identifier))->find();
			$data['name'] = $user['user'];
		}else{
			$data['name'] = '';
		}*/
	}else{
		$data['name'] = $name;
	}

	$data['t'] = time();
	$data['ip'] = $_SERVER["REMOTE_ADDR"];
	$data['log'] = $log;
	$data['log_type'] = $log_type;
	$Model->data($data)->add();
}


/**
*
* ???????????????
* @param  int $uid      ???ID??
* @param  string $name  ?????У??磺'uid'??'uid,user'??
*
**/
function member($uid,$field=false) {
	$model = M('Member');
	if($field){
		return $model ->field($field)-> where(array('uid'=>$uid)) -> find();
	}else{
		return $model -> where(array('uid'=>$uid)) -> find();
	}
}

/*
	*????????????ID?????ID
	*return array;
	*/
function getuserid(){
	//$auth = cookie('auth');
    if($_SESSION['user_id']){
        $user_id = $_SESSION["user_id"];
    }else {
        $user_id = $_COOKIE["user_id"];
    }
	//list($identifier, $token) = explode(',', $auth);
	//$member = M('member')->field('uid')->where(array('uid'=>$user_id))->find();
	$uid = $user_id;
	$usergroupaccess = M('auth_group_access')->field('uid,group_id')->where("uid=$uid")->find();
	
	return array('uid'=>$uid,'gid'=>$usergroupaccess['group_id']);
		
}

/*
* 同步数据量是否正常
 * $start  在多少分钟之前，一天以上则 时间戳查询时间，
 * $end	   在多少分钟之前，一天以上则 时间戳查询时间，
*/
function select_log($start=5,$end=2){

	$time=time();
	$starttime=($_REQUEST['starttime'])?$_REQUEST['starttime']:$time-10*60;
	$endtime=($_REQUEST['endtime'])?$_REQUEST['endtime']:$time-2*60;

	$log['starttime']=$starttime;
	$log['endtime']=$endtime;
	$log['register_log'] = M('register_log')->field('uid')->where("reg_time > $starttime ")->group("openid")->select();
	$log['payapi_log'] = M('payapi_log')->field('orderid')->where("time > $starttime  and status = 1")->group("orderid")->select();
	//echo M('payapi_log')->getLastSql();die;
	$log['pay_log'] = M('pay_log')->field('qw_pay_log.pay_serial')->join('qw_payapi_log on qw_pay_log.pay_serial = qw_payapi_log.orderid ','LEFT')->where("qw_payapi_log.time > $starttime")->group("qw_pay_log.pay_serial")
->select();

	Think\Log::record("find_log_count:".$starttime."->".$endtime."NUM:".json_encode($log),'DEBUG',true);

	return $log;
}

/**
 * 格式化响应信息
 * @param $param array 返回的信息数组
 * @return json
 */
function formatResponse($param, $type = 'json')
{
	switch($type){
		case 'json':
			$res = json_encode($param);
			break;
		default:
			$res = json_encode($param);
	}

	return $res;
}

/**
 * @param $url
 * @param string $params
 * @return mixed
 */
function curl_get($url, $params = '')
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

//生成sign
function get_cps_sign($_data)
{
	$cps_api = C('cps_api');
	$_data['app_key'] = $cps_api[$_data['app_id']]['app_key'];
	$sign_arr = [];
	ksort($_data);
	foreach($_data as $key=>$val){
		if($val != '' && $key != 'ext'){//空值字段不参与签名
			$sign_arr[] = $key . '=' . $val;
		}
	}

	$sign = md5(implode('&',$sign_arr));//签名

	return $sign;
}


/*
 * 根据weid获取EasyWechat实例
 *
 * @param int $weid 微信配置配置中的数组下标key
 *
 * return EasyWeChat\Foundation\Application or null
 */
function getEasyWechatHander($weid) {
    $weixinConfigs =  C("WEIXIN_USER_LIST");
    if(empty($weixinConfigs[$weid])){
        return null;
    }

    $weixin = $weixinConfigs[$weid];
    $options        = array(
        'app_id'         => $weixin['app_id'],
        'secret'         => $weixin['secret'],
        'debug'  => true,
        'token'   =>$weixin['token'],
        'aes_key' => empty($weixin['aes_key'])?'':$weixin['aes_key'],
        'log' => [
            'level' => 'debug',
            'file'  => APP_PATH.'../log/wechat-'.$weid.'.log',
        ],
        /**
         * 微信支付
         */
        'payment' => [
            'merchant_id'        => empty($weixin['merchant_id'])?'':$weixin['merchant_id'],
            'key'                => empty($weixin['payment_key'])?'':$weixin['payment_key'],
            'cert_path'          => APP_PATH.(empty($weixin['cert_path'])?'':$weixin['cert_path']),
            'key_path'           => APP_PATH.(empty($weixin['key_path'])?'':$weixin['key_path']),
            'notify_url'        => empty($weixin['notify_url'])?'':$weixin['notify_url'],
        ],
    );

    import("Lib/WeiXinPay/Autoload");
    $app = new EasyWeChat\Foundation\Application($options);

    return $app;
}


//推送维护
function push_weihu_tel($msg)
{
    $apikey = '48ccb80f6758b5f6ba917a8f6b050052';
    $mobile=M("site")->where(array('name' => "YUNWEI_TEL"))->find();
    $tpl_value = "#name#=" . C('WEB_NAME') . "&#msg#=" . $msg;
    $mobiles = explode(',', $mobile['valus']);

    foreach ($mobiles as $key) {
        $data = tpl_send_sms($apikey, "1494678", $tpl_value, $key);
    }
}

/**
 * 智能匹配模版接口发短信
 * apikey 为云片分配的apikey
 * text 为短信内容
 * mobile 为接受短信的手机号
 */
function send_sms($apikey, $text, $mobile)
{
    $url = "http://yunpian.com/v1/sms/send.json";
    $encoded_text = urlencode("$text");
    $mobile = urlencode("$mobile");
    $post_string = "apikey=$apikey&text=$encoded_text&mobile=$mobile";
    return sock_post($url, $post_string);
}


/**
 * 模板接口发短信
 * apikey 为云片分配的apikey
 * tpl_id 为模板id
 * tpl_value 为模板值
 * mobile 为接受短信的手机号
 */
function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile)
{
    $url = "http://yunpian.com/v1/sms/tpl_send.json";
    $encoded_tpl_value = urlencode("$tpl_value");  //tpl_value需整体转义
    $mobile = urlencode("$mobile");
    $post_string = "apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
    WeixinLog("tpl sms send: {$mobile} {$tpl_value}");
    return sock_post($url, $post_string);
}



/*
 * 返回域名层级分类
 */
function getDomainLayers($k=0){
    $layers = [1=>'微信群分享域名(第一层)',4=>'广告分享域名(第一层)',2=>'入口域名(第二层)',3=>'展示域名(第三层)'];

    if($k && isset($layers[$k])) return $layers[$k];
    return $layers;
}

/*
 *  判断是否在微信中运行
 */
function isInWeiXin()
{
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($user_agent, 'micromessenger') === false) {
        return false;
    } else {
        if (strpos($user_agent, "wechatdevtools")) {
            return false;
        }
        return true;

    }
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function cnzzLogin($siteId,$cookieFile){

    $login_url  = 'http://mt.cnzz.com/login.php?t=login&siteid='.$siteId;
    $post_fields = 'password=7813180';
    $header[] = "Referer: http://new.cnzz.com/v1/login.php?siteid=".$siteId;
    $header[] = "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9B176 MicroMessenger/4.3.2";
    //$header[] = "Referer: http://new.cnzz.com/v1/login.php?siteid=1254990082";
    $ch = curl_init($login_url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_exec($ch);
    curl_close($ch);
}

function cnzzGetRealtimeOnline($siteId){
    $cookieFile = tempnam('./cnzz_cookie','cookie');

    cnzzLogin($siteId,$cookieFile);

    $day = date("Y-m-d");
    $url="http://mt.cnzz.com/main.php?c=flow&a=realtime&action=module=getOnlineMaxUser|module=getCurrentOnlineOverview|module=onlinePvMixList&siteid={$siteId}&st={$day}&et={$day}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $contents = curl_exec($ch);
    curl_close($ch);
    preg_match("/<span class=\"data_size_32 data_m_cnzz_sec data_bold\">(\d+)<\/span>/",$contents,$online);
    //    var_dump($contents);
    if(empty($online) || count($online)!=2){
        return -1;
        //        cnzzLogin($cookieFile);
        //        return cnzzGetRealtimeOnline();
    }

    return $online[1];
}
