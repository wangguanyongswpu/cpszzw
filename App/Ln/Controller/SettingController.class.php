<?php
/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2016-01-20
* 版    本：1.0.0
* 功能说明：网站设置控制器。
*
**/

namespace Ln\Controller;
use Ln\Controller\ComController;

class SettingController extends ComController {
    public function setting(){

		$vars = M('setting')->where('type=1')->select();
        foreach ($vars as $key => $value)
        {
            $vars[$key]['data'] = json_decode($value['data'],true);
        }
        $this->assign('vars',$vars);
		$this -> display();
    }

    public function update(){
		$data = $_POST;
		$data['sitename'] = isset($_POST['sitename'])?strip_tags($_POST['sitename']):'';
		$data['title'] = isset($_POST['title'])?strip_tags($_POST['title']):'';
		$data['keywords'] = isset($_POST['keywords'])?strip_tags($_POST['keywords']):'';
		$data['description']= isset($_POST['description'])?strip_tags($_POST['description']):'';
		$data['footer'] = isset($_POST['footer'])?$_POST['footer']:'';
		$data['interval'] = isset($_POST['interval'])?$_POST['interval']:'';
		$Model = M('setting');
		
		$paytype=$Model->where("k = 'paytype' ")->find();
		if($data['paytype']!=$paytype['v']){
			$type[1]="公众号";
			$type[2]="第三方威富通";
			addlog('启用'.$type[$data["paytype"]].'支付方式',false,2);
		}

		foreach($data as $k=>$v){
			$Model->data(array('v'=>$v))->where("k='{$k}'")->save();
		}
		addlog('修改网站配置。');
		$this->success('恭喜，网站配置成功！');
    }

    /*
     * 支付回调设置
    */

    public function notify(){
    	$list = M('notify_url')->select();

    	$this->assign('list',$list);
    	$this->display();
    }
    /*
     * 修改异步通知
    */
    public function add(){
		$post = I('post.','','trim');
		if( !$post['url'] || !$post['name'] ){
			$this->error('请将数据填写完整!');exit;
		}
		if(M('notify_url')->add($post)){
			$this->success('添加成功!');exit;
		}
		
		$this->error('操作失败!');
    }

    public function notify_handel(){
    	$get = I('get.','');
    	if($get['id'] && $get['status'] >-1){
    		if($get['status'] == 1){
    			$up = ['status' => 0 ];
    		}else{
    			$up = ['status' => 1];
    		}
    		if(M('notify_url')->where(['id' => $get['id']])->save($up)){
    			$this->success('操作成功!');exit;
    		}
    	}
    	$this->error('操作失败!');
    }

    public function notify_del(){
    	if($_GET['id']){
    		if( M('notify_url')->where(['id'=>$_GET['id'] ])->delete() ){
    			$this->success('删除成功!');exit;
    		}
    	}
    	$this->error('操作失败!');
    }

    public function notify_update(){
    	$post = I('post.','','trim');
    	$id = I('param.id','');
    	if(!$id){
    		$this->error('参数错误!');exit;
    	}
    	if($post){
           if( M('notify_url')->where(['id'=>$post['id']])->save($post) ){
           	 $this->success('修改成功!',U('notify'));exit;
           }
           $this->error('操作失败!');exit;
    	}
    	$result = M('notify_url')->find($id);
    	$this->assign('result',$result);
        $this->display();
    }
}
