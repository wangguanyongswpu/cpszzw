<?php
/**
*
* 版权所有：恰维网络<Ln.qiawei.com>
* 作    者：寒川<hanchuan@qiawei.com>
* 日    期：2016-01-17
* 版    本：1.0.0
* 功能说明：后台登录控制器。
*
**/

namespace Ln\Controller;
use Common\Controller\BaseController;
use Think\Auth;
class LoginController extends BaseController {
    public function index(){
		$flag = false;
		if($_SESSION['auth']){
			$auth=$_SESSION['auth'];
		}else{
			$auth = $_COOKIE["auth"];
		}
        if($_SESSION['user_id']){
            $user_id = $_SESSION["user_id"];
        }else {
            $user_id = $_COOKIE["user_id"];
        }
        if($user_id){
            $user = M('member')->field('uid,user,identifier,token,salt')->where(array('uid'=>$user_id))->find();
            $this->USER = $user;
            $flag = true;
        }
		/*list($identifier, $token) = explode(',', $auth);
		if (ctype_alnum($identifier) && ctype_alnum($token)) {
			$user = M('member')->field('uid,user,identifier,token,salt')->where(array('identifier'=>$identifier))->find();
			if($user) {
				if($token == $user['token'] && $user['identifier'] == password($user['uid'].md5($user['user'].$user['salt']))){
					$flag = true;
					$this->USER = $user;
				}
			}
		}*/
        if ($flag) {
			
           $this -> error('您已经登录,正在跳转到主页',U("Tg/index"));
        }

		$this -> display();
    }


    public function login(){
		//  开启session
		Session_start();
		//  判断用户传过来的验证码是否存在，不存在设置为空。
		$verify = isset($_POST['verify'])?trim($_POST['verify']):'';
		//  判断用户输入的验证码是否输入合法（调用当前类中定义好的验证方法check_verify，传入参数）
		/*if (!$this->check_verify($verify,'login')) {
			//  用户输入错误，提示错误信息，并跳转到登录页面
			$this -> error('验证码错误！',U("login/index"));
			exit;
		}*/
		//  接收用户输入的用户名称，没有输入则赋值为空
		$username = isset($_POST['user'])?trim($_POST['user']):'';
		//  接收用户输入的用户密码，没有输入则赋值为空
		$password = isset($_POST['password'])?password(trim($_POST['password'])):'';
		//  接收用户输入选择的是否自动登录，没有选则赋值为0
		$remember = isset($_POST['remember'])?$_POST['remember']:0;
		//  用户名输入为空
		if ($username=='') {
			//  提示错误信息，并跳转到登录页面
			$this -> error('用户名不能为空！',U("login/index"));
			exit;
			//  用户密码输入为空
		} elseif ($password=='') {
			//  提示错误信息，并跳转到登录页面
			$this -> error('密码必须！',U("login/index"));
			exit;
		}
		//  实例化用户模型对象
		$model = M("Member");
		//  根据条件查询出该用户的对应数据信息
		$user = $model ->field('uid,user,login_lock')-> where(array('user'=>$username,'password'=>$password)) -> find();		//\Think\Log::record('login error:' . $_POST['password'] . ' user:'.$_POST['user'],'DEBUG',true);		//\Think\Log::record('login error:' . $model->getLastSql(),'DEBUG',true);
		//  用户数据信息存在
		if($user && $user['login_lock']==1) {
			//  判断该用户数据当中存在token值没有
			if($user['token']){
				//  存在赋值到变量中
				$token=$user['token'];
			}else{
				//  创建新的token值(调用password方法将传过去的值进行md5加盐加密)
				$token = password(uniqid(rand(), TRUE));
			}
			//  判断该用户数据当中存在salt值没有
			if($user['salt']){
				//  存在赋值到变量中
				$salt=$user['salt'];
			}else{
				//  创建新的salt值(调用random方法将长度字节传输过去)
				$salt = random(10);
			}
			//  md5加密
			$identifier = password($user['uid'].md5($user['user'].$salt));

			$auth = $identifier.','.$token;
			
			M('member')->data(array('identifier'=>$identifier,'token'=>$token,'salt'=>$salt))->where(array('uid'=>$user['uid']))->save();

			//  用户选择自动登录
			if($remember){				
				setcookie('auth',$auth,3600*24*365);//记住我				
				session('user_id',$user['uid']);				
				setcookie('BJYSESSION',session_id(),time()+3600*24);				
				//$_SESSION['user_id']=$user['uid'];				
				setcookie('user_id',$user['uid'],3600*24*365);			
			}else{				
				setcookie('auth',$auth,3600*5);				
				session('user_id',$user['uid']);				
				setcookie('BJYSESSION',session_id(),time()+3600*2);				
				//$_SESSION['user_id']=$user['uid'];				
				setcookie('user_id',$user['uid'],3600*5);
			}

			//  写入登录日志
			addlog('登录成功。',false,5);
			//  设置跳转的URL地址
			$url=U('Ln/Tg/index');
			//  跳转到首页
			header("Location: $url");
			exit(0);
		}else if($user && $user['login_lock']!=1){
			$this -> error('帐号被锁定，请联系管理员！',U("login/index"));
		}else{
			//  添加本次登录情况日志
			addlog('登录失败。',$username);
			//  提示错误信息，并跳转到登录页面
			$this -> error('账号或密码有误，请重试！',U("login/index"));
		}
    }

	//  验证码
	public function verify() {
		$config = array(
		'fontSize' => 14, // 验证码字体大小
		'length' => 4, // 验证码位数
		'useNoise' => false, // 关闭验证码杂点
		'imageW'=>100,
		'imageH'=>30,
		);
		$verify = new \Think\Verify($config);
		$verify -> entry('login');
	}

	//  定义验证验证码的方法
	function check_verify($code, $id = '') {
		$verify = new \Think\Verify();
		return $verify -> check($code, $id);
	}
}
