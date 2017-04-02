<?php
/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2016-01-17
* 版    本：1.0.0
* 功能说明：外部获取推广链接的控制器。
*
**/

namespace Ln\Controller;
use Common\Controller\BaseController;
use Think\Auth;
class GeturlController extends BaseController {
    public function index(){
		$refid=1;
		if(isset($_GET['refid']) && !empty($_GET['refid']) && intval($_GET['refid'])==$_GET['refid']){
			$refid=$_GET['refid'];
		}else{
			echo '推广ID有误';exit;
		}
		$member = M('member')->where("uid={$refid}")->find();
		if(empty($member)){
			echo '推广ID不存在';exit;
		}
        $Mdomain = M('domain');

        $group_domain = $Mdomain->where(array('type' => 3, 'look' => 0))->limit(1)->select();
		
        $time = date("YmdHi");
        foreach ($group_domain as $v) {
            $url = 'http://' . $v['url'] . C('PORT') . '/' . $time . '/' . $refid . '.png';
            $domain[] = array('url' => $url);
        }
		if(!isset($domain['0']['url']) || empty($domain['0']['url'])){
			echo '推广链接有误，请联系管理员！';exit;
		}
		header('location:'.$domain['0']['url']);
    }
}
