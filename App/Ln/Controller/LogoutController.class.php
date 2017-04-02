<?php

/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2016-01-17
* 版    本：1.0.0
* 功能说明：后台登出控制器。
*
**/




namespace Ln\Controller;

use Ln\Controller\ComController;

class LogoutController extends ComController {

    public function index(){
        session('user_id',null);
        setcookie('user_id',"",time()-1);
		setcookie('auth',"",time()-1);    //记住我
		setcookie('auth');
		setcookie("user_id");
		$_SESSION['auth']="";
		$_SESSION['user_id']="";
		$url = U("login/index");
		header("Location: {$url}");
		exit(0);

    }

}