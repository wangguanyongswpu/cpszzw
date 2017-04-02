<?php

/**

 *

 * 版权所有：恰维网络<Ln.qiawei.com>

 * 作    者：寒川<hanchuan@qiawei.com>

 * 日    期：2016-01-20

 * 版    本：1.0.0

 * 功能说明：用户控制器。

 *

 **/

namespace Ln\Controller;

use Ln\Controller\ComController;

class TghbController extends ComController
{
	public function index()
	{
		$id = I("get.id");
		if($id){
			$info = M("tghb")->where("id=".$id)->find();
			$this->assign("info",$info);
		}
		$list = M("tghb")->select();
		foreach($list as $k=>$v){
			$list[$k]["state_str"] = $v["state"]==1 ? "备用" : "启用";
		}
		$this->assign("list",$list);
		$this->display();
	}
	public function update(){
		$id = I("post.id");
		$data["api_domain"] = I("post.api_domain");
		$data["show_domain"] = I("post.show_domain");
		$data["state"] = I("post.state");
		$data["ip"] = I("post.ip");
		if($id){
			$res = M("tghb")->where("id=".$id)->save($data);
		}else{
			$res = M("tghb")->add($data);
			$id = $res;
		}
		if($res){
			if($data["state"]==2){
				M("tghb")->where("state=2 and id!=".$id)->save(["state"=>1]);
			}
			$this->success("编辑成功",U("Tghb/index"));
		}else{
			$this->error("编辑失败");
		}
	}
	public function change_state(){
		$id = I("get.id");
		$state = I("get.state")==1 ? 2 : 1;
		$res = M("tghb")->where("id=".$id)->save(["state"=>$state]);
		if($res){
			if($state==2){
				M("tghb")->where("state=2 and id!=".$id)->save(["state"=>1]);
			}
			$this->success("编辑成功");
		}else{
			$this->error("编辑失败");
		}
	}
	
	public function del(){
		$id = I("get.id");
		$res = M("tghb")->where("id=".$id)->delete();
		if($res){
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
	}
	
	
	
}