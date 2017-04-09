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

class MemberController extends ComController
{
	public function index()
	{

		$cpstype = $this->cpstype();
		$this->assign('cpstype', $cpstype);
		$p = isset($_GET['p']) ? intval($_GET['p']) : '1';
		$pagesize = 15;#每页数量
		$offset = $pagesize * ($p - 1);//计算记录偏移量
		$limit = $offset . ',' . $pagesize;

		$field = 'user';//isset($_GET['field'])?$_GET['field']:'';

		$keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';

		$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

		$prefix = C('DB_PREFIX');
		$uid = empty(I('get.uid')) ? 0 : intval(I('get.uid'));
		if($uid){
			$u = M('auth_group_access')->field('uid,group_id as gid')->where("uid={$uid}")->find();
		}else{
			$u = $this->getuserid();
		}

		$ajax = empty(I('get.ajax')) ? 0 : I('get.ajax');
		//$where='1=1';
		$where = "{$prefix}member.uid <> '1'";
		if($ajax){
			$limit = '';
			$where .= " AND {$prefix}member.cid={$u['uid']}";
		}else{
			if($u['gid']==1 && !$keyword){ //搜索时查询全部
				$where.='  AND g.group_id=2 ';
			}//一级渠道
			elseif ($u['gid'] == 2) {
				$where .= " AND {$prefix}member.cid={$u['uid']}";
			} //二级渠道
			elseif ($u['gid'] == 3) {
				$where .= " AND {$prefix}member.uid={$u['uid']}";
			}
		}

		if($order == 'asc'){
			$order = "{$prefix}member.t asc";
		}elseif(($order == 'desc')){
			$order = "{$prefix}member.t desc";
		}else{
			$order = "{$prefix}member.uid asc";
		}

		if($keyword <>''){
			if($field=='user'){
				$where .= " AND {$prefix}member.user LIKE '%$keyword%'";
			}
			if($field=='phone'){
				$where .= " AND {$prefix}member.phone LIKE '%$keyword%'";
			}
			if($field=='qq'){
				$where .= " AND {$prefix}member.qq LIKE '%$keyword%'";
			}
			if($field=='email'){
				$where .= " AND {$prefix}member.email LIKE '%$keyword%'";
			}
		}

        if(I('get.suid','')){
            $where .= " and {$prefix}member.uid = ".I('get.suid','','trim');
        }

		$user = M('member');
		$count = $user->join("{$prefix}auth_group_access g ON {$prefix}member.uid=g.uid",'LEFT')->where($where)->count();

		$group = M('auth_group')->field('id,title')->select();
		$this->assign('group',$group);

		$list  = $user->field("{$prefix}member.*,{$prefix}auth_group.id as gid,{$prefix}auth_group.title,(select user from {$prefix}member as pmember where uid = {$prefix}member.cid) as puser")
			->join("{$prefix}auth_group_access g ON {$prefix}member.uid = g.uid")
			->join("{$prefix}auth_group ON {$prefix}auth_group.id = g.group_id")
			->where($where)
			->order($order)
			->limit($limit)
			->select();
		//print_r($list);exit;
		$Super_list=$this->getSuper();
		// var_dump($gidd=="1");die();
		$html = '';
		foreach($list as $key=>$value){
        	if ($value['gid'] == 2 && !in_array($value['cid'], $Super_list)) {//次一级推广
              	if ($ajax) {  
                  	$list[$key]['user'] .= '<font color="red">(次一级推广)</font>';
                  	
              	}else{//次一级推广商 获取上级
                	$Superior = M('member')->field('user')->where("uid={$value['cid']}")->find();
					$list[$key]['user'] .= '<font color="red">(上级推广：'.$Superior["user"].')</font>';
                }
              $value['user']=$list[$key]['user'];
            }
			if($ajax){
				$html .= '<tr class="child_'. $u['uid'] .'">';
				$html .= '<td>&para;&minus;&minus;'. $value['uid'] .'<input class="uids" type="hidden" name="uids[]" value="'.$value['uid'].'"></td>';
				$html .= '<td>&para;&minus;&minus;'. $value['user'] .'</td>';
				//$html .= '<td>'.$value['remark'].'</td>';
				$html .= '<td class="grouptd"><span class="group" val="'.$value['uid'].'">' .$value['title'] . '</span></td>';
				$html .= '<td>' .$value['phone'] . '</td>';
				$html .= '<td>' .$value['wei_number'] . '</td>';
				$html .= '<td>' .$value['alipay_number'] . '</td>';
				//$html .= '<td>' .$value['bank_name'] . '</td>';
				//$html .= '<td>' .$value['bank_number'] . '</td>';
				$m = $this->getuserid();
				if(in_array($m['uid'], C('FINANCE_IDS'))){
					$html .= '<td>' . $value['scale'] . '</td>';
				}
				//$html .= '<td>' . $value['puser'] . '</td>';

				//$html .= '<td></td>';
				//$html .= '<td>'.$cpstype[$value['cps_type']].'</td>';
				$html .= '<td>'.date('Y年m月d日 H:i:s', $value['t']).'</td>';
              	$html .= '<td>'.($value['login_lock']!=1?'<span style="color:red">锁定</span>':'正常').'</td>';
				// echo $_GET['gid'];die();
				if ($_GET['gid']=="1") {
					$html .= '<td class="center"><a href="'.U('edit').'&uid='.$value['uid'].'">修改</a>&nbsp;';
				}else{
					$html .= '<td class="center">&nbsp;';
				}
				
				$html .='</td>';
				$html .= '</tr>';

			}else{
				$data = $user->field("user")->where(array('uid'=>$value['cid']))->select();
				$list[$key]['puser'] = $data[0]['user'];
				$child_count = 0;
				if ($u['uid'] != $value['uid'] && $value['gid'] > 1) {
					$child_count = M('member')->where("cid={$value['uid']}")->count();
				}
				$list[$key]['date'] = empty($value['t']) ? '' : date('Y年m月d日 H:i:s', $value['t']);
				$list[$key]['has_child'] = empty($child_count) ? false : true;
				if($value['gid']==2 && !in_array($value['cid'],$Super_list)){
					$Superior = M('member')->field('user')->where("uid={$value['cid']}")->find();
					$list[$key]['title']=$value['title'].'<font color="red">(上级推广：'.$Superior["user"].')</font>';
				}

			}

		}

		if($ajax){
			echo json_encode(['html' => $html]);
			exit;
		}
		if($u['gid']==2){
			$member = M('member')->field('p.wechart_id')->join("{$prefix}packet p ON p.id={$prefix}member.packet_id",'LEFT')->where("uid={$u['uid']}")->find();
			$w_ids = unserialize($member['wechart_id']);
			$wechats = array();
			if($w_ids){
				$wechats = M('domain')->where(array('id'=>array('in',$w_ids)))->select();
			}

			$this->assign('wechats',$wechats);
		}

		$page	=	new \Think\Page($count,$pagesize);
		$page = $page->show();
		
		$this->assign('list',$list);
		$this->assign('gid', $u['gid']);
		$this->assign('page',$page);
		$this->assign('u',$u);
		$this->assign('packet_list',array());
		$this->display();

	}

