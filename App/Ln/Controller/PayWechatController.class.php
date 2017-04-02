<?php
/**
 * 支付微信公众号
 * User: dark
 * Date: 2016/7/30 0030
 */
namespace Ln\Controller;

use Ln\Controller\ComController;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Core\AccessToken;

class PayWechatController extends ComController {

    protected $options;
    protected $WEIXIN_PAY_API;
    protected $token_code = array(
            '-1'  => '系统繁忙',
            0     =>  '请求成功',
            40001 => '获取access_token时AppSecret错误，或者access_token无效',
            40002 =>  '不合法的凭证类型',
            40003 =>  '不合法的OpenID',
            40004 =>  '不合法的媒体文件类型',
            40005 =>  '不合法的文件类型',
            40006 =>  '不合法的文件大小',
            40007 =>  '不合法的媒体文件id',
            40008 =>  '不合法的消息类型',
            40009 =>  '不合法的图片文件大小',
            40010 =>  '不合法的语音文件大小',
            40011 =>  '不合法的视频文件大小',
            40012 =>  '不合法的缩略图文件大小',
            40013 =>  '不合法的APPID',
            40014 =>  '不合法的access_token',
            40015 =>  '不合法的菜单类型',
            40016 =>  '不合法的按钮个数',
            40017 =>  '不合法的按钮个数',
            40018 =>  '不合法的按钮名字长度',
            40019 =>  '不合法的按钮KEY长度',
            40020 =>  '不合法的按钮URL长度',
            40021 =>  '不合法的菜单版本号',
            40022 =>  '不合法的子菜单级数',
            40023 =>  '不合法的子菜单按钮个数',
            40024 =>  '不合法的子菜单按钮类型',
            40025 =>  '不合法的子菜单按钮名字长度',
            40026 =>  '不合法的子菜单按钮KEY长度',
            40027 =>  '不合法的子菜单按钮URL长度',
            40028 =>  '不合法的自定义菜单使用用户',
            40029 =>  '不合法的oauth_code',
            40030 =>  '不合法的refresh_token',
            40031 =>  '不合法的openid列表',
            40032 =>  '不合法的openid列表长度',
            40033 =>  '不合法的请求字符，不能包含\uxxxx格式的字符',
            40035 =>  '不合法的参数',
            40038 =>  '不合法的请求格式',
            40039 =>  '不合法的URL长度',
            40050 =>  '不合法的分组id',
            40051 =>  '分组名字不合法',
            41001 =>  '缺少access_token参数',
            41002 =>  '缺少appid参数',
            41003 =>  '缺少refresh_token参数',
            41004 =>  '缺少secret参数',
            41005 =>  '缺少多媒体文件数据',
            41006 =>  '缺少media_id参数',
            41007 =>  '缺少子菜单数据',
            41008 =>  '缺少oauth code',
            41009 =>  '缺少openid',
            42001 =>  'access_token超时',
            42002 =>  'refresh_token超时',
            42003 =>  'oauth_code超时',
            43001 =>  '需要GET请求',
            43002 =>  '需要POST请求',
            43003 =>  '需要HTTPS请求',
            43004 =>  '需要接收者关注',
            43005 =>  '需要好友关系',
            44001 =>  '多媒体文件为空',
            44002 =>  'POST的数据包为空',
            44003 =>  '图文消息内容为空',
            44004 =>  '文本消息内容为空',
            45001 =>  '多媒体文件大小超过限制',
            45002 =>  '消息内容超过限制',
            45003 =>  '标题字段超过限制',
            45004 =>  '描述字段超过限制',
            45005 =>  '链接字段超过限制',
            45006 =>  '图片链接字段超过限制',
            45007 =>  '语音播放时间超过限制',
            45008 =>  '图文消息超过限制',
            45009 =>  '接口调用超过限制',
            45010 =>  '创建菜单个数超过限制',
            45015 =>  '回复时间超过限制',
            45016 =>  '系统分组，不允许修改',
            45017 =>  '分组名字过长',
            45018 =>  '分组数量超过上限',
            46001 =>  '不存在媒体数据',
            46002 =>  '不存在的菜单版本',
            46003 =>  '不存在的菜单数据',
            46004 =>  '不存在的用户',
            47001 =>  '解析JSON/XML内容错误',
            48001 =>  'api功能未授权',
            50001 =>  '用户未授权该api',
            '50002' =>  '该公众号被屏蔽',
        );

