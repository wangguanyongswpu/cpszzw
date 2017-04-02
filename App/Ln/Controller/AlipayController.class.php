<?php
/**
 * 日    期：2016-09-01
 * 版    本：1.0.0
 * 功能说明：支付宝支付
 **/
namespace Ln\Controller;
use Common\Controller\BaseController;
use Think;

class AlipayController extends BaseController
{
    /**
     *  alipay-确认订单
     */
    public function index(){
        //暂未启用,跳转至默认充值
        header("Location: ".'http://'.$_SERVER['HTTP_HOST'].'/PayApi/link?'.$_SERVER['QUERY_STRING']);
        exit;

        $data = I('get.');
        if(empty($data['orderid'])){
            $data['orderid'] = "v".time().rand(10000,999999);
        }
        $data['money'] = 48;

        $order = M("payapi_log")->where(array("orderid"=>$data['orderid']))->find();
        if($order['id'] && $order['status'] == 1){
            $this->error("已支付");
        }

        if(empty($order['id'])){
            $payapi_log = array(
                'prepayid'    => $data['orderid'],
                'orderid'     => $data['orderid'],
                'pay_orderid' => $data['orderid'],
                'openid'      =>  time(),
                'uid'         =>  'cps',
                'refid'       =>  $data['refid'],
                'ad_app_id'   =>  $data['ad_app_id'],
                'ad_app_uid'  =>  $data['ad_app_uid'],
                'gid'         =>  '1',
                'time'        =>  time(),
                'status'      =>  0,
                'money'       =>  $data["money"],
                'ip'          =>  get_iplong(),
                'call_url'    =>  'alipay',
            );
            $id = M("payapi_log")->add($payapi_log);
            if(!$id){
                $this->error("支付发起错误!");
            }

            $order['id'] = $id;
            $register_log = [
                'refid' => $data['refid'],
                'openid' => time(),
                'sex' => 1,
                'cps_app_id' => '1000000001',
                'reg_type' => 1,
                'reg_time' => time(),
                'reg_ip' => get_iplong(),
            ];
            $this->add_register($register_log);
        }

        $data['id'] = $order['id'];
        $data['type'] = 1;
        $data['label'] = '支付订单';
        $this->assign('order_info',$data);
        $this->display('PayApi:alipay');
    }

    /**
     * alipay
     */
    public function alipay_pay()
    {
        vendor('Alipay.Alipay');

        $id = I('get.id',0);
        $order = M("payapi_log")->where(array("id"=>$id))->find();
        if(empty($order) || $order['status'] == 1){
            $this->error('错误请求');
        }

        $data = array(
            'out_trade_no' => $order['orderid'],
            'subject' => 'vip服务',
            'total_fee' => $order['money'],
        );

        $alipay = new \Alipay();

        echo $alipay->pay($data);
    }

    /**
     * alipay 同步返回页面
     */
    public function alipay_return()
    {
        vendor('Alipay.Alipay');
        $alipay = new \Alipay();
        if(!$alipay->verifyReturn()){
            \Think\Log::record('alipay return verify fail:'.json_encode($_GET),'DEBUG', true);
            $this->error("验证失败");
        }

        $data = I('get.');
        if($data['trade_status'] == 'TRADE_FINISHED' || $data['trade_status'] == 'TRADE_SUCCESS') {
            $ret['label'] = '支付成功';
        } else {
            $ret['label'] = '支付失败';
        }

        $this->assign('order_info', $ret);

        $this->display('PayApi:alipay');
    }

