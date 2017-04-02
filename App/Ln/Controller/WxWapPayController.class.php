<?php
/**
 * 日    期：2016-07-21
 * 版    本：1.0.0
 * 功能说明：微信支付接口
 **/
namespace Ln\Controller;
use Think;
use Common\Controller\BaseController;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

class WxWapPayController extends BaseController
{
    protected $data;
    protected $WEIXIN_PAY_API;
    protected $options;
    protected $OrderInfo; //订单信息
    protected $money;  //价格
    protected $appid;  //appid
    protected $rurl;   //第3方回调地址
    protected $openid; //会员openid
    protected $uid;    //会员id
    protected $pay_openid; //支付会员openid
    protected $paymentCallbackURl; //微信回调地址

    public function _initialize()
    {
        $this->data = I('get.');
        Think\Log::record(json_encode($this->data),'DEBUG',true);
        $get = session("get");
        if ($get) {
            $this->data = array_merge($this->data,$get);
        }
        /*if(!in_array(ACTION_NAME, ['link','postpayinfo','paybank','bankcallback']) && !$this->checkSign()){
            $this->error("签名错误!");
            echo $this->formatResponse(array('ret' => '20', 'msg' => '签名错误！'));
            exit;
        }else{*/

            session("get",$this->data);
        //}


        $this->data['money'] = 48;

        $source = C('cps_api');
        $web_id = $source[$this->data['app_id']]['web_id'];
        empty($web_id) && $web_id = 1;
        $test_pay = M('pay_wechat')->where('status=1 AND web_id='.$web_id)->find();
        $this->WEIXIN_PAY_API = C("WEIXIN_PAY_API");
        if($test_pay['id']){
            $this->WEIXIN_PAY_API = [
                'appid' => $test_pay['app_id'],
                'secret' => $test_pay['secret'],
                'mchid' => $test_pay['merchant_id'],
                'serve' => $test_pay['key'],
            ];
        }
        //测试支付接口
        if($this->data['test']){
            $id=$this->data['test'];
            $online_pay = M('pay_wechat')->where("id=$id")->find();
            $code=md5($id."+".$online_pay['api_url']."test");
            if($this->data['tokencode']==$code){
                $this->WEIXIN_PAY_API = [
                    'appid' => $online_pay['app_id'],
                    'secret' => $online_pay['secret'],
                    'mchid' => $online_pay['merchant_id'],
                    'serve' => $online_pay['key'],
                ];
                //$this->data['money'] = 0.01;
            }
        }
        $this->options        = array(
            'app_id'         => $this->WEIXIN_PAY_API['appid'],
            'secret'         => $this->WEIXIN_PAY_API['secret'],
            'payment'        => array(
                'merchant_id'        => $this->WEIXIN_PAY_API['mchid'],
                'key'                => $this->WEIXIN_PAY_API['serve'],
            ),
        );
        $this->OrderInfo      = array(
            'name'          => 'vip服务',
            'detail'        => 'vip服务',
            'serial'        =>  time().rand(1000,9999), //订单号
            'total_fee'     =>  $this->data['money'],                  //价格
        );
        $this->openid         = $this->data['openid'];
        $this->rurl           = $this->data['rurl'];
        $this->uid            = $this->data['uid'];
        $this->money          = $this->data['money'];
        $this->appid          = $this->data['appid'];
        $this->paymentCallbackURl = C("paymentCallbackURl");
    }


    /**
     * 校验签名
     * @return bool
     */
    protected function checkSign()
    {
        if (ACTION_NAME == 'callback') {
            return true;
        }
        $Tg = C('cps_api');
        $app_id  = $this->data['app_id'];
        $app_key = $Tg[$app_id]['app_key'];
        $sign = md5($app_id . '+' . $this->data['uid'] . '+' . $this->data['refid'] . '+' . $this->data['timestamp'] . '+' . $app_key);//签名规则
        if($this->data['sign'] !== $sign){
            return false;
        }
        return true;
    }