	public function saveWechatSelect()
	{
		$ajax = I('get.ajax', 0);
		$ids = I('get.ids','');
		$id = I('get.id', 0);
		if (!$ajax || !$ids || !$id) {
			echo 'fail';
			exit;
		}

		if (M('member')->where("uid={$id}")->data(array('wechats' => $ids))->save() == false) {
			echo 'fail';
			exit;
		}

		echo 'success';
	}

	public function del()
	{


		$uids = isset($_REQUEST['uids'])?$_REQUEST['uids']:false;

		//uid为1的禁止删除

		if($uids==1 or !$uids){

			$this->error('参数错误！');

		}

		if (is_array($uids)) {

			foreach($uids as $k=>$v){

				if($v==1){//uid为1的禁止删除

					unset($uids[$k]);

				}

				$uids[$k] = intval($v);

			}

			if(!$uids){

				$this->error('参数错误！');

				$uids = implode(',',$uids);

			}

		}


		$map['uid']  = array('in',$uids);

		if(M('member')->where($map)->delete()){

			M('auth_group_access')->where($map)->delete();

			M('promotion_url')->where($map)->delete();

			addlog('删除会员UID：'.$uids);

			$this->success('恭喜，用户删除成功！');

		}else{

			$this->error('参数错误！');

		}

	}