    public function index()
    {
        $where = '1=1';
        if(I('get.status',-1) > -1){
            $where = 'status='.I('get.status',-1);
        }

        $wechat = M('pay_wechat')->field('*')->where($where)->order('id DESC')->select();
		
		$status_label = ['0' => '关闭', '1' => '启用', '2' => '异常', '3' => '待用'];
        foreach($wechat as $k=>$v){
            $wechat[$k]['status'] = $status_label[$v['status']];
        }
        $this->assign('list', $wechat);

        $this -> display();
    }

    public function add(){
        $this -> display();
    }

    public function testQrCode(){

        $id  = I('get.id',0,'intval');
        $wechat = M('pay_wechat')->field('*')->where("id=$id")->find();
        if(!$id){
            $back_data = array('status' => '0', 'msg' => '无效的参数！');
        }else{
            $path = dirname(APP_PATH);
            $url = '/Public/Ln/images/paytestqrcode/'.$id.'/' . $id . '_' . $wechat['api_url'] . '.png';
            if(!file_exists($path . $url)){
                try {
                    $url = $this->TestnewQrCode($id, ['host' =>$wechat['api_url']]);

                    $back_data = array('status' => '1', 'msg' => '生成成功！','url' => $url);
                } catch (Exception $e){
                    $url = '1001';
                    $back_data = array('status' => '0', 'msg' => '生成失败！');
                }
            }else{
                $back_data = array('status' => '1', 'msg' => '已经生成！', 'url' =>$url);
            }
        }
        echo json_encode($back_data);
    }


    public function del(){
        $ids = isset($_REQUEST['ids'])?$_REQUEST['ids']:false;
        if($ids){
            if(is_array($ids)){
                $ids = implode(',',$ids);
                $map['id']  = array('in',$ids);
            }else{
                $map = 'id='.$ids;
            }

            $data = M('pay_wechat')->where($map)->find();
            if(M('pay_wechat')->where($map)->delete()){
                addlog('删除支付公众号，ID：'.$ids."  公众号名称：".$data['name']);
                $this->success('恭喜，支付公众号删除成功！');
            }else{
                $this->error('参数错误！');
            }
        }else{
            $this->error('参数错误！');
        }
    }

    public function edit($id = 0)
    {
        $id = intval($id);
        $wechat = M('pay_wechat')->where('id='.$id)->find();
        if($wechat){
            $this->assign('wechat',$wechat);
        }else{
            $this->error('参数错误！');
        }

        $this -> display();
    }