    /**
     * 格式化响应信息
     * @param $param array 返回的信息数组
     * @return json
     */
    protected function formatResponse($param)
    {
        $res = json_encode($param);
        return $res;
    }

  
    /**
     * 其他支付
     *
     *
     */
    public function paybank()
    {
		//UC不能开启加速功能，数据会丢失

		Think\Log::record("bank_start: ".json_encode($_GET),'DEBUG',true);
        //if(empty($this->data['orderid'])){
            $this->data['orderid'] = "v".time().rand(10000,999999);
        //}
        if( M("payapi_log")->where(array("orderid"=>$this->data['orderid'],'status'=>1))->find() ){
            $this->error("已支付");
        }

        $notify_url=C("paybankCallbackURl");//异步地址
        $return_url = 'http://www.drxwh.com/wap.wumaiqi.com/9dv12/daog.html';  //通知地址
        $pay_amt=$this->money;
        //测试用金额
        //$pay_amt='0.01'; //金额
		$pay_code="";
        $agent_id=C('AGENT_ID');
        $sign_key=C('SIGN_KEY');
        $agent_bill_id=$this->data['orderid'];//订单编号
        $pay_type = '20';//银行支付
		$pay_type = '30';//微信支付
        $goods_name="会员充值"; //商品名
        $goods_num='1';//商品数量
        $remark=time();//自定义信息

		$wxpay_type = 1;//0:微信扫码支付;1:微信WAP支付;2:微信公众号支付
		
        $extra_return_param="";//回传参数（name^zhangsan|sex^male）
        $payapi_log = array(
            'orderid'     => $agent_bill_id,
            'pay_orderid' => $agent_bill_id,
            'openid'      =>  time(),
            'uid'         =>  'bank',
            'refid'       =>  $this->data['refid'],
            'ad_app_id'   =>  $this->data['ad_app_id'],
            'ad_app_uid'  =>  $this->data['ad_app_uid'],
            'gid'         =>  '1',
            'time'        =>  time(),
            'status'      =>  0,
            'money'       => $pay_amt,
            'ip'          =>  get_iplong(),
            'call_url'    => $notify_url,
        );
		Think\Log::record("bank_log: ".json_encode($payapi_log),'DEBUG',true);
        if( M("payapi_log")->add($payapi_log) == false){
            $this->error("支付发起错误!");
        }
        include './PayDemo/post_action.php';
    }

    /**
     *  其他支付异步回调
     */
    public function bankcallback(){

        Think\Log::record("bankcallback: ".json_encode($_GET),'DEBUG',true);

        $result=$_GET['result'];
        $pay_message=$_GET['pay_message'];
        $agent_id=$_GET['agent_id'];
        $jnet_bill_no=$_GET['jnet_bill_no'];
        $agent_bill_id=$_GET['agent_bill_id'];
        $pay_type=$_GET['pay_type'];
        $pay_amt=$_GET['pay_amt'];
        $remark=$_GET['remark'];
        $returnSign=$_GET['sign'];
        //商户的KEY
        $key = C('SIGN_KEY');

        $signStr='';
        $signStr  = $signStr . 'result=' . $result;
        $signStr  = $signStr . '&agent_id=' . $agent_id;
        $signStr  = $signStr . '&jnet_bill_no=' . $jnet_bill_no;
        $signStr  = $signStr . '&agent_bill_id=' . $agent_bill_id;
        $signStr  = $signStr . '&pay_type=' . $pay_type;
        $signStr  = $signStr . '&pay_amt=' . $pay_amt;
        $signStr  = $signStr .  '&remark=' . $remark;
        $signStr = $signStr . '&key=' . $key;
        //$sign='';
        $sign=md5($signStr);
        //请确保 notify.php 和 return.php 判断代码一致
        if($sign==$returnSign){

            $order= M("payapi_log")->where(array("orderid"=>$agent_bill_id,'status'=>0))->find();
            $order= M("payapi_log")->where(array("orderid"=>$agent_bill_id,'status'=>0))->find();
            if(!empty($order)&&$pay_amt==$order['money']){
                $_order['call_status']=1;
                if (!$pay_message) {
                    $_order['status'] = 1;
                } else {
                    $_order['status'] = 2;
                }
            }
			
            $_order['pay_time'] = $remark;
            $_order['transaction_id'] = $jnet_bill_no;
			
            if( M("payapi_log")->where(array("orderid"=>$agent_bill_id) )->save($_order) !== false){

                if(isset($order)&&$_order['status']==1) {
                    Think\Log::record("order: ".json_encode($order),'DEBUG',true);
                    $this->add_pay($order['id']);
                }
            }

            $response="ok";
        }else{
			Think\Log::record("bank_err: ".json_encode($pay_message),'DEBUG',true);
            $response="error";
        }

        echo $response;
    }


