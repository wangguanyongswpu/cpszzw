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



class LogController extends ComController  {

    public  function index(){
        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $pagesize = 100;#每页数量
        $offset = $pagesize*($p-1);//计算记录偏移量
        $count = M('log')->where('log_type =1')->count();
        $list = M('log')->field('*')->where('log_type =1')->limit($offset.','.$pagesize)->order('id desc')->select();


        $page	=	new \Think\Page($count,$pagesize);
        $page = $page->show();

        $this->assign('page',$page);
        $this->assign('list', $list);

        $this -> display();
    }
	
	public  function paylog(){
		$p = isset($_GET['p'])?intval($_GET['p']):'1';
		$pagesize = 100;#每页数量
		$offset = $pagesize*($p-1);//计算记录偏移量
		$count = M('log')->where('log_type =2')->count();
		$list = M('log')->field('*')->where('log_type =2')->limit($offset.','.$pagesize)->order('id desc')->select();


		$page	=	new \Think\Page($count,$pagesize);
		$page = $page->show();

		$this->assign('page',$page);
		$this->assign('list', $list);

		$this -> display();
	}

    public function loginLog(){
        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $pagesize = 50;#每页数量
        $offset = $pagesize*($p-1);//计算记录偏移量
        //$count = M('log')->field('*,count(id) as nums')->group("uid")->where('log_type =5')->count();
        //$list = M('log')->field('*')->where('log_type =0')->limit($offset.','.$pagesize)->order('id desc')->select();
		$count = M('log')->field('*,count(id) as nums')->group("uid")->where('log_type =5')->count();
		$list = M("log")->query("select *,count(id) as nums from (select * from qw_log order by t desc) qw_log where log_type=5 group by uid order by nums desc limit {$offset},$pagesize");
        $page	=	new \Think\Page($count,$pagesize);
        $page = $page->show();

        $this->assign('page',$page);
        $this->assign('list', $list);

        $this->display('loginLog');
    }

	public function parallellog(){

		

			$date['starttime']=intval($_REQUEST['starttime']);
			$date['endtime']=intval($_REQUEST['endtime']);

			$user_list=$_REQUEST['user_list'];
			$order_list=$_REQUEST['order_list'];

			$log=select_log($date['starttime'],$date['endtime']);
			if($user_list){
				if(empty($log['register_log'])||$user_list!=count($log['register_log'])){
					$date['ret']='20';
					foreach($log['register_log'] as $k =>$v){
						$date['register_log'][]=$v['uid'];
					}
				}
			}

			if($order_list){
				if(empty($log['payapi_log'])||$order_list!=count($log['payapi_log'])){
					$date['ret']='20';
					foreach($log['payapi_log'] as $k =>$v){
						$date['payapi_log'][]=$v['orderid'];
					}
				}
				if(empty($log['pay_log'])||$order_list!=count($log['pay_log'])){
					$date['ret']='20';
					foreach($log['pay_log'] as $k =>$v){
						$date['pay_log'][]=$v['pay_serial'];
					}
				}
			}
			//echo formatResponse($log);
			echo formatResponse($date);
	/*if($_REQUEST['token']==md5("LOG".$_REQUEST['time'].C("PAY_API_CALL_KEY"))){
		}else{
			$_REQUEST['ret']="30";
			echo formatResponse($_REQUEST);
		}
*/


		//$this -> display();
	}
	//域名获取日志
	public function get_domain(){
		$p = isset($_GET['p'])?intval($_GET['p']):'1';
		$pagesize = 50;#每页数量
		$offset = $pagesize*($p-1);//计算记录偏移量
		$count = M('log')->field('*,count(id) as nums')->group("uid")->where('log_type=6')->count();
		$list = M("log")->query("select *,count(id) as nums from (select * from qw_log order by t desc) qw_log where log_type=6 group by log order by nums desc limit {$offset},$pagesize");
		$page	=	new \Think\Page($count,$pagesize);
		$page = $page->show();

		$this->assign('page',$page);
		$this->assign('list', $list);

		$this->display('get_domain');
	}

	//ajax获取域名日志详情
	public function domain_detail(){
		$domain = I("get.domain");
		$uid = I("get.uid");
		if($uid){
			$list = M("log")->query("select * from qw_log where log_type=6 and log='{$domain}' and uid='{$uid}' order by t desc");
		}else{
			$list = M("log")->query("select *,count(id) as nums from (select * from qw_log order by t desc) qw_log where log_type=6 and log='{$domain}' group by uid order by nums desc");
		}

		foreach($list as $k=>$v){
			$list[$k]["t"] = date("Y-m-d H:i:s",$v["t"]);
		}
		echo json_encode($list);
		exit;
	}
}