    /**
     * alipay 异步回调
     */
    public function alipay_notify()
    {
        vendor('Alipay.Alipay');
        $alipay = new \Alipay();
        if(!$alipay->verifyNotify()){
            \Think\Log::record('alipay notify verify fail:'.json_encode($_GET),'DEBUG', true);
            echo 'fail';
            exit;
        }
        $data = I('get.');
        if($data['trade_status'] == 'TRADE_FINISHED') {
            \Think\Log::record('alipay pay success:'.json_encode($_GET),'DEBUG', true);
            $order = M("payapi_log")->where(array("orderid"=>$data['out_trade_no'], 'status' => 0))->find();
            $_order = array(
                'pay_time' => strtotime($data['gmt_payment']),
                'status' => 1,
                'transaction_id' => $data['trade_no'],
            );

            if( M("payapi_log")->where(array("id"=>$order['id']) )->save($_order) !== false){
                \Think\Log::record("order: ".json_encode($order),'DEBUG',true);
                $this->add_pay($order['id']);
            }

            $ret['label'] = '支付成功';
        } else {
            \Think\Log::record('alipay pay fail:'.json_encode($_GET),'DEBUG', true);
            $ret['label'] = '支付失败';
        }

        echo 'success';
    }

    /**
     * 创建付款记录
     * @param $id integer 订单id
     * @return bool
     */
    private function add_pay($id)
    {
        $prefix = C('DB_PREFIX');
        $order= M("payapi_log")->where(array("id"=>$id))->find();
        $count = M('pay_log')->where("cps_app_id='1000000001' AND pay_serial='{$order['orderid']}'")->count();
        if($count > 0){
            return false;
        }
        $member = [];
        if($order['refid']) {
            $member = M('member')->field('user,cid,g.uid,g.group_id AS gid,t')
                ->join("{$prefix}auth_group_access g ON g.uid={$prefix}member.uid", 'LEFT')
                ->where("{$prefix}member.uid={$order['refid']}")
                ->find();
        }

        $data['account'] = 1;
        $ref = 'ref1_id';
        if(empty($member) || empty($member['gid']) || $member['gid'] == 1){
            $data['account'] = -1;
            $data['ref1_id'] = $order['refid'];
            $data['ext'] = $order['refid'];
        } elseif($member['gid'] == 2) {
            $data['ref1_id'] = $order['refid'];
            $data['ref1_name'] = $member['user'];
        } elseif($member['gid'] == 3) {
            $ref = 'ref2_id';
            $parent = M('member')->where('uid='.$member['cid'])->find();
            $data['ref1_id'] = $parent['uid'];
            $data['ref1_name'] = $parent['user'];
            $data['ref2_id'] = $order['refid'];
            $data['ref2_name'] = $member['user'];

        }

        \Think\Log::record('bili:' . $data['account'] . "--{$order['ad_app_id']} -- " .date("Y-m-d H:i:s", $member['t']), 'DEBUG', true);
        //扣量
        if($data['account']!=-1 && !$order['ad_app_id'] && $member['t']+3*3600*24<=time()){
            \Think\Log::record('bili-t:' . $member['t'], 'DEBUG', true);
            if($this->get_rand(array('ref' => $ref,'refid' => $order['refid']))){
                $data['account'] = 0;
            } else {
                $data['account'] = 1;
            }
            \Think\Log::record('bili-a:' . $data['account'], 'DEBUG', true);
        }

        $data['uid'] = $order['pay_orderid'] ? $order['pay_orderid'] : 0;
        $data['username'] = '';
        $data['cps_app_id'] = '1000000001';
        $data['pay_serial'] = $order['orderid'];
        $data['ad_app_id'] = $order['ad_app_id'];  //username
        $data['ad_app_uid'] = $order['ad_app_uid'];	//other
        $data['pay_channel_serial'] = $order['transaction_id'];
        $data['pay_channel'] = 1;
        $data['pay_amount'] = $order['money'];
        $data['pay_type'] = 1;
        $data['pay_time'] = $order['pay_time'];
        $data['pay_ip'] = $order['ip'];

        $data['ref1_id'] =isset($data['ref1_id'])?$data['ref1_id']:0;
        $data['ref2_id'] =isset($data['ref2_id'])?$data['ref2_id']:0;

        \Think\Log::record(json_encode($data),'DEBUG',true);
//Think\Log::record("test_pay_data:".json_encode($order),'DEBUG',true);
        $pay_log=M('pay_log')->add($data);

        return true;
    }

