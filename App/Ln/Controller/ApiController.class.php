<?php

/**

 * 日    期：2016-07-14

 * 版    本：1.0.0

 * 功能说明：注册记录和充值记录Api。

 *

 **/



namespace Ln\Controller;

use Think;

use Common\Controller\BaseController;



class ApiController extends BaseController
{
    protected $data;


    public function _initialize()
    {
        if(IS_POST){
            $this->data = I('post.');
        } else {
            $this->data = I('get.');
        }

        Think\Log::record(json_encode($this->data),'DEBUG',true);

        $no_sign = [''];//['getSkipUrl','getPayInfo'];
        //校验签名
//        if(!in_array(ACTION_NAME, $no_sign) && !$this->checkSign($this->data)){
//            echo $this->formatResponse(array('ret' => '20', 'msg' => '签名错误！'));
//            exit;
//        }
    }


//生成二维码
    public function chenQrCode(){

        $uid  = I('get.uid',0,'intval');
        $we_id  = I('get.we_id','');
        $path = dirname(APP_PATH);
        $t_path = '/Public/Ln/images/';
        $filename = 'chenqrcode/' .$we_id . '/' . $uid .'_'. $we_id.'.png';
        $img_url = $t_path.$filename;
        if(!file_exists($path .$t_path . $filename)){
            $url = 'http://'.$we_id.'/' . date('YmdHis') . '/'.$uid.'.gif';
            $img_url = $this->QrCode($filename, $url);
        }
        echo json_encode($img_url);

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

    /**
     * 添加注册记录
     */
    public function register()
    {exit;
        $prefix = C('DB_PREFIX');

        $data = array();
        $count = M('register_log')->where("openid='{$this->data['openid']}' AND cps_app_id='{$this->data['app_id']}'")->count();
        if($count > 0){
            echo $this->formatResponse(array('ret' => '10', 'msg' => '已注册'));
            return ;
        }

        $ref_id = intval($this->data['refid']);
        $member = M('member')->field('user,cid,g.uid,g.group_id AS gid,t')
            ->join("{$prefix}auth_group_access g ON g.uid={$prefix}member.uid", 'LEFT')
            ->where("{$prefix}member.uid={$ref_id}")
            ->find();

        if(!$ref_id || empty($member) || empty($member['gid']) || $member['gid'] == 1){
            $data['account'] = 0;
            $data['ref1_id'] = $ref_id;
            $data['ext'] = $ref_id;
        } elseif($member['gid'] == 2) {
            $data['ref1_id'] = $ref_id;

            $data['ref1_name'] = $member['user'];

        } elseif($member['gid'] == 3) {

            $parent = M('member')->where('uid='.$member['cid'])->find();

            $data['ref1_id'] = $parent['uid'];

            $data['ref1_name'] = $parent['user'];

            $data['ref2_id'] = $ref_id;

            $data['ref2_name'] = $member['user'];

        }



        $data['uid'] = $this->data['uid'];

        $data['username'] = $this->data['username'];

        $data['sex'] = $this->data['sex'];

        $data['openid'] =  $this->data['openid'];
        $data['gid'] =  $this->data['gid'];

        $data['mobile'] = $this->data['mobile'];

        $data['cps_app_id'] = $this->data['app_id'];

        $data['reg_type'] = $this->data['reg_type'] ? $this->data['reg_type'] : 1;

        $data['reg_time'] = $this->data['reg_time'] ? $this->data['reg_time'] : time();

        $data['reg_ip'] = $this->data['reg_ip'];



        if(isset($this->data['ext'])){

            $data['ext'] = $this->data['ext'];

        }

        $data['account']=1;
        $time=strtotime(date('Y-m-d'));
        if(false){//$member['t']+3*3600*24>=$time){
            if($member['gid'] == 2){
                $ref='ref1_id';
            }elseif($member['gid'] == 3){
                $ref='ref2_id';
            }
            $ret=$this->account($ref,$ref_id);
			$data['account'] = $ret['account'];
        }

        $id = M('register_log')->data($data)->add();

        if(!$id){

            echo $this->formatResponse(array('ret' => '30', 'msg' => '错误', 'data' => $ret));

            return ;

        }



        echo $this->formatResponse(array('ret' => '00', 'msg' => '成功', 'data' => $ret));

    }

    /**

     * 添加充值记录

     */

    public function pay()
    {exit;

        $prefix = C('DB_PREFIX');

        $data = array();
        $count = M('pay_log')->where("uid='{$this->data['openid']}' AND cps_app_id='{$this->data['app_id']}' AND pay_serial='{$this->data['pay_serial']}'")->count();
        if($count > 0){
            echo $this->formatResponse(array('ret' => '10', 'msg' => '重复请求'));
            return ;
        }

        $ref_id = intval($this->data['refid']);

        $member = M('member')->field('user,cid,g.uid,g.group_id AS gid')

            ->join("{$prefix}auth_group_access g ON g.uid={$prefix}member.uid", 'LEFT')

            ->where("{$prefix}member.uid={$ref_id}")

            ->find();

        if(!$ref_id || empty($member) || empty($member['gid']) || $member['gid'] == 1){
            $data['account'] = 0;
            $data['ref1_id'] = $ref_id;
            $data['ext'] = $ref_id;

        } elseif($member['gid'] == 2) {
            $data['ref1_id'] = $ref_id;

            $data['ref1_name'] = $member['user'];

        } elseif($member['gid'] == 3) {

            $parent = M('member')->where('uid='.$member['cid'])->find();

            $data['ref1_id'] = $parent['uid'];

            $data['ref1_name'] = $parent['user'];

            $data['ref2_id'] = $ref_id;

            $data['ref2_name'] = $member['user'];

        }
        if($this->data['uid']){
            $reg_log = M('register_log')->field('id,account')->where("openid='" . $this->data['openid'] . "'")->find();
            if($reg_log && !empty($reg_log['account'])){
                $data['account'] = 1;
            } else {
                $data['account'] = 0;
            }
        } else {
            $data['account'] = 0;
        }


        $data['uid'] = $this->data['openid'];

        $data['username'] = $this->data['username'];

        $data['cps_app_id'] = $this->data['app_id'];

        $data['pay_serial'] = $this->data['pay_serial'];

        $data['pay_channel_serial'] = $this->data['pay_channel_serial'];

        $data['pay_channel'] = $this->data['pay_channel'];

        $data['pay_amount'] = $this->data['pay_amount'];

        $data['pay_type'] = $this->data['pay_type'];

        $data['pay_time'] = $this->data['pay_time'];

        $data['pay_ip'] = $this->data['pay_ip'];
		$data['account'] = 1;


        if(isset($this->data['ext'])){

            $data['ext'] = $this->data['ext'];

        }

Think\Log::record(json_encode($data),'DEBUG',true);

        $id = M('pay_log')->data($data)->add();

        if(!$id){

            echo $this->formatResponse(array('ret' => '30', 'msg' => '错误'));

            return ;

        }

        echo $this->formatResponse(array('ret' => '00', 'msg' => '成功'));

    }
    //获取威富通支付域名
    public function checkWftPayUrl()
    {
        $url = M('setting')->where('k="wftpayurl"')->getField('v');
        echo $this->formatResponse(['ret' => '00', 'msg' => '成功', 'url' => $url]);
    }
    /**
     * @获取使用的跳转域名列表
     */
    public function getSkipUrl()
    {
        $domains = M('domain')->field("*")
            ->where("look=0 AND type=0")
            ->order('id desc')
            ->select();

        $res = [];
        foreach($domains as $val){
            $res[] = ['id' => $val['id'], 'url' => $val['url']];
        }

        echo $this->formatResponse(['ret' => '00', 'msg' => '成功', 'data' => $res]);
    }

    //检查充值是否正常
    public function checkpay()
    {
        $last_time = M('pay_log')->field("pay_time")
            ->order('pay_time desc')
            ->find();
        $last10_time = M('pay_log')->field("pay_time")
            ->order('pay_time desc')
            ->limit(10,1)
            ->select();
        $lost_time=time()-$last_time['pay_time'];
        $interval=$last_time['pay_time']-$last10_time[0]['pay_time'];
        $interval=$interval?$interval:1;

        $payck = M('setting')->field("v")
            ->where("k = 'payck'")
            ->find();
        $data['interval']=$interval;
        $data['lost_time']=$lost_time;
        $data['last_time']=$last_time['pay_time'];
        $data['last10_time']=$last10_time[0]['pay_time'];
        $data['payck']=ceil($payck['v'])*60;
        if($interval<$lost_time&&$lost_time>ceil($payck['v'])*60){
            //$info = M('pay_wechat')->where('status=3')->find();
            //M('pay_wechat')->where('status=1')->setField('status','2');
            //M('pay_wechat')->where('id='.$info['id'])->setField('status','1');
            //$data['api_url'] = empty($info)?null:$info['api_url'];
            echo $this->formatResponse(['ret' => '00', 'msg' => '充值故障', 'data' => $data]);
        }else{
            echo $this->formatResponse(['ret' => '30', 'msg' => '充值正常', 'data' => $data]);
        }
    }

    /**
     * @获取使用的跳转域名列表
     */
    public function getPayUrl()
    {
        $web_id = 1;
        $test_pay = M('pay_wechat')->where('status=1 AND web_id='.$web_id)->find();

        $url = "http://{$test_pay['api_url']}/PayApi/link";

        echo $this->formatResponse(['ret' => '00', 'msg' => '成功', 'data' => $url]);
    }

    /**
     * @获取可用的跳转域名列表
     */
    public function getValidUrl()
    {
        $app_id = $this->data['app_id'];
        $ad_config = C('cps_api');
        $ref_id = $ad_config[$app_id]['ref_id'];
        $g_zid = $ad_config[$app_id]['g_zid'];

        $packet_id = M('member')->where("uid={$ref_id}")->getField('packet_id');
        if(empty($packet_id)){
            echo $this->formatResponse(['ret' => '10', 'msg' => '未分配']);
            exit;
        }

        $d_where = 'look=0 AND type=0';
        $domain_arr = M('packet')->where("id={$packet_id}")->getField('wechart_id');
        if(!empty(unserialize($domain_arr))) {
            $d_where .= " AND id IN (" . implode(',', unserialize($domain_arr)) . ")";
        }

        $domains = M('domain')->field("*")
            ->where($d_where)
            ->order('id desc')
            ->select();

        $count = count($domains);
        if($count < 5){
            $limit = 5 - $count;
            $d_where = 'look=0 AND type=0';
            if(!empty(unserialize($domain_arr))) {
                $d_where .= " AND id NOT IN (" . implode(',', unserialize($domain_arr)) . ")";
            }
            $add = M('domain')->field("*")
                ->where($d_where)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }

        $domains = array_merge($add, $domains);
        $res = [];
        foreach($domains as $val){
            $res[] = ['id' => $val['id'], 'url' => "http://".$val['url']."/index/index/link?refid={$ref_id}&gzid={$g_zid}"];
        }

        echo $this->formatResponse(['ret' => '00', 'msg' => '成功', 'data' => $res]);
    }

    /**
     * @获取跳转域名
     */
    public function getJumpDomain()
    {
        $domains = M('domain')->where("type=2 AND look=0")->select();

        $res = [];
        foreach($domains as $val){
            $res[] = 'http://'.$val['url'].'/'.date('YmdHis').'/'.I('get.uid').'.gif';
        }

        echo $this->formatResponse(array('ret' => '00', 'msg' => '成功', 'data'=> $res));
    }

    /**
     * @更新跳转域名
     */
    public function updateJumpDomain()
    {
        if($this->data['lasttime'] > time()){
            echo $this->formatResponse(array('ret' => '10', 'msg' => '失败'));
            //return ;
        }
//echo C('ENTRANCE_API_URL');die;
        $domains = curl_get(C('ENTRANCE_API_URL'));
        $domains = json_decode($domains, true);//print_r($domains);die;
        if(count($domains) < 1){
            echo $this->formatResponse(array('ret' => '30', 'msg' => '无域名'));
            //return ;
        }

        //弃用之前的
        M('domain')->where("type=2 AND look<>3")->data(array('look'=> 3))->save();

        foreach($domains as $val){
            $o_domain = M('domain')->where("type=2 AND url='{$val}'")->find();
            if($o_domain['id']){
                M('domain')->where("id={$o_domain['id']}")->data(array('look'=> 0))->save();
            } else {
                M('domain')->data(array('look'=> 0,'type'=> 2, 'url'=>$val))->add();
            }
        }

        return $this->formatResponse(array('ret' => '00', 'msg' => '成功'));
    }

    /**
     * @获取域名
     * layer=1 微信群
     * layer=4 广告
     */
    public function updatemoreDomain($layer)
    {//echo C('MOIVE_API_URL');die;
        $domains = curl_get(C('MOIVE_API_URL').$layer);		
        $domains = json_decode($domains, true);

        $type = ['1'=>3,'2'=>0,'4'=>4];
        //弃用之前的
        M('domain')->where("type={$type[$layer]} AND look<>3")->data(array('look'=> 3))->save();

        foreach($domains as $val){
            $o_domain = M('domain')->where("url='{$val}'and type = ".$type[$layer])->find();
            if($o_domain['id']){
                M('domain')->where("id={$o_domain['id']}")->data(array('look'=> 0))->save();
            } else {
                M('domain')->data(array('look'=> 0,'type'=>$type[$layer], 'url'=>$val))->add();
            }
        }

        return $this->formatResponse(array('ret' => '00', 'msg' => '成功'));
    }

    /**
     * @获取订单信息
     */
    public function getPayInfo()
    {
        if(empty($this->data['ad_app_id']) || empty($this->data['ad_app_uid'])){
            echo $this->formatResponse(array('ret' => '10', 'msg' => '参数错误'));
            return ;
        }
        $prefix = C('DB_PREFIX');
        $where = '1=1';
        $start_time = empty($this->data['start_time']) ? 0 : strtotime($this->data['start_time']);
        $end_time = empty($this->data['end_time']) ? 0 : strtotime($this->data['end_time']);
        if (!empty($start_time) && !empty($end_time)) {
            $where .=" AND {$prefix}pay_log.pay_time >= $start_time AND {$prefix}pay_log.pay_time <= $end_time";
        } elseif (!empty($start_time)) {
            $where .=" AND {$prefix}pay_log.pay_time >= $start_time ";
        } elseif (!empty($end_time)) {
            $where .=" AND {$prefix}pay_log.pay_time <= $end_time ";
        }
        $where .= " AND ad_app_id={$this->data['ad_app_id']} AND ad_app_uid={$this->data['ad_app_uid']}";

        $pay_log = M('pay_log')->field("*")
            ->where($where)
            ->order('id desc')
            ->select();

        $res = [];
        foreach($pay_log as $val){
            $res[] = [
                'id' => $val['id'],
                'pay_amount' => $val['pay_amount'],
                'pay_time' => $val['pay_time'],
                'ad_app_id' => $val['ad_app_id'],
                'ad_app_uid' => $val['ad_app_uid'],
            ];
        }

        echo $this->formatResponse(['ret' => '00', 'msg' => '成功', 'data' => $res]);
    }


    /**
     * 校验签名
     * @param  array $data
     * @return bool
     */
    protected function checkSign($data)
    {
        $Tg = C('cps_api');
        $app_id = $data['app_id'];
        $data['app_key']  = $app_key = $Tg[$app_id]['app_key'];

        $sign = $this->getSign($data);

        if(empty($app_id) || empty($app_key) || $data['sign'] != $sign){
            return false;
        }

        return true;
    }

    /**
     * 签名
     * @param array $data
     * @return string $sign
     */
    protected function getSign($data)
    {
        $sign_arr = [];
        ksort($data);
        foreach($data as $key=>$val){
            if($val != '' && $key != 'ext' && $key != 'sign' && $key != 'm'){//空值字段不参与签名
                $sign_arr[] = $key . '=' . $val;
            }
        }

        $sign = md5(implode('&',$sign_arr));//签名

        return $sign;
    }

    /**
     * 格式化响应信息
     * @param $param array 返回的信息数组
     * @return json
     */
    protected function formatResponse($param, $type = 'json')
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

    protected function account($ref,$refid){
        $time=strtotime(date('Y-m-d'));
        $res = [];
        $account1=M('register_log')->where("{$ref}={$refid} and reg_time>={$time} and account=1")->count();
        if($account1==0){Think\Log::record('bili--' . $refid . ':account=0','DEBUG',true);
            $res = ['account' => 1, 'proportion' => '0 / 0'];
            return $res;
        }
        $total=M('register_log')->where("{$ref}={$refid} and reg_time>={$time}")->count();

        $per=M('setting')->where("k='Proportion'")->getField('v');
        if(empty($per)){
            $per=1;
        }
        $proportion = 'bili--' . $refid . " : " . $account1 . " / " . $total . ' = ' . round($account1/$total, 3);
        Think\Log::record($proportion,'DEBUG',true);
        if($account1/$total>floatval($per)){
            $res['account'] = 0;
        }else{
            $res['account'] = 1;
        }

        return $res;
    }
}