	public function cpstype()
	{

		$cpstype[1]="微信朋友圈";
		$cpstype[2]="微信群分享";
		$cpstype[3]="直链广告二维码";
		return $cpstype;

	}

	public function edit()
	{

		$cpstype=$this->cpstype();
		$this->assign('cpstype',$cpstype);

		$uid = isset($_GET['uid'])?intval($_GET['uid']):false;

		if($uid){

			//$member = M('member')->where("uid='$uid'")->find();

			$prefix = C('DB_PREFIX');

			$user = M('member');

			$member  = $user->field("{$prefix}member.*,{$prefix}auth_group_access.group_id")->join("{$prefix}auth_group_access ON {$prefix}member.uid = {$prefix}auth_group_access.uid")->where("{$prefix}member.uid=$uid")->find();



		}else{

			$this->error('参数错误！');

		}

		$data = $this->getuserid();

		$uid = $data['gid'];

		$usergroup = M('auth_group')->field('id,title')->where("id > $uid")->select();

		$this->assign('usergroup',$usergroup);

		$this->assign('member',$member);
		// var_dump($member);die();

		$this->assign('gid',$data['gid']);

		$this -> display();

	}

	public function update($ajax = '')
	{
		if ($ajax == 'yes') {

			$uid = I('get.uid',0,'intval');

			$gid = I('get.gid',0,'intval');
			/*if($gid != 2){
				$res = M('member')->data(array('packet_id'=>''))->where("uid='$uid'")->save();
			}*/

			M('auth_group_access')->data(array('group_id'=>$gid))->where("uid='$uid'")->save();

			die('1');

		}
		if($ajax == 'packet'){  /////修改微信分组
			$uid = I('get.uid',0,'intval');
			$packet_id = I('get.packet_id',0,'intval');
			$user_name = member($uid,'user');

			/*$res = M('member')->data(array('packet_id'=>$packet_id))->where("uid='$uid' or cid='$uid'")->save();
			if($res){
				addlog('分销商中修改微信分组，UID：'.$uid.'  姓名 '.$user_name['user'],false,1);
				die('1');
			}else{
				die('0');
			}*/

		}

		if($ajax == 'change_puser'){
			$uid = I('get.uid',0,'intval');
			$pId = I('get.pId',0,'intval');
			/*$res = M('member')->data(array('cid'=>$pId,'packet_id' => ''))->where("uid='$uid'")->save();
			if($res){
				die('1');
			}else{
				die('0');
			}*/
		}

		$uid = isset($_POST['uid'])?intval($_POST['uid']):false;

		$user = isset($_POST['user'])?htmlspecialchars($_POST['user'], ENT_QUOTES):'';

		$group_id = isset($_POST['group_id'])?intval($_POST['group_id']):0;

		$u = $this->getuserid();
		if($u['uid'] == $uid){
			$this->error('错误');
		}
		/*if(!$group_id || $group_id<=$u['gid']){

			$this->error('只能建立下级用户！');

		}*/
		if(!$group_id || $group_id<$u['gid']){

			$this->error('不能创建上级用户！');

		}

		$password = isset($_POST['password'])?trim($_POST['password']):false;

		if($password) {

			$data['password'] = password($password);

		}

		$head = I('post.head','','strip_tags');
		$token = password(uniqid(rand(), TRUE));
		$salt = random(10);
		$identifier = password($user['uid'].md5($user['user'].$salt));
		$auth = $identifier.','.$token;
		$data['sex'] = isset($_POST['sex'])?intval($_POST['sex']):0;
		$data['valve'] = intval($_POST['valve'])==1?2:1;
		$data['head'] = $head?$head:'';
		$data['remark'] = $_POST['remark'];
		$data['birthday'] = isset($_POST['birthday'])?strtotime($_POST['birthday']):0;
		$data['phone'] = isset($_POST['phone'])?trim($_POST['phone']):'';
		$data['alipay_number'] = isset($_POST['alipay_number'])?trim($_POST['alipay_number']):'';
		$data['qq'] = isset($_POST['qq'])?trim($_POST['qq']):'';
		//$model->check($_POST['email'],'email');
		$data['email'] = isset($_POST['email'])?trim($_POST['email']):'';
		$data['identifier'] =$identifier;
		$data['token'] = $token;
		$data['salt'] = $salt;
		$data['cps_type'] = $_POST['cps_type'];
      	$data['login_lock'] = isset($_POST['login_lock'])?(empty($_POST['login_lock'])?0:1):1;
		$data['scale'] = isset($_POST['scale']) ? $_POST['scale'] : '70%';

        if($u['gid'] == 1){
            $data['is_begin_kl'] = I('post.is_begin_kl',0,'abs');
            $data['begin_kl_time'] = I('post.begin_kl_time',0,'abs');
            $data['kl_max_s'] = I("post.kl_max_s",0,'abs');
            $data['kl_min_s'] = I("post.kl_min_s",0,'abs');
        }

		if (in_array($u['uid'], C('FINANCE_IDS'))) {
			isset($_POST['wei_number']) && $data['wei_number'] = $_POST['wei_number'];
			isset($_POST['bank_name']) && $data['bank_name'] = $_POST['bank_name'];
			isset($_POST['bank_number']) && $data['bank_number'] = $_POST['bank_number'];
			isset($_POST['bank_user']) && $data['bank_user'] = $_POST['bank_user'];
			isset($_POST['bank_openaddress']) && $data['bank_openaddress'] = $_POST['bank_openaddress'];
			//isset($_POST['alipay_number']) && $data['alipay_number'] = $_POST['alipay_number'];
			isset($_POST['alipay_name']) && $data['alipay_name'] = $_POST['alipay_name'];
		}
		if($u['gid'] == 1){
			$data['enable_deduct'] = empty($_POST['enable_deduct']) ? 0 : 1;
		}
		if($u['gid'] == 2){
			$data['deduct_rate'] = empty($_POST['deduct_rate']) ? 0 : intval($_POST['deduct_rate']);
			if(!is_numeric($data['deduct_rate']) || $data['deduct_rate'] > 10 || $data['deduct_rate'] < 0){
				$this->error('请设置正确的抽成比例!');
			}
		}

		if ($group_id == 2 && !isset($_POST['scale'])) {
			$data['scale'] = "70%";
		}

		if(!$uid){
			$data['cid'] = $u['uid'];
			if($user==''){
				$this->error('用户名称不能为空！');
			}
			if(!$password){
				$this->error('用户密码不能为空！');
			}
			if(M('member')->where("user='$user'")->count()){
				$this->error('用户名已被占用！');
			}
			$data['user'] = trim($user);
			$data['t'] = time();
			/*if($u["gid"]==1){
				$data["packet_id"] = $_POST['packet_id'];
			}elseif($u["gid"]==2){
				$packet_id = M("member")->where("uid=".$u["uid"])->getField("packet_id");
				if($packet_id){
					$data["packet_id"] = $packet_id;
				}
			}*/
			$uid = M('member')->data($data)->add();
			M('auth_group_access')->data(array('group_id'=>$group_id,'uid'=>$uid))->add();

			addlog('新增会员，会员UID：'.$uid);

		}else{
			//M('auth_group_access')->data(array('group_id'=>$group_id))->where("uid=$uid")->save();//根据需要管理员只能创建一级，所以创建成功后用户组不能更改
			
			M('member')->data($data)->where("uid=$uid")->save();

			$Model = M();
			$Model->execute("UPDATE `qw_member` SET remark='".$data['remark']."' WHERE  uid={$uid} ");

			addlog('编辑会员信息，会员UID：'.$uid);
		}

		$this->success('操作成功！');
	}