    /**
     *  支付API接口
     */
    public function index(){

        return $this->link();
        exit;
        $data["weixin_pay_api"] = $this->WEIXIN_PAY_API;
        if(empty($this->data['orderid'])){
            $this->data['orderid'] = "v".time().rand(10000,999999);
        }
        if( M("payapi_log")->where(array("orderid"=>$this->data['orderid'],'status'=>1))->find() ){
            $this->error("已支付");
        }
        $user_info = $this->Wexin_Get_Code();
        $open_id   =  $user_info['openid'];
        $data = array(
            "pay_openid"  => $open_id,
            'openid'      => $this->openid,
        );
        import("Lib/WeiXinPay/Autoload");
        $app        = new Application($this->options);
        $payment    = $app->payment;
        $orderInfo  = $this->OrderInfo;
        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => $orderInfo['name'],
            'detail'           => $orderInfo['detail'],
            'out_trade_no'     => $this->data['orderid'], //订单号
            'total_fee'        => intval($orderInfo['total_fee']*100),
            'notify_url'       => $this->paymentCallbackURl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $open_id,
        ];
        $order = new Order($attributes);
        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;
        }
        $data['js']= $app->js->config(array('onMenuShareQQ', 'onMenuShareWeibo','chooseWXPay'), false);
        $data['config'] = $payment->configForJSSDKPayment($prepayId);
        $data['order_info'] = array(
            'orderid'=>$this->data['orderid'],
            'money'  =>$orderInfo['total_fee'],
        );
        $payapi_log = array(
            'prepayid'    =>  $prepayId,
            'orderid'     =>  $this->data['orderid'],
            'pay_orderid' =>  $open_id,
            'openid'      =>  time(),
            'uid'         =>  'cps',
            'refid'       =>  $this->data['refid'],
            'gid'         =>  '1',
            'time'        =>  time(),
            'status'      =>  0,
            'money'       =>  $this->data["money"],
            'ip'          =>  get_iplong(),
            'call_url'    =>  'ceshi',
        );
        if( M("payapi_log")->add($payapi_log) == false){
            $this->error("支付发起错误!");
        }
        $register_log = [
            'refid' => $this->data['refid'],
            'openid' => $open_id,
            'sex' => 1,
            'cps_app_id' => '1000000001',
            'reg_type' => 1,
            'reg_time' => time(),
            'reg_ip' => get_iplong(),
        ];
        $this->add_register($register_log);

        $data['call_url'] = $this->data['call_url'];
        session("get",null);
        $this->assign($data);
        $this->display('index');
    }

    /**
     *  支付API接口
     */
    public function link(){
        $data["weixin_pay_api"] = $this->WEIXIN_PAY_API;
        if(empty($this->data['orderid'])||$this->data['test']){
            $this->data['orderid'] = "v".time().rand(10000,999999);
        }
        if( M("payapi_log")->where(array("orderid"=>$this->data['orderid'],'status'=>1))->find() ){
            $this->error("已支付");
        }
        $user_info = $this->Wexin_Get_Code();
        $open_id   =  $user_info['openid'];
        $pay_log = M('pay_log')->where("uid='{$open_id}' AND pay_amount>1")->find();
        if($pay_log['id']&&$this->data['money']>1){
            $url = 'http://now.qq.com/h5/index.html?roomid=6628544&_bid=&_wv=&from=';
            header("Location:".$url);
            die();
        }
        $data = array(
            "pay_openid"  => $open_id,
            'openid'      => $this->openid,
        );
        import("Lib/WeiXinPay/Autoload");
        $app        = new Application($this->options);
        $payment    = $app->payment;
        $orderInfo  = $this->OrderInfo;
        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => $orderInfo['name'],
            'detail'           => $orderInfo['detail'],
            'out_trade_no'     => $this->data['orderid'], //订单号
            'total_fee'        => intval($orderInfo['total_fee']*100),
            'notify_url'       => $this->paymentCallbackURl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $open_id,
        ];
        $order = new Order($attributes);
        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;
        }
        $data['js']= $app->js->config(array('onMenuShareQQ', 'onMenuShareWeibo','chooseWXPay'), false);
        $data['config'] = $payment->configForJSSDKPayment($prepayId);
        $data['order_info'] = array(
            'orderid'=>$this->data['orderid'],
            'money'  =>$orderInfo['total_fee'],
        );
        $payapi_log = array(
            'prepayid'    =>  $prepayId,
            'orderid'     =>  $this->data['orderid'],
            'pay_orderid' =>  $open_id,
            'openid'      =>  time(),
            'uid'         =>  'cps',
            'refid'       =>  $this->data['refid'],
            'ad_app_id'   =>  $this->data['ad_app_id'],
            'ad_app_uid'  =>  $this->data['ad_app_uid'],
            'gid'         =>  '1',
            'time'        =>  time(),
            'status'      =>  0,
            'money'       =>  $this->data["money"],
            'ip'          =>  get_iplong(),
            'call_url'    =>  'ceshi',
        );
        if( M("payapi_log")->add($payapi_log) == false){
            $this->error("支付发起错误!");
        }
        $register_log = [
            'refid' => $this->data['refid'],
            'openid' => $open_id,
            'sex' => 1,
            'cps_app_id' => '1000000001',
            'reg_type' => 1,
            'reg_time' => time(),
            'reg_ip' => get_iplong(),
        ];
        $this->add_register($register_log);

        $data['call_url'] = $this->data['call_url'];
        session("get",null);
        $this->assign($data);
        $this->display('index');
    }


    /**
     *  支付API接口
     */
    public function wapWx(){
        if(empty($_GET['debug']) || $_GET['debug']!='6628544'){
            exit('wapwx');
        }
        $data["weixin_pay_api"] = $this->WEIXIN_PAY_API;
        if(empty($this->data['orderid'])||$this->data['test']){
            $this->data['orderid'] = "v".time().rand(10000,999999);
        }
        if( M("payapi_log")->where(array("orderid"=>$this->data['orderid'],'status'=>1))->find() ){
            $this->error("已支付");
        }
//        $user_info = $this->Wexin_Get_Code();
        $open_id   =  '';//$user_info['openid'];
        $pay_log = M('pay_log')->where("uid='{$open_id}' AND pay_amount>1")->find();
        if($pay_log['id']&&$this->data['money']>1){
            $url = 'http://now.qq.com/h5/index.html?roomid=6628544&_bid=&_wv=&from=';
            header("Location:".$url);
            die();
        }
        $data = array(
            "pay_openid"  => $open_id,
            'openid'      => $this->openid,
        );
        import("Lib/WeiXinPay/Autoload");
        $app        = new Application($this->options);
        $payment    = $app->payment;
        $orderInfo  = $this->OrderInfo;
        $attributes = [
            'trade_type'       => 'NATIVE', // JSAPI，NATIVE，APP...
            'body'             => $orderInfo['name'],
            'detail'           => $orderInfo['detail'],
            'out_trade_no'     => $this->data['orderid'], //订单号
            'total_fee'        => intval($orderInfo['total_fee']*100),
            'notify_url'       => $this->paymentCallbackURl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => $open_id,
        ];
        $order = new Order($attributes);
        $result = $app->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;
        }else{
            var_dump($result);
        }


        $js = json_decode($app->js->config(array('onMenuShareQQ', 'onMenuShareWeibo','chooseWXPay'), false));