    /**
     * 创建注册记录
     * @param $param array
     * @return bool
     */
    private function add_register($param)
    {
        $prefix = C('DB_PREFIX');
        $count = M('register_log')->where("openid='{$param['openid']}' AND cps_app_id='{$param['cps_app_id']}'")->count();
        if($count > 0){
            return false;
        }
        $member = [];
        if($param['refid']) {
            $member = M('member')->field('user,cid,g.uid,g.group_id AS gid')
                ->join("{$prefix}auth_group_access g ON g.uid={$prefix}member.uid", 'LEFT')
                ->where("{$prefix}member.uid={$param['refid']}")
                ->find();
        }

        if(empty($member) || empty($member['gid']) || $member['gid'] == 1){
            $data['account'] = 0;
            $data['ref1_id'] = $param['refid'];
            $data['ext'] = $param['refid'];
        } elseif($member['gid'] == 2) {
            $data['ref1_id'] = $param['refid'];
            $data['ref1_name'] = $member['user'];
        } elseif($member['gid'] == 3) {
            $parent = M('member')->where('uid='.$member['cid'])->find();
            $data['ref1_id'] = $parent['uid'];
            $data['ref1_name'] = $parent['user'];
            $data['ref2_id'] = $param['refid'];
            $data['ref2_name'] = $member['user'];

        }

        $data['account'] = 1;
        if(mt_rand(1,100)<80) $data['account'] = 0;

        $data['username'] = '';
        $data['uid'] = empty($param['uid'])?time()-1472000000:$param['uid'];
        $data['sex'] = $param['sex'];
        $data['openid'] =  $param['openid'];
        $data['cps_app_id'] = $param['cps_app_id'];
        $data['reg_type'] = $param['reg_type'] ? $param['reg_type'] : 1;
        $data['reg_time'] = $param['reg_time'] ? $param['reg_time'] : time();
        $data['reg_ip'] = $param['reg_ip'];
        $data['ref1_id'] =isset($data['ref1_id'])?$data['ref1_id']:0;
        Think\Log::record("reg--pay-data: ".json_encode($data),'DEBUG',true);

        //保证注册不比充值少
        if($data['ref2_id']){
            $today = strtotime(date("Y-m-d 0:0:0"));
            $regcount = M('register_log')->where("ref2_id='{$data['ref2_id']}' AND reg_time>{$today} and account=1")->count();
            $paycount = M('pay_log')->where("ref2_id='{$data['ref2_id']}' AND pay_time>{$today} and account=1")->count();
            Think\Log::record("reg--pay2-{$data['ref2_id']}: reg:".$regcount.' pay:'.$paycount,'DEBUG',true);

            //订单量/已付款的>5,则此条注册无效
            //随机一个注册比
            $randrate = mt_rand(3500,5500)/1000;
            Think\Log::record("reg--pay2-{$data['ref2_id']} randrate: ".$randrate,'DEBUG',true);
            $nowrate = $paycount?$regcount/$paycount:0;
            Think\Log::record("reg--pay2-{$data['ref2_id']} nowrate: ".$nowrate,'DEBUG',true);
            if($paycount && $regcount && $nowrate>$randrate){
                $data['account'] = 0;
                Think\Log::record("reg--pay2-{$data['ref2_id']} randrate account set to 0",'DEBUG',true);
            }
            //当天没有注册的不扣
            if(!$regcount) $data['account'] = 1;
            //有注册没有充值的扣掉
            if(!$paycount && $regcount>mt_rand(3,8)){
                Think\Log::record("reg--pay2-{$data['ref2_id']} pay is 0 account set to 0",'DEBUG',true);
                $data['account'] = 0;
            }

            if($paycount>$regcount){
                $dao = M();
                $c2update = $paycount-$regcount+mt_rand(1,3);
                $sql = "update qw_register_log set account=1 where account=0 and ref2_id='{$data['ref2_id']}' AND reg_time>{$today} limit {$c2update}";
                Think\Log::record("reg--pay2-{$data['ref2_id']} sql: ".$sql,'DEBUG',true);
                $dao->execute($sql);
            }
        }elseif($data['ref1_id']){
            $today = strtotime(date("Y-m-d 0:0:0"));
            $regcount = M('register_log')->where("ref1_id='{$data['ref1_id']}' AND reg_time>{$today} and account=1")->count();
            $paycount = M('pay_log')->where("ref1_id='{$data['ref1_id']}' AND pay_time>{$today} and account>=1")->count();
            Think\Log::record("reg--pay1-{$data['ref1_id']}: reg:".$regcount.' pay:'.$paycount,'DEBUG',true);

            //订单量/已付款的>5,则此条注册无效
            //随机一个注册比
            $randrate = mt_rand(3500,5500)/1000;
            Think\Log::record("reg--pay1-{$data['ref1_id']} randrate: ".$randrate,'DEBUG',true);
            $nowrate = $paycount?$regcount/$paycount:0;
            Think\Log::record("reg--pay1-{$data['ref1_id']} nowrate: ".$nowrate,'DEBUG',true);
            if($paycount && $regcount && $nowrate>$randrate){
                $data['account'] = 0;
                Think\Log::record("reg--pay1-{$data['ref1_id']} randrate account set to 0",'DEBUG',true);
            }
            //当天没有注册的不扣
            if(!$regcount) $data['account'] = 1;
            //有注册没有充值的扣掉
            if(!$paycount && $regcount>mt_rand(3,8)){
                Think\Log::record("reg--pay1-{$data['ref1_id']} pay is 0 account set to 0",'DEBUG',true);
                $data['account'] = 0;
            }

            if($paycount>$regcount){
                $dao = M();
                $c2update = $paycount-$regcount+mt_rand(1,3);
                $sql = "update qw_register_log set account=1 where account=0 and ref1_id='{$data['ref1_id']}' AND ref2_id=0 AND reg_time>{$today} limit {$c2update}";
                Think\Log::record("reg--pay1-{$data['ref1_id']} sql: ".$sql,'DEBUG',true);
                $dao->execute($sql);
            }
        }

        M('register_log')->data($data)->add();

        return true;
    }