	//  一级分销商只能添加自己的下级分销商
	public function add()
	{

		$cpstype=$this->cpstype();

		$this->assign('cpstype',$cpstype);

		//  获取用户当前用户ID，权级ID
		$data = $this->getuserid();

		$gid = $data['gid'];

		$strExp = '/^[12]{1}$/';
		preg_match($strExp,$gid,$str);
		if(!$str){
			$this->error('格式错误!');
			exit;
		}

		$rows = M('Member')->field('cid,valve')->where(['uid'=>$data['uid']])->find();

		//$cid = intval($rows['cid']);

		/*$parent = M('member')->field('g.group_id AS gid')
			->join("qw_auth_group_access g ON g.uid=qw_member.uid", 'LEFT')
			->where("qw_member.uid={$cid}")
			->find();*/
		/*if($gid.''=='1' || $parent['gid'].''!=='1'){
			$query ="id > $gid" ;
			$limit = '0,1';
		}else{
			$query = "id >= $gid";
			$limit = '0,2';
		}*/
		if(intval($rows['valve']) !== 2){
			$query ="id > $gid" ;
			$limit = '0,1';
		}else{
			$query = "id >= $gid";
			$limit = '0,2';
		}
		$usergroup = M('auth_group')->field('id,title')->where($query)->order('id ASC')->limit($limit)->select();//16.8.4 只能创建下一级经销商
		$utype=M('member')->where('uid ='.$data['uid'])->find();
		$ucps=$utype['cps_type'];
		$utype['gid'] = $gid;
		/*if($gid==1){
			$packet_api = M('setting')->where('k="packet_api"')->getField('v');
			//接口获取域名分组列表
			$this->packet_list = json_decode($this->httpGet($packet_api),true);
		}*/
		$this->assign('user',$utype);
		$this->assign('ucps',$ucps);
		$this->assign('usergroup',$usergroup);

		$this -> display();

	}

//   根据给定的数组下载excl
	public function do_exs()
	{
		header("Content-type: text/html; charset=utf-8");
		set_time_limit(0);
		$prefix = C('DB_PREFIX');
		$user = $this->getuserid();
		$uid = I('post.uid', 0);
		$ref = '';
		if ($user['gid'] == 1) {
			if ($uid && $uid != $user['uid']) {
				$ref = "ref2_id";
				$where["id"] = $uid;
			} else {
				$ref = "ref1_id";
				// $where["group_id"] ='2';
			}
		} else if ($user['gid'] == 2) {
			$ref = "ref2_id";
			$where["cid"] = $user['uid'];
		} else {
			exit;
		}

        if($uid){
            $member = M('member')->where("uid={$uid}")->find();
            $filename = "{$member['user']}-".date("Ymd",strtotime('-1 days')).'.xls';
        }

        $allmember=I("post.suid");
        $allmember=explode(",",$allmember);

		$temp = array();
		foreach ($allmember as $key) {
			$one = D("member")->where(array("user" => $key))->find();
			array_push($temp, $one["uid"]);
		}

        $where["{$prefix}member.uid"]=array("in",$temp);// 包含uid
        $user = M('member');
        $list  = $user->field("{$prefix}member.*")
            ->join("{$prefix}auth_group_access g ON {$prefix}member.uid = g.uid")
            ->where($where)
            ->order('uid desc')
            ->select();

		$str = "ID\t用户名\t备注\t电话\t微信号\t银行名称\t银行账号\t开户姓名\t开户行\t支付宝用户名\t支付宝账号\t昨日金额\t分成比例(自己)\t\n";
		$str = iconv('utf-8','gb2312',$str);
		if($_GET['startime']){
			$start = strtotime($_GET['startime']);
		}else{
			$start = strtotime(date("Y-m-d",strtotime('-1 days')));
		}
		if($_GET['endtime']){
			$end = strtotime($_GET['endtime']);
		}else{
			$end = strtotime(date("Y-m-d"));
		}
		foreach ($list as $key=>$v) {
            $p_where = "pay_time>={$start} AND pay_time<{$end} AND pay_amount>1 AND account=1";
            $tlist=D("member")->where(array("cid"=>$v["uid"]))->select();
            if(count($tlist)>0) //判断子类是否有值
            {
                foreach($tlist as $keys)
                {
                    $p_where=$p_where." And ref2_id= ".$keys["uid"];
                }
            }else{
                $temp=D("member")->where(array("uid"=>$v["cid"]))->find();//判断是否有上级
                if($temp)
                {
                    !empty($ref) && $p_where .= " AND {$ref}={$temp['uid']}";
                }else{
                    !empty($ref) && $p_where .= " AND {$ref}={$v['uid']}";
                }

            }
            $str.= iconv('utf-8','gb2312',$v['uid'])."\t";
			$v['user']=str_replace("—","-",$v['user']);
			$str.= iconv('utf-8','gb2312',$v['user'])."\t";
			$str.= iconv('utf-8','gb2312',$v['remark'])."\t";
			$str.= iconv('utf-8','gb2312',$v['phone'])."\t";
			$str.= iconv('utf-8','gb2312',$v['wei_number'])."\t";
			$str.= iconv('utf-8','gb2312',$v['bank_name'])."\t";
			$str.= iconv('utf-8','gb2312',$v['bank_number'])."\t";
			$str.= iconv('utf-8','gb2312',$v['bank_user'])."\t";
			$str.= iconv('utf-8','gb2312',$v['bank_openaddress'])."\t";
			$str.= iconv('utf-8','gb2312',$v['alipay_user'])."\t";
			$str.= iconv('utf-8','gb2312',$v['alipay_number'])."\t";
            $monies = M('pay_log')->where($p_where)->sum('pay_amount');
            $income = round($monies * intval($v['scale']) / 100, 2);
            $str.= iconv('utf-8','gb2312',round($monies,2))."\t";
			$str.= iconv('utf-8','gb2312',$v['scale']) ."\t\n";
			//$str.= iconv('utf-8','gb2312',$income)."\t\n";
		}
		$this->exportExcel($filename,$str);
	}