//        var_dump($result);
//        var_dump($js);


        $url = "appid={$js->appId}&noncestr={$js->nonceStr}&package=WAP&prepayid={$prepayId}&sign={$js->signature}&timestamp={$js->timestamp}";
        echo "<a href='weixin://wap/pay?".urlencode($url)."'>微信WAP支付</a><br />";
        echo "<a href='weixin://dl/scan'>微信扫描</a><br />";
        echo "二维码支付:".$result->code_url.'<br />';
        exit;
        $data['config'] = $payment->configForJSSDKPayment($prepayId);
        $data['order_info'] = array(
            'orderid'=>$this->data['orderid'],
            'money'  =>$orderInfo['total_fee'],
        );
        $payapi_log = array(
            'prepayid'    =>  $prepayId,
            'orderid'     =>  $this->data['orderid'],
            'pay_orderid' =>  $open_id,
            'openid'      =>  time(),
            'uid'         =>  'cps',
            'refid'       =>  $this->data['refid'],
            'ad_app_id'   =>  $this->data['ad_app_id'],
            'ad_app_uid'  =>  $this->data['ad_app_uid'],
            'gid'         =>  '1',
            'time'        =>  time(),
            'status'      =>  0,
            'money'       =>  $this->data["money"],
            'ip'          =>  get_iplong(),
            'call_url'    =>  'ceshi',
        );
        if( M("payapi_log")->add($payapi_log) == false){
            $this->error("支付发起错误!");
        }
