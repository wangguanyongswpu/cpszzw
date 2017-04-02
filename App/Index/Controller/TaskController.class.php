<?php
namespace Index\Controller;
use EasyWeChat\Core\Exception;
use Think\Controller;
use EasyWeChat\Message\News;
use EasyWeChat\Foundation\Application;
use Think\Think;
use IpLocationZh\Ip;

set_time_limit(0);

/*
 * 任务管理模块
 */

class TaskController extends Controller
{

    private $tasklist;
    protected $iplocation;  //IP地址
    protected $ip;  //IP

    public function _initialize()
    {
        $this->tasklist = M('task')->select();
    }

    //任务执行
    public function run()
    {

        foreach ($this->tasklist as $key => $v) {
            if ($v['status'] == 0) {
                //任务执行周期 默认为5分钟
                $chacktime = $v['chacktime'] ? $v['chacktime'] : 5;
                $newtime = $v['thistime'] + ($chacktime * 60);
                if (time() >= $newtime) {
                    echo "执行." . $v['name'] . "<br/>";
                    $this->$v['name']();
                }
            } else {
                //超时处理
                if ($v['overtime']) {
                    $overtime = $v['overtime'] * 60;
                    //超时
                    if ((time() - $v['thistime']) > $overtime) {
                        $_data = [
                            'status' => 0,
                        ];
                        M("task")->where(array('id' => $v['id']))->save($_data);
                    }
                }
            }
        }
    }

    public function test()
    {
        echo "aaaaa";

    }



    //同步IP转化省份
     function IPlocation()
    {
		$k_time = time();
		$lasttime=$k_time-5*60;
        $IPlist= M("payapi_log")->field('id,ip')->where('iplocation is null and  ip > 0 and  time >  unix_timestamp("2016-10-20 12") and time < '.$lasttime )->limit('2000')->select();
        $iploc=new Ip();
        foreach($IPlist as $key=> $value){
            $location=$iploc->find($value['ip']);
            $data['id']=$value['id'];
            $data['iplocation']=$location[1];
            M("payapi_log")->save($data);
        }
        $iploc->close();
		$losttime=time() - $k_time;
        echo  date("Y-m-d H:i:s")."运行间隔:".$losttime;
    }


    //检查微信自定义菜单拦截情况
    private function ChackWeixinUrl()
    {
        $ChackWeixinUrl = M("task")->where(array('name' => 'ChackWeixinUrl'))->find();
        if ($ChackWeixinUrl['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $ChackWeixinUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $ChackWeixinUrl['id']))->save($task_data);
            $weixin = M('weixin')->field("info,status,k")->where('status=1')->select();
            $k_time = time();
            foreach ($weixin as $key => $v) {
                $info = json_decode($v['info'], true);
                $url = "http://" . $info['url'] . "?gid=" . $v['k'];
                if ($this->chackurl($url) == false) {
                    $msg = "公众号" . $info['name'] . "入口网址:{$info['url']}被拦截";
                    $this->push_weihu_tel($msg);
                }

            }
            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];
            M("task")->where(array('id' => $ChackWeixinUrl['id']))->save($task_data);
        }
    }


