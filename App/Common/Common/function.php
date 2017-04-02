<?php
/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2015-09-17
* 版    本：1.0.0
* 功能说明：模块公共文件。
*
**/


function UpImage($callBack="image",$width=100,$height=100,$image=""){
    echo '<iframe scrolling="no" frameborder="0" border="0" onload="this.height=this.contentWindow.document.body.scrollHeight;this.width=this.contentWindow.document.body.scrollWidth;" width='.$width.' height="'.$height.'"  src="'.U('Upload/uploadpic',['Width'=>$width,'Height'=>$height,'BackCall'=>$callBack,'Img'=>$image]).'"></iframe>
         <input type="hidden" name="'.$callBack.'" id="'.$callBack.'">';
}
function BatchImage($callBack="image",$height=300,$image=""){
    echo '<iframe scrolling="no" frameborder="0" border="0" onload="this.height=this.contentWindow.document.body.scrollHeight;this.width=this.contentWindow.document.body.scrollWidth;" src="'.U('Upload/batchpic').'?BackCall='.$callBack.'&Img='.$image.'"></iframe>
		<input type="hidden" name="'.$callBack.'" id="'.$callBack.'">';
}


/*
 * 函数：网站配置获取函数
 * @param  string $k      可选，配置名称
 * @return array          用户数据
*/
function setting($k=''){
	if($k==''){
        $setting =M('setting')->field('k,v')->select();
		foreach($setting as $k=>$v){
			$config[$v['k']] = $v['v'];
		}
		return $config;
	}else{
		$model = M('setting');
		$result=$model->where("k='{$k}'")->find(); 
		return $result['v'];
	}
}

/**
 * 函数：格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 函数：加密
 * @param string            密码
 * @return string           加密后的密码
 */
function password($password){
	/*
	*后续整强有力的加密函数
	*/
	return md5('Q'.$password.'W');

}

/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
function random($length=6, $type='string', $convert=0){
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );
    
    if(!isset($config[$type])) $type = 'string';
    $string = $config[$type];
    
    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string{mt_rand(0, $strlen)};
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}


function get_ip() {
        $clientip = '';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $clientip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $clientip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $clientip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $clientip = $_SERVER['REMOTE_ADDR'];
        }

        preg_match("/[\d\.]{7,15}/", $clientip, $clientipmatches);
        $clientip = $clientipmatches[0] ? $clientipmatches[0] : 'unknown';
        return $clientip;
    }



//ip2long函数负数BUG
function iplong($ip){ 
return bindec(decbin(ip2long($ip)));
}

/**
 *  获取当前ip的long
 */
function get_iplong(){
   $ip = get_ip();
   return iplong($ip);
}

function WeixinLog($msg){
    $msg .= "\r\n";
    $file = "./log/" . date("Ymd") . ".log";
    if(PHP_SAPI=='cli'){
        echo date("Ymd H:i:s") . ' ' . $msg;
    }

    //touch($file);
    if (file_put_contents($file, date("Ymd H:i:s") . ' ' . $msg, FILE_APPEND)) {
        return true;
    } else {
        return false;
    }
}

/*
 * thinkphp 日志简单封装,日志添加完全的时间及毫秒数
 */
function thinklog($msg, $lv='DEBUG'){
    list($usec, $sec) = explode(".", time());
    $date = date('Ymd H:i:s.').substr($usec,0,3);
    Think\Log::record($date.' '.$msg, $lv);
}

/**
 * 智能匹配模版接口发短信
 *
 * text 为短信内容
 * mobile 为接受短信的手机号,多个手机号以,分隔
 */
function sms($text, $mobile)
{
    //相同内容1小时之内只发一次
    $ckey = md5($text);
    $sended = S($ckey);
    if($sended) return;

    $apikey = '48ccb80f6758b5f6ba917a8f6b050052';
    $url = "https://sms.yunpian.com/v2/sms/tpl_batch_send.json";
    $tpl_value = "#name#=" . C('WEB_NAME') . "&#msg#=" . $text;
    $encoded_text = urlencode("$tpl_value");
    $mobile = urlencode("$mobile");
    $post_string = "apikey=$apikey&tpl_id=1494678&tpl_value=$encoded_text&mobile=$mobile";

    $ret =  sock_post($url, $post_string);
    return $ret;

    //    $apikey = "48ccb80f6758b5f6ba917a8f6b050052";
    //    $tplId = '1494678';
    //
    //    $data=array('tpl_id'=>$tplId,'tpl_value'=>('#name#').'='.urlencode('TXBB').'&'.urlencode('#msg#').'='.urlencode($text),'apikey'=>$apikey,'mobile'=>$mobile);
    //    $ch = curl_init();
    //    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
    //    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    //    curl_setopt($ch, CURLOPT_POST, 1);
    //    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //    curl_setopt ($ch, CURLOPT_URL, "https://sms.yunpian.com/v2/sms/tpl_batch_send.json");
    //    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    //    $ret = curl_exec($ch);
    //    curl_close($ch);
    //
    //    WeixinLog("push sms http: {$mobile} msg: {$text}, ret:{$ret}");
    //    S($ckey,time(),3600);
    //
    //    return $ret;
}


/**
 * url 为服务的url地址
 * query 为请求串
 */
function sock_post($url, $query)
{
    $data = "";
    $info = parse_url($url);
    $fp   = fsockopen($info["host"], 80, $errno, $errstr, 30);
    if (!$fp) {
        return $data;
    }
    $head = "POST " . $info['path'] . " HTTP/1.0\r\n";
    $head .= "Host: " . $info['host'] . "\r\n";
    $head .= "Referer: http://" . $info['host'] . $info['path'] . "\r\n";
    $head .= "Content-type: application/x-www-form-urlencoded\r\n";
    $head .= "Content-Length: " . strlen(trim($query)) . "\r\n";
    $head .= "\r\n";
    $head .= trim($query);
    $write  = fputs($fp, $head);
    $header = "";
    while ($str = trim(fgets($fp, 4096))) {
        $header .= $str;
    }
    while (!feof($fp)) {
        $data .= fgets($fp, 4096);
    }
    return $data;
}