//        $register_log = [
//            'refid' => $this->data['refid'],
//            'openid' => $open_id,
//            'sex' => 1,
//            'cps_app_id' => '1000000001',
//            'reg_type' => 1,
//            'reg_time' => time(),
//            'reg_ip' => get_iplong(),
//        ];
//        $this->add_register($register_log);

        $data['call_url'] = $this->data['call_url'];
        session("get",null);
        $this->assign($data);
        //$this->display('wapwx');
    }

    /**
     *  支付回调
     */
    public function callback(){
        import("Lib/WeiXinPay/Autoload");
        // file_put_contents('./pay.log',date("ymd H:i:s").PHP_EOL.file_get_contents('php://input', 'r').PHP_EOL.json_encode($_REQUEST).PHP_EOL,FILE_APPEND);
        $options = $this->options;
        $app = new Application($options);
        $response = $app->payment->handleNotify(function($notify, $successful){
            $order= M("payapi_log")->where(array("orderid"=>$notify['out_trade_no'],'status'=>0))->find();
            $_order['pay_time'] = strtotime($notify['time_end']);
            $_order['transaction_id'] = $notify['transaction_id'];
            if ($notify['result_code'] =='SUCCESS') {
                $_order['status'] = 1;
            } else {
                $_order['status'] = 2;
            }
            if( M("payapi_log")->where(array("orderid"=>$notify['out_trade_no']) )->save($_order) !== false){

                if(isset($order)&&$_order['status']==1) {
                    Think\Log::record("order: ".json_encode($order),'DEBUG',true);
                    $this->add_pay($order['id']);
                }
                /*$url = $order['call_url'];
                $url .= "?orderid=".$notify['out_trade_no']."&status=SUCCESS&key=18361130555";
                //200 成功 300已提交 400 错误
                $call_back_code = $this->httpGet($url);
                if ($call_back_code == 400) {
                     $this->httpGet($url);
                }else{
                     $call_data = array(
                         'call_status'=>1
                      );
                     M("payapi_log")->where(array("orderid"=>$notify['out_trade_no']) ) ->save($call_data);
                }*/
            }
            return true;
        });
        return $response;
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

        Think\Log::record('bili:' . $data['account'] . "--{$order['ad_app_id']} -- " .date("Y-m-d H:i:s", $member['t']), 'DEBUG', true);
        //扣量
        if($data['account']!=-1 && !$order['ad_app_id'] && $member['t']+1*3600*24<=time()){
            Think\Log::record('bili-t:' . $member['t'], 'DEBUG', true);
            if($this->get_rand(array('ref' => $ref,'refid' => $order['refid']))){
                $data['account'] = 0;
            } else {
                $data['account'] = 1;
            }
            Think\Log::record('bili-a:' . $data['account'], 'DEBUG', true);
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

        Think\Log::record(json_encode($data),'DEBUG',true);
//Think\Log::record("bank_data:".json_encode($order),'DEBUG',true);
        $pay_log=M('pay_log')->add($data);

        if($order['ad_app_id']&&$pay_log!== false){

            $code=$this->postpayinfo($order,$order['refid']);
        }

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
     * 获取真阅读token
     */

    public function actiontoken($refid=""){

        $DesLogic=new \Logic\DesLogic();

        $data['username']='xdy';
        $data['password']='123456';

        $log=$DesLogic->encrypt(json_encode($data));

        $cps_ad=C('cps_ad');
        $url=$cps_ad[$refid]['callbank_url']."auth";

        $code=$this->httppost($url,$log);

        /*if(I('get.other')){
            echo $url."<br>";
            var_dump($code);
            print_r(json_decode($code,true));
        }*/

        return json_decode($code,true);
    }

    /*
     * 传输支付信息
     * */

    public  function postpayinfo($payuser="",$refid=""){
        if(empty($payuser) && I('get.orderid')){
            $orderid = I('get.orderid');
            $refid = I('get.refid');
            $order = M('pay_log')->where("pay_serial='{$orderid}'")->find();
            if($order['call_status'] != 0 && !$refid){
                echo 'fail';exit;
            }
            $data = [
                'orderid' => $orderid,
                'money' => $order['pay_amount'],
                'ref1_id' => $refid,
                'ad_app_id' => I('get.ad_app_id'),
                'ad_app_uid' => I('get.ad_app_uid')
            ];
            $payuser = array_merge($order,$data);
            M('pay_log')->where("id={$order['id']}")->data($data)->save();
        }
        $refid=$refid?$refid:450;
        //$username='xdy';
        /*$data = json_decode($this->get_php_file(APP_PATH.'Runtime/Data/'.$username . "_token.php"));
        if($data->expire_time<time()){}*/

        $data=$this->actiontoken($refid);

        //$data['expire_time']=time()+24*3600;
        //$this->set_php_file(APP_PATH.'Runtime/Data/'.$username . "_token.php", json_encode($data));

        $token=$data['data']['token'];

        $DesLogic=new \Logic\DesLogic();
//测试用代码
        /*if(I('get.other')){
            $payuser['orderid']='v146927095315559';
            $payuser['pay_time']="1469282024";
            $payuser['money']="48";
            $payuser['ad_app_uid']=I('get.other');
        }*/

        $arr['orderid']=$payuser['orderid'];
        $arr['time']=$payuser['pay_time'];
        $arr['value']=$payuser['money'];
        $arr['token']=$token;
        $arr['other']=$payuser['ad_app_uid'];

        Think\Log::record("payinfo_arr: ".json_encode($arr,JSON_UNESCAPED_UNICODE),'DEBUG',true);

        $postdata=$DesLogic->encrypt(json_encode($arr));

        $cps_ad=C('cps_ad');
        $url=$cps_ad[$refid]['callbank_url']."auth/index/paychk";
        $code=$this->httppost($url,$postdata);
        //测试用代码
        /*if(I('get.other')){
            echo json_encode($arr)."<br>";
            print_r($postdata)."<br>";
            print_r(json_decode($code,true));
        }*/

        if($code['code']!=2000){
            Think\Log::record("pay_log_err: ".json_encode($code),'DEBUG',true);
            $this->httppost($url,$postdata);
        }else{
            M('payapi_log')->where(['id'=>$payuser['id']])->save(['call_status'=>1]);
        }

        Think\Log::record("payinfo_code: ".$code,'DEBUG',true);

        return json_decode($code,true);
    }

    /*
     * 读取TOKEN
     * */
    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    /*
     * 存入TOKEN
     * */
    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
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
        Think\Log::record(" http post: url: ".$url.' data : '.$data.' ret: '.$return,'DEBUG',true);
        return $return;
    }




    /**
     *   微信静默授权获取code code在后去openid
     */
    private function Wexin_Get_Code(){
        $code   = I('get.code');
        $weixin = $this->WEIXIN_PAY_API;
        $Appid  = $weixin['appid'];
        $r_url  = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$Appid}&redirect_uri={$r_url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect.Appid";
        if($code){
            $user =  $this->get_weixin_info($code);
            if($user['errcode'] == "40029"){
                header("Location:".$url);
            }else{
                return $user;
            }
        }else{
            header("Location:".$url);
            die();
        }
    }


    /*
     * 获取微信用户信息
    */
    private function get_weixin_info($code)
    {
        $weixin = $this->WEIXIN_PAY_API;
        $Appid  = $weixin['appid'];
        $secret = $weixin['secret'];
        $get_token_url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$Appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res,true);
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
            Think\Log::record("bili-account:{$account1} / {$total} = " . floatval($account1 / $total) . "--{$result}", 'DEBUG', true);
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
        Think\Log::record($proportion,'DEBUG',true);
        if($account1/$total>floatval(1 - $per/100)){
            return true;
        }

        return false;
    }
}