    /*
     * GET传参
     * */
    private function httpGet($url) {
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

    /*
     * POST 传参
    */

    private function  httppost($url,$data){
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        \Think\Log::record(" http post: url: ".$url.' data : '.$data.' ret: '.$return,'DEBUG',true);
        return $return;
    }



    /**
     * 概率计算 10万次计算 相差+-300
     */
    private function get_rand($data) {
        $per=M('setting')->where("k='Proportion'")->getField('v');
        $per_arr = [
            '0' => 100 - $per,
            '1' => $per
        ];

        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($per_arr);

        //概率数组循环
        foreach ($per_arr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($per_arr);

        $time = strtotime(date("Y-m-d"));
        $account1=M('pay_log')->where("{$data['ref']}={$data['refid']} and pay_time>={$time} and account=1")->count();
        $total=M('pay_log')->where("{$data['ref']}={$data['refid']} and pay_time>={$time}")->count();
        if($total) {
            \Think\Log::record("bili-account:{$account1} / {$total} = " . floatval($account1 / $total) . "--{$result}", 'DEBUG', true);
        }

        return $result;
    }

    protected function account($ref,$refid){
        $time=strtotime(date('Y-m-d'));
        $account1=M('pay_log')->where("{$ref}={$refid} and pay_time>={$time} and account=1")->count();
        if($account1==0){
            return false;
        }
        $total=M('pay_log')->where("{$ref}={$refid} and pay_time>={$time}")->count();

        $per=M('setting')->where("k='Proportion'")->getField('v');
        if(empty($per)){
            $per=100;
        }
        $proportion = 'bili--' . $refid . " : " . $account1 . " / " . $total . ' = ' . round($account1/$total, 3);
        \Think\Log::record($proportion,'DEBUG',true);
        if($account1/$total>floatval(1 - $per/100)){
            return true;
        }

        return false;
    }
}