    public function do_ex(){

        header("Content-type: text/html; charset=utf-8");
        set_time_limit(0);

        $prefix = C('DB_PREFIX');
        $user = $this->getuserid();
        $uid = I('get.uid', 0);
        $ref = '';
		//获取超级管理员帐号列表
		$Super_admin_str=implode(',',$this->getSuper());
        if($user['gid'] == 1){
            if($uid && $uid != $user['uid']){
                $ref = "ref2_id";
                $where = "{$prefix}member.cid={$uid}";
            } else {
                $ref = "ref1_id";
                $where = "g.group_id = 2 and {$prefix}member.cid in({$Super_admin_str})";
            }
        } else if($user['gid'] == 2){
            $ref = "ref2_id";
            $where = "{$prefix}member.cid={$user['uid']}";
        } else{
            exit;
        }

        if($uid){
            $member = M('member')->where("uid={$uid}")->find();
            $filename = "{$member['user']}-".date("Ymd",strtotime('-1 days')).'.xls';
        }
		$ref1_ids = I('get.ref1_ids', '');
		if(!empty($ref1_ids) && is_string($ref1_ids)){
			
			//去除非超级管理员创建的一级推荐帐号
			$ref1_ret=M('member')->field("uid")->where(array('uid'=>array('in',$ref1_ids),'cid'=>array('in',$Super_admin_str)))->select();
			$ref1_arr=array();
			foreach($ref1_ret as $value){
				$ref1_arr[]=$value['uid'];
			}
			$ref1_ids=implode(',',$ref1_arr);
		}
		if(!empty($ref1_ids) && is_string($ref1_ids)){
			$filename = "指定用户名-".date("Ymd",strtotime('-1 days')).'.xls';
			$where = array('cid'=>array('in',$ref1_ids));
			$ref = "ref2_id";
		}
		
        $user = M('member');
        $list  = $user->field("{$prefix}member.*,g.group_id")
            ->join("{$prefix}auth_group_access g ON {$prefix}member.uid = g.uid")
            ->where($where)
            ->order('uid desc')
            ->select();
			if($_GET['debug']){
				//echo M('member')->getLastSql();exit;
			}
		//print_r($list);exit;
        $str = "ID\t用户名\t备注\t电话\tQQ\t微信号\t银行名称\t银行账号\t开户姓名\t开户行\t支付宝用户名\t支付宝账号\t金额\t分成比例(自己)\t注册时间\t\n";
        $str = iconv('utf-8','gb2312',$str);
		
		
		if($_GET['startime']){
			$start = strtotime($_GET['startime']);
		}else{
			$start = strtotime(date("Y-m-d",strtotime('-1 days')));
		}
		if($_GET['endtime']){
			$end = strtotime($_GET['endtime']);
		}else{
			$end = strtotime(date("Y-m-d"));
		}
		
		//当其选择了时间段下载时，文件名做调整
		if($_GET['startime'] || $_GET['endtime']){
			$date_interval=date("Ymd",$start).'-'.date("Ymd",$end);
			if($uid){
				$filename = "{$member['user']}-".$date_interval.'.xls';
			}
			if(!empty($ref1_ids) && is_string($ref1_ids)){
				$filename = "指定用户名-".$date_interval.'.xls';
			}
		}
        foreach ($list as $key => $v) {
			$monies=$this->countUserMoney($v,$start,$end,$ref);
			/*$p_where = "pay_time>={$start} AND pay_time<{$end} AND pay_amount>1";
			if($ref == 'ref2_id'){
				$p_where .= " AND {$ref}={$v['uid']} AND account=1";
			} elseif($ref == 'ref1_id'){
				$p_where .= " AND {$ref}={$v['uid']} AND account>=1";
			}
			$monies = M('pay_log')->where($p_where)->sum('pay_amount');
			*/
			
			if(round($monies, 2) > 0) {
				$str .= iconv('utf-8', 'gb2312', $v['uid']) . "\t";
				$v['user'] = str_replace("—", "-", $v['user']);
				$v['user'] = str_replace("-", "-", $v['user']);
				$str .= mb_convert_encoding($v['user'],'gb2312', 'utf-8') . "\t";
				$v['remark'] = str_replace("—", "-", $v['remark']);
				$v['remark'] = str_replace("-", "-", $v['remark']);
				$str .= iconv('utf-8', 'gb2312', $v['remark']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['phone']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['qq']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['wei_number']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['bank_name']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['bank_number']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['bank_user']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['bank_openaddress']) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['alipay_user']) . "\t";
				$str .= iconv('utf-8', 'gb2312', trim($v['alipay_number'])) . "\t";

				$income = round($monies * intval($v['scale']) / 100, 2);
				$str .= iconv('utf-8', 'gb2312', round($monies, 2)) . "\t";
				$str .= iconv('utf-8', 'gb2312', $v['scale']) . "\t";
				$str .= iconv('utf-8', 'gb2312', date("Y-m-d H:i:s", $v['t'])) . "\t\n";
				//$str.= iconv('utf-8','gb2312',$income)."\t\n";
			}
        }

