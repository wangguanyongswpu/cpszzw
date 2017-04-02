<?php
/**
 * 微信设置相关
 * User: dark
 * Date: 2016/7/30 0030
 */
namespace Ln\Controller;
use Ln\Controller\ComController;

class WechatController extends ComController {
    public function index()
    {
        $wechat = M('wechat')->field('*')->order('gid asc')->select();
		
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

    public function del(){
        $ids = isset($_REQUEST['ids'])?$_REQUEST['ids']:false;
        if($ids){
            if(is_array($ids)){
                $ids = implode(',',$ids);
                $map['id']  = array('in',$ids);
            }else{
                $map = 'id='.$ids;
            }

            if(M('wechat')->where($map)->delete()){
                addlog('删除公众号，ID：'.$ids);
                $this->success('恭喜，公众号删除成功！');
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
        $wechat = M('wechat')->where('id='.$id)->find();
        if($wechat){
            $this->assign('wechat',$wechat);
        }else{
            $this->error('参数错误！');
        }

        $this -> display();
    }

    public function update($id=0){
        $id = intval($id);
        $data['gid'] = I('post.gid');
        $data['name'] = isset($_POST['name'])?$_POST['name']:'';
        $data['app_id'] = isset($_POST['app_id'])?$_POST['app_id']:'';
        $data['secret'] = I('post.secret','','strip_tags');
        $data['token'] = I('post.token','','strip_tags');
        $data['domain'] = I('post.domain','','strip_tags');
        $data['status'] = isset($_POST['status'])?$_POST['status']: 0;;

        if(!$data['gid'] or !$data['app_id'] or !$data['secret'] or !$data['token'] or !$data['domain']){
            $this->error('警告！必填项不能为空。');
        }

        if($id){
            M('wechat')->data($data)->where('id='.$id)->save();
            addlog('编辑公众号，ID：'.$id);
            $this->success('恭喜！公众号编辑成功！');

        }else{
            $id = M('wechat')->data($data)->add();
            if($id){
                addlog('新增公众号，ID：'.$id);
                $this->success('恭喜！公众号新增成功！');
            }else{
                $this->error('抱歉，未知错误！');
            }
        }
    }

    public function sync()
    {
        $url = C('MOIVE_API_HOST') . 'index.php?m=Index&c=Log&a=getWechatConfig';
        $param = ['time' => time() + mt_rand(1000, 10000)];
        $param['sign'] = md5($param['time']);
        $ret = $this->curl_get($url, $param);
        \Think\Log::record($ret,'DEBUG',true);
        $ret = json_decode($ret, true);

        if($ret['ret'] != '00'){
            echo 0;
            exit;
        }

        $model = M('wechat');
        foreach($ret['data'] as $v){
            $wechat = $model->where("gid={$v['gid']}")->find();
            $data = [
                'name' => $v['name'],
                'app_id' => $v['app_id'],
                'secret' => $v['secret'],
                'token' => $v['token'],
                'domain' => $v['domain'],
                //'status' => $v['status']
            ];
            if($wechat){
                $model->data($data)->where("id={$wechat['id']}")->save();
            } else {
                $data['gid'] = $v['gid'];
                $model->data($data)->add();
            }
        }
        echo 1;
    }
	
	public function wxselect(){
		$dd='';
		if(!empty($_GET['id'])){
			$dd=' and id>'.$_GET['id'];
		}
		$ret=M('payapi_log')->where('time>=1480089600 and time<1480118400'.$dd)->find();
		if(empty($ret)){
			echo 'end';exit; 
		}
		if(!empty($ret['transaction_id'])){
			$parm=array(
				'appid'=>'wx555e72dc7abab9cf',
				'mch_id'=>'1396683002',
				'transaction_id'=>$ret['transaction_id'],
				'out_trade_no'=>'',
				'nonce_str'=>time(),
				'sign_type'=>'MD5',
			);
			ksort($parm);
			$buff = "";

			foreach ($parm as $k => $v)
			{
				if($k != "sign" && $v != "" && !is_array($v)){
					$buff .= $k . "=" . $v . "&";
				}
			}
			$string = trim($buff, "&");
			

			//签名步骤二：在string后加入KEY

			$string = $string . "&key=fe3c7ac7de5bb9ab2436eb7ec0f21be4";

			//签名步骤三：MD5加密

			$string = md5($string);

			//签名步骤四：所有字符转为大写

			$parm['sign'] = strtoupper($string);
			
			
			$url='https://api.mch.weixin.qq.com/pay/orderquery';
			$xml='<xml>';
			foreach($parm as $key=>$value){
				$xml.="<$key>$value</$key>";
			}
			$xml.='</xml>';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
			curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			 
			curl_setopt($ch, CURLOPT_POST, 1);    // post 提交方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$output = curl_exec($ch);
			curl_close($ch);
			
			libxml_disable_entity_loader(true);

			$arr= json_decode(json_encode(simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA)), true);	

			$payapi_log=array(
				'pay_serial'=>$ret['orderid'],
				'pay_amount'=>$ret['money'],
				'pay_channel_serial'=>$ret['transaction_id'],
				'status'=>$ret['status'],
				'trade_state'=>isset($arr['trade_state'])?$arr['trade_state']:'',
				'total_fee'=>isset($arr['total_fee'])?$arr['total_fee']:'',
				'time_end'=>isset($arr['time_end'])?$arr['time_end']:'',
			);
			M('pay_bf')->add($payapi_log); 
		} 
		
		header("Location:http://cps.hnszzqy.com/index.php?m=Ln&c=Wechat&a=wxselect&id".$ret['id']);
	}
}
