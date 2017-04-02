<?php
/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2015-09-17
* 版    本：1.0.0
* 功能说明：管理后台模块公共控制器，用于储存公共数据。
*
**/
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller {
    function __construct(){
    	parent::__construct();								    																																															
    }
	public function _initialize(){
		C(setting());
    }
}