    public function update($id=0){
        $id = intval($id);
        $data['web_id'] = isset($_POST['web_id'])?$_POST['web_id'] : 1;
        $data['web_url'] = isset($_POST['web_url'])?$_POST['web_url'] : '';
        $data['api_url'] = isset($_POST['api_url'])?$_POST['api_url']:'';
        $data['name'] = isset($_POST['name'])?$_POST['name']:'';
        $data['app_id'] = isset($_POST['app_id'])?$_POST['app_id']:'';
        $data['secret'] = I('post.secret','','strip_tags');
        $data['merchant_id'] = I('post.merchant_id','','strip_tags');
        $data['key'] = I('post.key','','strip_tags');
        $data['status'] = isset($_POST['status'])?$_POST['status']: 3;

        if(!$data['app_id'] or !$data['secret'] or !$data['merchant_id'] or !$data['key'] or !$data['web_id'] or !$data['api_url'] or !$data['web_url']){
            $this->error('警告！必填项不能为空。');
        }

        if($data['status'] == 1){
            //关闭其他启用的支付
            $ids = M('pay_wechat')->where('status=1 AND web_id='.$data['web_id'])->getField('id',true);
            if(!empty($ids)){
                $ids = implode(',',$ids);
                M('pay_wechat')->data(['status'=> 2])->where(['id' => ['in', $ids]])->save();
            }
            //同步
            $r_data = [
                'app_id' => '1000000001',
                'url' => $data['api_url'],
            ];
            $r_data['sign'] = get_cps_sign($r_data);
            $ret = curl_get(C('MOIVE_API_HOST') . 'index.php/index/admin/site_pay_url?',$r_data);
			\Think\Log::record('pay-wechat:' . json_encode($ret),'DEBUG',true);
            $status = '启用';
        }else if($data['status'] == 2){
            $status = '异常';
        }else{
            $status = '待用';
        }

        if($id){
            if(M('pay_wechat')->data($data)->where('id='.$id)->save()) {

                addlog('编辑支付公众号，ID：' . $id."  公众号名称：".$data['name']." 状态：".$status,false,2);
                $this->success('恭喜！支付公众号编辑成功！', U('index'));
            }else{
                $this->error('抱歉，未知错误！');
            }

        }else{
            $id = M('pay_wechat')->data($data)->add();
            if($id){
                addlog('新增支付公众号，ID：'.$id."  公众号名称：".$data['name']." 状态：".$status,false,2);
                $this->success('恭喜！支付公众号新增成功！', U('index'));
            }else{
                $this->error('抱歉，未知错误！');
            }
        }
    }

    /**
     * 一键切换支付
     */
    public function quick_update()
    {
        $pay_model = M('pay_wechat');
        $new_pay = $pay_model->where('status=3 AND web_id=1')->find();//获取一个备用支付
        if(empty($new_pay)){
            $this->error('警告！没有备用支付了');
        }

        $pay_model->where('status=1 AND web_id=1')->data(['status'=> 2])->save();//标示正在启用的支付为异常
        $pay_model->where('id='.$new_pay['id'])->data(['status'=>1])->save();//启用新的支付

        //同步到moive
        $data = [
            'app_id' => '1000000001',
            'url' => $new_pay['api_url'],
        ];
        $data['sign'] = get_cps_sign($data);
        $ret = curl_get(C('MOIVE_API_HOST') . 'index.php/index/admin/site_pay_url?',$data);
        $ret = json_decode($ret, true);
        if($ret['ret'] != '00'){
            $this->error('同步到moive失败');
        }

        $this->success('切换成功');
    }


    public function get_access_token($id){

        $id = intval($id);
        $wechat = M('pay_wechat')->where('id='.$id)->find();
        $options        = array(
            'app_id'         => $wechat['app_id'],
            'secret'         => $wechat['secret'],
        );
        // import("Lib/WeiXinPay/Autoload");
        // $app = new Application($options);
        // try {
            // $accessToken = $app->access_token;
            // $token = $accessToken->getToken();
            // var_dump($token);die();
        // } catch (\Exception $ex) {
        //     $msg = $ex->getMessage();
        // }
        
        try{
            $accessToken = new AccessToken($wechat['app_id'], $wechat['secret']);
            // $accessToken = new AccessToken('wx2a6114649fd95e91', 'a4bc0347ad7287edb1533badf3d40659');
            $token = $accessToken->getToken(true);
        } catch (\Exception $ex) {
            $errormsg = $ex->getMessage();
            $code = strstr($ex->getMessage(), '{"');
            $code = json_decode($code, true);
            $code = $code['errcode'];
            $usermessage = "<span style='text-align: center; color: #f30808; font-weight: bold; font-size: 16px;'>异常：" . $this->token_code[$code] . ",</span><br>异常详情：". $errormsg;
            // var_dump($usermessage) ;die();

        }
        
        if ($token) {
            echo json_encode(array('code'=>1, 'token'=>"<div style='text-align: center; color: #09b145; font-weight: bold; font-size: 16px;    height: 60px; line-height: 60px;'>该支付公众号正常</div>"));die();
        }else{
            echo json_encode(array('code'=>0, 'token'=>$usermessage));die();
        }
        
        $this->display();
    }
}