    //检查支付接口链接
    private function ChackPayUrl()
    {
        $ChackPayUrl = M("task")->where(array('name' => 'ChackPayUrl'))->find();
        if ($ChackPayUrl['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $ChackPayUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data); //更新任务
            $k_time = time();//开始时间
            $url = C("PAY_API_URL");
            if ($this->chackurl($url) == false) {
                $msg = "支付接口网址被拦截";
                $this->push_weihu_tel($msg);
            }
            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];
            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data);
        }
    }

    //检查支付波动
    public function ChackPayTime()
    {
        $ChackPayUrl = M("task")->where(array('name' => 'ChackPayTime'))->find();
        if ($ChackPayUrl['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $ChackPayUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data); //更新任务
            $k_time = time();//开始时间
            $url = C("CPS_API_URL")."index.php?m=Ln&c=api&a=checkpay";
            $rtdata=$this->httpGet($url);
			WeixinLog(date("Ymd H:i:s") . " ChackPayTime : " .$rtdata);
            $rtdata=json_decode($rtdata,true);
            if ($rtdata['ret']==00) {
                $time=ceil($rtdata['data']['lost_time']/60);
                if(!$time) return ;
                $msg="充值间隔：超过".$time."分钟,请重新检查充值";
				WeixinLog(date("Ymd H:i:s") . " ChackPayTime_tel : " .$msg.' ret: '.$rtdata."\n");
				$this->push_weihu_tel($msg);
            }

            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];
            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data);
        }
    }

    //检查二维码接口链接
    private function ChackQRcode()
    {
        $ChackPayUrl = M("task")->where(array('name' => 'ChackQRcode'))->find();
        if ($ChackPayUrl['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $ChackPayUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data); //更新任务
            $k_time = time();//开始时间

            $app_id = C('PAY_API_URL_APP_ID');
            $sign = $this->get_cps_sign(array());
            $url = C("CPS_API_URL") . "index.php?m=Ln&c=api&a=getSkipUrl&app_id={$app_id}&sign={$sign}";
            $date=json_decode($this->httpGet($url),true);

            if($date['ret'] = '00'){
                foreach($date['data'] as $val){
                    if ($this->chackurl($val['url']) == false) {
                        $msg = "入口域名{$val['url']}被拦截";
                        $this->push_weihu_tel($msg);
                    }
                }
            }

            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];

            M("task")->where(array('id' => $ChackPayUrl['id']))->save($task_data);
        }
    }

    //轮换七牛域名
    private function UpdateIndexUrl()
    {
        $UpdateIndexUrl = M("task")->where(array('name' => 'UpdateIndexUrl'))->find();
        if ($UpdateIndexUrl['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $UpdateIndexUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $UpdateIndexUrl['id']))->save($task_data); //更新任务
            $k_time = time();//开始时间

            $arr = array(
                array(
                    'where' => 'type=1 AND look=0',
                    'n_where' => 'type=3 AND look=0',
                ),
                array(
                    'where' => 'type=1 AND look=1',
                    'n_where' => 'type=3 AND look=1',
                )
            );
            foreach($arr as $v) {
                $count = M('domain')->where($v['where'])->count();
                if ($count > 1) {
                    $ids = M('domain')->where($v['where'])->order('rand()')->limit($count - 1)->getField('id', true);
                    $n_ids = M('domain')->where($v['n_where'])->order('rand()')->limit(2)->getField('id', true);
                    if (count($n_ids) > 0) {
                        M('domain')->where(array('id' => array('in', $n_ids)))->data(array('type' => 1))->save();
                        M('domain')->where(array('id' => array('in', $ids)))->data(array('type' => 3))->save();
                        \Think\Log::record("qnssl domain new:" . implode(",", $n_ids) . "; close:" . implode(",", $ids));
                    }
                }
            }
            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];
            M("task")->where(array('id' => $UpdateIndexUrl['id']))->save($task_data);
        }
    }

    //检查展示页链接
    private function ChackIndexUrl()
    {
        $ChackIndexUrl = M("task")->where(array('name' => 'ChackIndexUrl'))->find();
        //$this->indexurl();
        if ($ChackIndexUrl['status'] == 0) {
            $this->indexurl_jb();

            $domain = M("domain")->where(array("type" => 1))->select();
            $task_data = [
                'status' => 1,
                'lasttime' => $ChackIndexUrl['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $ChackIndexUrl['id']))->save($task_data); //更新任务

            $k_time = time();//开始时间
            foreach ($domain as $key => $v) {
                $url = "http://" . $v['url'];
                if ($this->chackurl($url) == false) {
                    $d = array('type' => 2,'ban_time'=>time());
                    M("domain")->where(array('id' => $v['id']))->save($d);
                    \Think\Log::record("domain {$v['id']}: set to baned :".json_encode($v));
                    $newD = M("domain")->limit(1)->where(array('type'=>3, 'layer' => $v['layer'], 'look' => $v['look']))->find();
                    if($newD){

                        var_dump("domain {$newD['id']}: set to use: ".json_encode($newD));
                        $b=array('type'=>1);
                        M("domain")->where(array('id' => $newD['id']))->save($b);
                    }else{
                        $tel = C('DEV_TELS');
                        sms("第{$v['layer']}层域名还只剩下0个!立刻补充!",$tel);
                    }
                    //$msg = "七牛网址被拦截:{$v['url']}";
                    //$this->push_weihu_tel($msg);
                }
            }
            $task_data = [
                'status' => 0,
                'consuming' => time() - $k_time,
            ];
            M("task")->where(array('id' => $ChackIndexUrl['id']))->save($task_data);
        }
    }

    /*
     * 检查展示页域名--已经废弃
     *
     */
    private function indexurl()
    {
        $num = M('domain')->where("type=1")->count();
        $_num = 3;
        if ($num < 2) {
            $rnad = $_num - $num;
            $beiyong = M("domain")->where(array('type' => 3))->limit($rnad)->select();
            foreach ($beiyong as $key => $v) {
                M('domain')->where(array("id" => $v['id']))->save(array(
                    'type' => 1
                ));
            }
        }
    }

    /*
     * 检查每层域名 及时切换备用
     *
     * 当启用域名小于3个时切换一个
     */
    private function enableBackupDomain()
    {
        for ($i = 1; $i <= 10; $i++) {
            $num  = M('domain')->where(array("type" => 1, 'layer' => $i))->count();
            $num  = $num ? $num : 0;
            $_num = 3;
            if ($num <= 2) {
                $rnad    = $_num - $num;
                $beiyong = M("domain")->where(array("type" => 3, 'layer' => $i))->limit($rnad)->select();
                if(!$beiyong) return false;
                foreach ($beiyong as $key => $v) {
                    M('domain')->where(array("id" => $v['id']))->save(array('type' => 1));
                }
            }
        }
    }

    //展示页域名警报
    private function indexurl_jb()
    {
        $tel = C('DEV_TELS');

        $num = M('domain')->where("look=0 AND (type =1 or type = 3) AND layer=3")->count();
        if ($num < 1) {
//            $this->push_weihu_tel("展示页域名还只剩下一个!立刻补充!");
            sms("第三层展示页域名还只剩下{$num}个!立刻补充!",$tel);
        }

        $num = M('domain')->where("look=0 AND type = 1 AND layer=3")->count();
        if ($num < 1) {
            //            $this->push_weihu_tel("展示页域名还只剩下一个!立刻补充!");
            sms("启用的第三层展示页域名还只剩下{$num}个!立刻补充!",$tel);
        }

//        $num = M('domain')->where("look=1 AND (type =1 or type = 3) AND layer=3")->count();
//        if ($num == 1) {
//            $this->push_weihu_tel("分享域名还只剩下一个!立刻补充!");
//        }


        $num = M('domain')->where("(type =1 or type = 3) AND layer=2")->count();
        if ($num < 1) {
//            $this->push_weihu_tel("入口域名还只剩下一个!立刻补充!");
            sms("第二层入口域名还只剩下{$num}个!立刻补充!",$tel);
        }

       /* $num = M('domain')->where("(type =1 or type = 3) AND layer=1")->count();
        if ($num < 3) {
            //$this->push_weihu_tel("微信群分享域名还只剩下一个!立刻补充!");
            sms("第一层微信群分享域名还只剩下{$num}个!立刻补充!",$tel);
        }*/

        /*$num = M('domain')->where("(type =1 or type = 3) AND layer=4")->count();
        if ($num == 1) {
            $this->push_weihu_tel("广告分享域名还只剩下一个!立刻补充!");
        }*/
    }


    //手动推送任务
    private function PushWeixinInfo()
    {
        $PushWeixinInfo = M("task")->where(array('name' => 'PushWeixinInfo'))->find();
        if ($PushWeixinInfo['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $PushWeixinInfo['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $PushWeixinInfo['id']))->save($task_data); //更新任务
            $k_time = time();
            $push = M("push")->where(array('status' => 0))->select();
            $where = [];
            if ($push['where'] == 1) {
                $where['vip'] = 1;
            }
            if ($push['where'] == 0) {
                $where['vip'] = 0;
            }
            foreach ($push as $key => $push_v) {
                $push_info = json_decode($push_v['info'], true);
                $weixin = M('weixin')->select();
                foreach ($weixin as $key => $v) {
                    $gid = $v['k'];
                    $_weixin = json_decode($v['info'], true);
                    $where['gid'] = $gid;
                    $where['follow'] = 1;
                    if (empty($push_info['url'])) {
                        $push_info['url'] = "http://" . $_weixin['url'] . "?gid=" . $gid;
                    }
                    $user = M('user')->where($where)->select();
                    $num = 0;
                    foreach ($user as $key => $v2) {
                        $num++;
                        $push_info['touser'] = $v2['openid'];
                        $this->push_WeixinInfo($push_info, $_weixin);
                    }
                }
                $push_data = array(
                    'status' => 1,
                    'num' => $num,
                    'etime' => time()
                );
                M("push")->where(array('id' => $push_v['id']))->save($push_data);
            }
        }
        $task_data = [
            'status' => 0,
            'consuming' => time() - $k_time,
        ];
        M("task")->where(array('id' => $PushWeixinInfo['id']))->save($task_data);
    }

    //更新跳转域名
    private function UpdateJumpDomain()
    {
        $PushWeixinInfo = M("task")->where(array('name' => 'UpdateJumpDomain'))->find();
        if ($PushWeixinInfo['status'] == 0) {
            $task_data = [
                'status' => 1,
                'lasttime' => $PushWeixinInfo['thistime'],
                'thistime' => time(),
            ];
            M("task")->where(array('id' => $PushWeixinInfo['id']))->save($task_data); //更新任务
            $k_time = time();

            $app_id = C('PAY_API_URL_APP_ID');
            $sign = $this->get_cps_sign(array('lasttime'=>$task_data['lasttime']));
            $url = C("CPS_API_URL") . "index.php?m=Ln&c=api&a=updateJumpDomain&app_id={$app_id}&lasttime={$task_data['lasttime']}&sign={$sign}";
            $ret = $this->httpGet($url);

        }
        $task_data = [
            'status' => 0,
            'consuming' => time() - $k_time,
        ];
        M("task")->where(array('id' => $PushWeixinInfo['id']))->save($task_data);
    }

    //推送维护
    private function push_weihu_tel($msg)
    {

        $mobile = M("site")->where(array('name' => "YUNWEI_TEL"))->find();
        
        if($mobile['valus']){
            $ret = sms($msg,$mobile['valus']);
            WeixinLog("push sms: {$mobile['valus']} msg: {$msg} ret: $ret");
        }else{
            WeixinLog("push sms err: {$mobile['valus']} msg: {$msg}");
        }
        return true;

//        $tpl_value = "#name#=平台B&#msg#=" . $msg;
//        $mobiles = explode(',', $mobile['valus']);
//
//        $apikey = '48ccb80f6758b5f6ba917a8f6b050052';
//        foreach ($mobiles as $key) {
//            $data = $this->tpl_send_sms($apikey, "1494678", $tpl_value, $key);
//        }
    }


    //检查链接 
    public function chackurl($url)
    {
//        $_url = "http://www.yundq.cn/url/wx?key=693E6C9885DC4231&url=" . $url;
//        $data = $this->httpGet($_url);
//
//        WeixinLog("task url check: {$url} : {$data}");
//        if (strpos($data, '黑名单')!==false) {
//            return false;
//        } elseif(strpos($data, '可访问')!==false||strpos($data, '待验证')!==false||strpos($data, '剩余验证次数')!==false) {
//            return true;
//        }else{
//            $message="第三方接口返回数据异常,请手动检测";
//            $pushtime=$_SESSION['panduan'];
//            if(time()-$pushtime>=3600){
//                //$this->push_weihu_tel($message);
//                $_SESSION['panduan']=time();
//            }
//            echo  '<a style="font-size:13pt;color:red;">'.$message.':'.'<a>';
//            return true;
//        }
        return true;
        $_url = "http://www.3045.com.cn:8889/Api.php?url={$url}&_=".time();
        $data = $this->httpGet($_url);

        WeixinLog("task url check: {$url} : {$data}");
        $ret = json_decode($data,true);
        if(isset($ret['code']) && $ret['code']===1){
            return false;
        }elseif(isset($ret['code']) && $ret['code']===0){
            return true;
        }else{
            $message="第三方接口返回数据异常,请手动检测";
            $pushtime=$_SESSION['panduan'];
            if(time()-$pushtime>=3600){
                //$this->push_weihu_tel($message);
                $_SESSION['panduan']=time();
            }
            echo  '<a style="font-size:13pt;color:red;">'.$message.':'.'<a>';
            return true;
        }

    }


    protected function push_WeixinInfo($data, $weixin)
    {
        $json = '{"touser":"' . $data["touser"] . '","msgtype":"news","news":{"articles": [ { "title":"' . $data["title"] . '","description":"' . $data["description"] . '","url":"' . $data['url'] . '","picurl":"' . $data["picurl"] . '"}]}}';
        $Token = $this->GetWeixinToken($weixin['app_id'], $weixin['secret']);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $Token;
        $user = $this->https_request($url, $json);
        return json_decode($user, true);
    }


    /**
     * 智能匹配模版接口发短信
     * apikey 为云片分配的apikey
     * text 为短信内容
     * mobile 为接受短信的手机号
     */
    private function send_sms($apikey, $text, $mobile)
    {
        $url = "http://yunpian.com/v1/sms/send.json";
        $encoded_text = urlencode("$text");
        $mobile = urlencode("$mobile");
        $post_string = "apikey=$apikey&text=$encoded_text&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }


    /**
     * 模板接口发短信
     * apikey 为云片分配的apikey
     * tpl_id 为模板id
     * tpl_value 为模板值
     * mobile 为接受短信的手机号
     */
    private function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile)
    {
        $url = "http://yunpian.com/v1/sms/tpl_send.json";
        $encoded_tpl_value = urlencode("$tpl_value");  //tpl_value需整体转义
        $mobile = urlencode("$mobile");
        $post_string = "apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";

        $ret =  $this->sock_post($url, $post_string);
        WeixinLog("task sms send: {$mobile} {$tpl_value} {$ret}");
        return $ret;
    }


    /**
     * url 为服务的url地址
     * query 为请求串
     */
    private function sock_post($url, $query)
    {
        $data = "";
        $info = parse_url($url);
        $fp = fsockopen($info["host"], 80, $errno, $errstr, 30);
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
        $write = fputs($fp, $head);
        $header = "";
        while ($str = trim(fgets($fp, 4096))) {
            $header .= $str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }
        return $data;
    }

    public function checkWecatAccountValidTask()
    {

        $weixin = M('weixin')->where(['status' => 1])->field("id,status,info")->select();
        foreach ($weixin as $key => $v) {
            $info = json_decode($v['info'], true);
            WeixinLog('wechat account chek:' . $info['name']);
            $ret = $this->checkWecatAccountValid($info['app_id'], $info['secret']);
            $msg = "公众号: " . $info['name'] . " ret" . print_r($ret, true);
            if (!$ret) {
                $msg = "公众号被封: " . $info['name'];
                WeixinLog($v['k'] . ' ' . $msg . ', status will set to 2');
                M("weixin")->where(array('id' => $v['id']))->save(['status' => 2]);
                //$this->push_weihu_tel($msg);
            }

        }
    }

    public function checkWecatAccountValid($appId, $appSecret)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        WeixinLog('wechat account chek raw ret:' . $res);
        curl_close($curl);
        if (strpos($res, '"errcode":50002') !== false) {
            return false;
        }

        return true;
    }

    /**
     * 检测各层域名是否被微信屏蔽 p=8dbcd3c9e6d2477e3103fabc90391dbc
     */
   public function CheckWxUrl(){
        header("Content-type: text/html; charset=utf-8");
        $p=$_GET['p'];
        if($p!=md5("allow_to_invit")){
            $this->error('无权访问');
        }

        echo '<script language="javascript">
setTimeout("self.location.reload();",300000);
</script>';
        /*
        $weixin = M('weixin')->field("info,status,k")->where('status=1')->select();

        foreach ($weixin as $key => $v) {
            $info = json_decode($v['info'], true);
            $url = "http://" . $info['url'] . "?gid=" . $v['k'];
            if ($this->chackurl($url) == false) {
                $msg = "公众号" . $info['name'] . "入口网址:{$info['url']}被拦截";
                echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
                echo '<br/>';
            }else{
                $msg="公众号" . $info['name'] . "入口网址:{$info['url']}使用正常";
                echo $msg;echo '<br/>';
            }
        }
*/
//            $app_id = C('PAY_API_URL_APP_ID');
//            $sign = $this->get_cps_sign(array());
//            $url = C("CPS_API_URL") . "index.php?m=Ln&c=api&a=getSkipUrl&app_id={$app_id}&sign={$sign}";
//            $date=json_decode($this->httpGet($url),true);
//
//            if($date['ret'] = '00'){
//                foreach($date['data'] as $val){
//                    if ($this->chackurl($val['url']) == false) {
//                        $msg = "入口域名{$val['url']}被拦截";
//                        echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
//                echo '<br/>';
//                    }else{
//                        $msg="入口域名{$val['url']}使用正常";
//                echo $msg;echo '<br/>';
//                    }
//                }
//            }

       $domain = M("domain")->where(array("type" => 1,'layer'=>1))->select();
       foreach ($domain as $key => $v) {
           $url = "http://" . $v['url'];
           if ($this->chackurl($url) == false) {
               $msg = "第一层微信群分享网址被拦截:{$v['url']}";
               echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
               echo '<br/>';
           }else{
               $msg="第一层微信群分享网址使用正常:{$v['url']}";
               echo $msg;echo '<br/>';
           }
       }

       $domain = M("domain")->where(array("type" => 1,'layer'=>2))->select();
       foreach ($domain as $key => $v) {
           $url = "http://" . $v['url'];
           if ($this->chackurl($url) == false) {
               $msg = "第二层入口网址被拦截:{$v['url']}";
               echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
               echo '<br/>';
           }else{
               $msg="第二层入口网址使用正常:{$v['url']}";
               echo $msg;echo '<br/>';
           }
       }



       $domain = M("domain")->where(array("type" => 1,'layer'=>3))->select();
        foreach ($domain as $key => $v) {
            $url = "http://" . $v['url'];
            if ($this->chackurl($url) == false) {
                $msg = "七牛网址被拦截:{$v['url']}";
                echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
                echo '<br/>';
            }else{
                $msg="七牛网址使用正常:{$v['url']}";
                echo $msg;echo '<br/>';
            }
        }

        $url = C("PAY_API_URL");
        if ($this->chackurl($url) == false) {
            $msg = "支付接口网址被拦截{$url}";
            echo '<a style="font-size:13pt;color:red;">'.$msg.':'.'<a>';
            echo '<br/>';
        }else{
            $msg="支付网址使用正常:{$url}";
            echo $msg;echo '<br/>';
        }
    }

    /*
     * 抓取CNZZ实时在线,并与上次在线做对比分析报警
     */
    public function syncCnzzRealtimeOnline(){
        $siteId = '1254990082';
        $online = cnzzGetRealtimeOnline($siteId);

        $tb = M("log_online");
        $data = [
            'site_id'=>$siteId,
            'num'=>intval($online),
            'created_at'=>time(),
        ];
        $tb->add($data);

        $timeStart = time()-300;
        $nums = $tb->where("num>=0 AND created_at>".$timeStart)->order('created_at desc')->limit(2)->select();
        //近期数据不足,无法判断
        if(count($nums)<2){
            return false;
        }
        //        var_dump($nums);
        //比例超过20%,绝对值超过100个即可报警
        $tel = '13540639499';//C('DEV_TELS');
        $minRate = 0.6;
        $minNum = 50;

        $lasNum = $nums[1]['num'];
        $nowNum = $nums[0]['num'];
        $nowRate = $nowNum/$lasNum;
        $onlineStr = "当前在线{$nowNum}(".date('H:i:s',$nums[0]['created_at'])."), 上次在线{$lasNum}(".date('H:i:s',$nums[1]['created_at']).")\n";

        if(($lasNum>100 && $nowRate<$minRate) || $lasNum-$nowNum>$minNum){
            $msg = "CNZZ在线异常:".$onlineStr;
            echo $msg;
            sms($msg,$tel);
            return true;
        }

        echo "CNZZ在线正常:".$onlineStr;

    }

    /*
     * 检测备用七牛域名是否配置正常
     *
     * 只检查域名网络可否访问及返回的是否是七牛的配置错误提醒消息,不检查微信拦截
     */
    public function qiniuSyncCheck(){
        //只取一个,防止获取太多,检测时候这个记录被其它进程重复检测
        $domain = M("domain")->where(array("type" => 3,'layer'=>3,'checked'=>['EXP','IS NULL']))->order('id ASC')->limit(10)->select();
        foreach ($domain as $key => $v) {
            if (strpos($v['url'], 'qnssl.com') === false || strpos($v['url'], '.clouddn.com') === false) {
                //目前只检测七牛域名
//                continue;
//                unset($domain[$key]);
            }
            //正在检测,防止其它进程获取到
            $d = ['checked' => 0];
            M("domain")->where(array('id' => $v['id']))->save($d);
        }

        foreach ($domain as $key => $v) {

            $url = "http://" . $v['url'].'/cdn7041a5d93d4ccb94ef533ab7025c5f90.html';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            try{
                $res = curl_exec($curl);
            }catch (Exception $e){
                $res = $e->getMessage();
            }
            curl_close($curl);
            $res = trim($res);

            if($res!='cdn-a5d93d4ccb94ef533ab7025c5f90'){
                if(!$res) $res='unknown err.';

                //$d=['type'=>2,'check_ret'=>$res,'checked'=>-1];
                $d=['check_ret'=>$res,'checked'=>-1];
                $msg = "网访问异常:{$v['url']}: '{$res}'\n";
                echo $msg;
                M("domain")->where(array('id' => $v['id']))->save($d);
            }else{
                $d=['checked'=>1];
                M("domain")->where(array('id' => $v['id']))->save($d);
                $msg = "网访问正常:{$v['url']}: {$res}\n";
                echo $msg;
            }
        }
    }


    /*
     * 检查cps系统响应情况
     */
    public function checkCpsValid()
    {
        $url = C("CPS_API_URL") . "index.php?m=Ln&c=login&a=index";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        //WeixinLog('cps chek raw ret:' . $res);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (intval($httpCode)>400 || strlen($res)<200 || strpos($res, 'err') !== false) {
            $tel = '13540639499';//C('DEV_TELS');
            $msg = "cps后台访问异常: httpcode:{$httpCode}, ret:".$res;
            echo $msg;
            sms($msg,$tel);
        }

        return true;
    }
	
	/**
     * 昨前两天充值统计
     */
    public function total()
    {
        header("Content-type: text/html; charset=utf-8");

        $arr = [
            /*[
                'title'=>'今天('.date('Y-m-d').')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d')),
            ],*/
            [
                'title'=>'昨天('.date('Y-m-d',strtotime('-1 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-1 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d')),
            ],
            [
                'title'=>'前天('.date('Y-m-d',strtotime('-2 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-2 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d',strtotime('-1 day'))),
            ]
        ];
        $sms_content="\r\n用户充值收支统计:\r\n";
        foreach ($arr as $v){
            $sms_content.=$v['title'] . "\r\n";

            $where = 'pay_amount>1'.$v['d_where'];
            $sum = M('pay_log')->where($where)->sum('pay_amount');
            //	echo M('pay_log')->getLastSql();
            //		echo "<br/>";
            $k_sum = M('pay_log')->where($where.' AND account=0')->sum('pay_amount');
            $u_sum = M('pay_log')->where($where.' AND account=-1')->sum('pay_amount');
            $n_sum = M('pay_log')->where($where.' AND account>=1')->sum('pay_amount');

            $sms_content.='参数丢失：'.(empty($u_sum)?0:$u_sum) . "\r\n";
            $sms_content.='扣量：'.(empty($k_sum)?0:$k_sum)  . "\r\n";
            $sms_content.='未扣量：'. (empty($n_sum)?0:$n_sum) . "\r\n";
            $sms_content.='合计：'.(empty($sum)?0:$sum)  . "\r\n";
        }
		sms($sms_content,'15199122312,13540639499,13708056674,18349223526');
		$sms_content=str_replace("\r\n",'  ',$sms_content);
		WeixinLog("task total sms: {$sms_content} ");
    }


    /**
     * 今天充值统计
     */
    public function totalnew()
    {
        header("Content-type: text/html; charset=utf-8");

        $arr = [
            [
                'title'=>'今天('.date('Y-m-d').')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d')),
            ],
            /*[
                'title'=>'昨天('.date('Y-m-d',strtotime('-1 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-1 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d')),
            ],
            [
                'title'=>'前天('.date('Y-m-d',strtotime('-2 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-2 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d',strtotime('-1 day'))),
            ]*/
        ];
        $sms_content="\r\n老平台用户充值收支统计:\r\n";
        foreach ($arr as $v){
            $sms_content.=$v['title'] . "\r\n";

            $where = 'pay_amount>1'.$v['d_where'];
            $sum = M('pay_log')->where($where)->sum('pay_amount');
            //  echo M('pay_log')->getLastSql();
            //      echo "<br/>";
            $k_sum = M('pay_log')->where($where.' AND account=0')->sum('pay_amount');
            $u_sum = M('pay_log')->where($where.' AND account=-1')->sum('pay_amount');
            $n_sum = M('pay_log')->where($where.' AND account>=1')->sum('pay_amount');

            $sms_content.='参数丢失：'.(empty($u_sum)?0:$u_sum) . "\r\n";
            $sms_content.='扣量：'.(empty($k_sum)?0:$k_sum)  . "\r\n";
            $sms_content.='未扣量：'. (empty($n_sum)?0:$n_sum) . "\r\n";
            $sms_content.='合计：'.(empty($sum)?0:$sum)  . "\r\n";
        }
        sms($sms_content,'15199122312,13540639499,13708056674,18349223526');
        $sms_content=str_replace("\r\n",'  ',$sms_content);
        WeixinLog("task total sms: {$sms_content} ");
    }


    /**
     * 万邦支付昨前两天充值统计
     */
    public function wbtotal()
    {
        header("Content-type: text/html; charset=utf-8");

        $arr = [
            /*[
                'title'=>'今天('.date('Y-m-d').')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d')),
            ],*/
            [
                'title'=>'昨天('.date('Y-m-d',strtotime('-1 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-1 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d')),
            ],
            [
                'title'=>'前天('.date('Y-m-d',strtotime('-2 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-2 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d',strtotime('-1 day'))),
            ]
        ];
        $sms_content="\r\n万邦平台用户充值收支统计:\r\n";
        foreach ($arr as $v){
            $sms_content.=$v['title'] . "\r\n";

            $where = 'pay_amount>1'.$v['d_where'];
            $sum = M('pay_log')->where($where)->sum('pay_amount');
            $uids = M('member')->field('uid')->where(array('internal_account'=>1))->select();
            $sql = M('member')->getLastSql();
            // 内部
            $uidsn = M('member')->field('uid')->where(array('internal_account'=>2))->select();
            $sqln = M('member')->getLastSql();
            //      echo "<br/>";
            $k_sum = M('pay_log')->where($where.' AND account=0 AND ref1_id in (' . $sql .')')->sum('pay_amount');
            $u_sum = M('pay_log')->where($where.' AND account=-1 AND ref1_id in (' . $sql .')')->sum('pay_amount');
            $n_sum = M('pay_log')->where($where.' AND account>=1 AND ref1_id in (' . $sql .')')->sum('pay_amount');
            $n_sumn = M('pay_log')->where($where.' AND ref1_id in (' . $sqln . ')')->sum('pay_amount');

            $k_sum=sprintf(" %1\$.2f",$k_sum);
            $u_sum=sprintf(" %1\$.2f",$u_sum);
            $n_sum=sprintf(" %1\$.2f",$n_sum);
            $n_sumn=sprintf(" %1\$.2f",$n_sumn);
            $sms_content.='参数丢失：'.(empty($u_sum)?0:$u_sum) . "\r\n";
            $sms_content.='扣量：'.(empty($k_sum)?0:$k_sum)  . "\r\n";
            $sms_content.='未扣量：'. (empty($n_sum)?0:$n_sum) . "\r\n";
            $sms_content.='内部充值（不扣量）:'. (empty($n_sumn)?0:$n_sumn) . "\r\n";
            $sms_content.='合计：'.(empty($sum)?0:$sum)  . "\r\n";
        }
        sms($sms_content,'13708056674,18349223526');
        $sms_content=str_replace("\r\n",'  ',$sms_content);
        WeixinLog("task total sms: {$sms_content} ");
    }


    /**
     * 万邦平台今天充值统计
     */
    public function wbtotalnew()
    {
        header("Content-type: text/html; charset=utf-8");

        $arr = [
            [
                'title'=>'今天('.date('Y-m-d').')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d')),
            ],
            /*[
                'title'=>'昨天('.date('Y-m-d',strtotime('-1 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-1 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d')),
            ],
            [
                'title'=>'前天('.date('Y-m-d',strtotime('-2 day')).')',
                'd_where' => ' AND pay_time>='.strtotime(date('Y-m-d',strtotime('-2 day'))) . ' AND pay_time<'.strtotime(date('Y-m-d',strtotime('-1 day'))),
            ]*/
        ];
        $sms_content="\r\n万邦平台用户充值收支统计:\r\n";
        foreach ($arr as $v){
            $sms_content.=$v['title'] . "\r\n";

            $where = 'pay_amount>1'.$v['d_where'];
            $sum = M('pay_log')->where($where)->sum('pay_amount');
            $sql = "SELECT uid FROM qw_member WHERE internal_account=1";
            //  echo M('pay_log')->getLastSql();
            //      echo "<br/>";
            $k_sum = M('pay_log')->where($where.' AND account=0 AND ref1_id in ( SELECT uid FROM qw_member WHERE internal_account=1 )')->sum('pay_amount');
            $u_sum = M('pay_log')->where($where.' AND account=-1 AND ref1_id in ( SELECT uid FROM qw_member WHERE internal_account=1 )')->sum('pay_amount');
            $n_sum = M('pay_log')->where($where.' AND account>=1 AND ref1_id in ( SELECT uid FROM qw_member WHERE internal_account=1 )')->sum('pay_amount');

            $n_sumn = M('pay_log')->where($where.' AND ref1_id in ( SELECT uid FROM qw_member WHERE internal_account=2 )')->sum('pay_amount');

            $k_sum=sprintf(" %1\$.2f",$k_sum);
            $u_sum=sprintf(" %1\$.2f",$u_sum);
            $n_sum=sprintf(" %1\$.2f",$n_sum);
            $n_sumn=sprintf(" %1\$.2f",$n_sumn);
            $sms_content.='参数丢失：'.(empty($u_sum)?0:$u_sum) . "\r\n";
            $sms_content.='扣量：'.(empty($k_sum)?0:$k_sum)  . "\r\n";
            $sms_content.='未扣量：'. (empty($n_sum)?0:$n_sum) . "\r\n";
            $sms_content.='内部充值（不扣量）：'. (empty($n_sumn)?0:$n_sumn) . "\r\n";
            $sms_content.='合计：'.(empty($sum)?0:$sum)  . "\r\n";
        }
        sms($sms_content,'13708056674,18349223526');
        $sms_content=str_replace("\r\n",'  ',$sms_content);
        WeixinLog("task total sms: {$sms_content} ");
    }
}

?>