        $this->exportExcel($filename,$str);
    }
	/**
	* 统计用户的总充值
	**/
	private function countUserMoney($v,$start,$end,$ref){
		$prefix = C('DB_PREFIX');
		
		if($v['group_id']==2){//当其为一级推荐的时候
			$user = M('member');
			$list  = $user->field("{$prefix}member.*,g.group_id")
				->join("{$prefix}auth_group_access g ON {$prefix}member.uid = g.uid")
				->where('cid='.$v['uid'])
				->order('uid desc')
				->select();
			
		}
		
		$list[]=$v;
		$monies_sum=0;
		foreach($list as $key=>$value){
			$p_where = "pay_time>={$start} AND pay_time<{$end} AND pay_amount>1";
			if($ref == 'ref2_id' && $v['group_id']==2){
				$p_where .= " AND ref1_id={$value['uid']} AND account=1";
			}else if($ref == 'ref2_id'){
				$p_where .= " AND {$ref}={$value['uid']} AND account=1";
			} elseif($ref == 'ref1_id'){
				$p_where .= " AND {$ref}={$value['uid']} AND account>=1";
			}
			$monies = M('pay_log')->where($p_where)->sum('pay_amount');
			if(!empty($monies)){
				$monies_sum+=$monies;
			}
		}
		return $monies_sum;
	}
	/**
	* 获取超级管理员列表
	**/
	private function getSuper(){
		//获取超级管理员帐号列表
		$Super_admin_list  = M('auth_group_access')->field("uid")->where('group_id=1')->select();
		foreach($Super_admin_list as $key=>$value){
			$Super_admin[]=$value['uid'];
		}
		return $Super_admin;
	}
	private  function exportExcel($filename,$content){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition: attachment; filename=".$filename);
		header("Content-Transfer-Encoding:binary");
		echo $content;
	}

}