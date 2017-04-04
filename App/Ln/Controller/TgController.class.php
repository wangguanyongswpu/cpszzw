<?php

/**

 *

 * 版权所有：

 * 日    期：2016-07-4

 * 版    本：1.1.0

 * 功能说明：渠道推广列表控制器。

 *

 **/

namespace Ln\Controller;

use EasyWeChat\Core\Exception;
use Ln\Controller\ComController;

class TgController extends ComController
{

    public function index()
    {

        $prefix = C('DB_PREFIX');

        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $pagesize = 15;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $limit = $offset . "," . $pagesize;

        $where = "1=1";

        $keyword = empty($_GET['keyword']) ? '' : htmlentities($_GET['keyword']);
        if ($keyword) {
            $where .= " AND {$prefix}member.user like '%{$keyword}%'";
        }

        $regWhere = $pWhere = '';
        $loginuser = $this->getuserid();
        $uid = empty(I('get.uid')) ? 0 : intval(I('get.uid'));
        if (empty($uid)) {
            $user = $this->getuserid();
        } else {
            $user = M('auth_group_access')->field('uid,group_id as gid')->where("uid={$uid}")->find();
        }

        $total = [];
        $ajax = empty(I('get.ajax')) ? 0 : I('get.ajax');
        if ($ajax) {
            $limit = '';
            $where .= " AND {$prefix}member.cid={$user['uid']}";
        } else {
            if ($user['gid'] == 1) {
                $total = [
                    ['uid' => '',
                        'user' => '总计',
                        'gid' => -1
                    ], ['uid' => '',
                        'user' => '未知分销商',
                        'gid' => 0
                    ]
                ];
                $where .= " AND g.group_id=2";
            } else {
                $where .= " AND g.group_id <> 1 AND ({$prefix}member.uid={$user['uid']} OR {$prefix}member.cid={$user['uid']})";
            }
        }

        $date = [
            'start' => isset($_GET['startime']) ? $_GET['startime'] : date('Y-m-d') . " 00:00:00",
            'end' => isset($_GET['endtime']) ? $_GET['endtime'] : ""
        ];
        $startime = strtotime($date['start']);
        $endtime = empty($date['end']) ? 0 : strtotime($date['end']);
        if (!empty($startime) && !empty($endtime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time >= $startime AND {$prefix}register_log.reg_time <= $endtime";
            $pWhere .= " AND {$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time <= $endtime";
        } elseif (!empty($startime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time >= $startime ";
            $pWhere .= " AND {$prefix}pay_log.pay_time >= $startime";
        } elseif (!empty($endtime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time <= $endtime ";
            $pWhere .= " AND {$prefix}pay_log.pay_time <= $endtime";
        }

        $order = "{$prefix}member.uid ASC";
        //$order = "CASE WHEN g.group_id=2 THEN {$prefix}member.uid ELSE {$prefix}member.cid END,{$prefix}member.uid";
        //echo $where;die;
        $count = M('member')->join("{$prefix}auth_group_access g ON {$prefix}member.uid=g.uid", 'LEFT')->where($where)->count();

        $list = M('member')->field("{$prefix}member.uid,{$prefix}member.cid,{$prefix}member.user,{$prefix}member.enable_deduct,g.group_id AS gid")
            ->join("{$prefix}auth_group_access g ON {$prefix}member.uid=g.uid", 'LEFT')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();

        $list = array_merge($total, $list);
        $Super_list = $this->getSuper();//获取超级管理员列表

        $html = '';
        $pWhere .= " AND {$prefix}pay_log.pay_amount > 1";
        foreach ($list as $key => $value) {
            $where = '1=1';
            $ksum = 0;
            $ci_fsum = 0;//次一级推广的资金
            $title_kz = '';
            if ($value['gid'] == -1) {
                $where .= ' AND account>=1';
            } elseif ($value['gid'] == 1) {
                $where .= ' AND account>=1';
            } elseif ($value['gid'] == 2 && !in_array($value['cid'], $Super_list)) {//次一级推广
                $title_kz = '<font color="red">(次一级推广)</font>';
                $where .= " AND account>=1 AND ref1_id={$value['uid']}";
            } elseif ($value['gid'] == 2) {
                $ci_fsum = $this->countUserMoney($value['uid'], $pWhere);//获取次一级下的充值
                $where .= " AND account>=1 AND ref1_id={$value['uid']}";
                $ksum = M('pay_log')->where("account=2 AND ref1_id={$value['uid']}" . $pWhere)->sum('pay_amount');

            } elseif ($value['gid'] == 3) {
                $where .= " AND account=1 AND ref2_id={$value['uid']}";
            } elseif ($value['gid'] === 0) {
                $where .= " AND account>=1 AND ref1_id=0 AND ref1_id='' AND ref1_id IS NULL";
            }

            $rcount = M('register_log')->where($where . $regWhere)->count();
            $pcount = M('pay_log')->where($where . $pWhere)->count();
            $fsum = M('pay_log')->where($where . $pWhere)->sum('pay_amount');

            $fsum += $ci_fsum;//总金额等与 帐号二级推广加上次一级推广的资金
            if ($ajax) {
                empty($fsum) && $fsum = 0;
                $html .= '<tr class="child_' . $user['uid'] . '">';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;' . $value['uid'] . '</td>';
                $html .= '<td>&nbsp;&nbsp;--&nbsp;' . $value['user'] . $title_kz . '</td>';
                $html .= '<td>' . $rcount . '</td>';
                $html .= '<td>' . $pcount . '</td>';
                $html .= '<td>&yen;' . $fsum . '</td>';
                $html .= '</tr>';
            } else {
                $child_count = 0;
                if ($user['uid'] != $value['uid'] && $value['gid'] > 1) {
                    $child_count = M('member')->where("cid={$value['uid']}")->count();
                }
                $list[$key]['rcount'] = $rcount;
                $list[$key]['pcount'] = $pcount;
                $list[$key]['fsum'] = $fsum == null ? 0 : $fsum;
                $list[$key]['ksum'] = empty($ksum) ? 0 : $ksum;
                $list[$key]['has_child'] = empty($child_count) ? false : true;
            }
        }
        //echo M('member')->getLastSql();exit;
        if ($ajax) {
            echo json_encode(['html' => $html]);
            exit;
        }


        $page = new \Think\Page($count, $pagesize);

        $page = $page->show();
        $this->assign('gid', $user['gid']);
        $this->assign('list', $list);
        $this->assign('date', $date);
        $this->assign('page', $page);

        $this->display();
    }
	
	public function user()
    {

        $prefix = C('DB_PREFIX');

        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $pagesize = 15;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $limit = $offset . "," . $pagesize;

        $where = "1=1";

        $keyword = empty($_GET['keyword']) ? '' : htmlentities($_GET['keyword']);
        if ($keyword) {
            $where .= " AND {$prefix}member.user like '%{$keyword}%'";
        }

        $regWhere = $pWhere = '';
        $loginuser = $this->getuserid();
        $uid = empty(I('get.uid')) ? 0 : intval(I('get.uid'));
        if (empty($uid)) {
            $user = $this->getuserid();
        } else {
            $user = M('auth_group_access')->field('uid,group_id as gid')->where("uid={$uid}")->find();
        }

        $total = [];
        $ajax = empty(I('get.ajax')) ? 0 : I('get.ajax');
        if ($ajax) {
            $limit = '';
            $where .= " AND {$prefix}member.cid={$user['uid']}";
        } else {
            if ($user['gid'] == 1) {
                $total = [
                    ['uid' => '',
                        'user' => '总计',
                        'gid' => -1
                    ], ['uid' => '',
                        'user' => '未知分销商',
                        'gid' => 0
                    ]
                ];
                $where .= " AND g.group_id=2";
            } else {
                $where .= " AND g.group_id <> 1 AND ({$prefix}member.uid={$user['uid']} OR {$prefix}member.cid={$user['uid']})";
            }
        }

        $date = [
            'start' => isset($_GET['startime']) ? $_GET['startime'] : date('Y-m-d') . " 00:00:00",
            'end' => isset($_GET['endtime']) ? $_GET['endtime'] : ""
        ];
        $startime = strtotime($date['start']);
        $endtime = empty($date['end']) ? 0 : strtotime($date['end']);
        if (!empty($startime) && !empty($endtime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time >= $startime AND {$prefix}register_log.reg_time <= $endtime";
            $pWhere .= " AND {$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time <= $endtime";
        } elseif (!empty($startime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time >= $startime ";
            $pWhere .= " AND {$prefix}pay_log.pay_time >= $startime";
        } elseif (!empty($endtime)) {
            $regWhere .= " AND {$prefix}register_log.reg_time <= $endtime ";
            $pWhere .= " AND {$prefix}pay_log.pay_time <= $endtime";
        }

        $order = "{$prefix}member.uid ASC";
        //$order = "CASE WHEN g.group_id=2 THEN {$prefix}member.uid ELSE {$prefix}member.cid END,{$prefix}member.uid";
        //echo $where;die;
        $count = M('member')->join("{$prefix}auth_group_access g ON {$prefix}member.uid=g.uid", 'LEFT')->where($where)->count();

        $list = M('member')->field("{$prefix}member.uid,{$prefix}member.cid,{$prefix}member.user,{$prefix}member.enable_deduct,g.group_id AS gid")
            ->join("{$prefix}auth_group_access g ON {$prefix}member.uid=g.uid", 'LEFT')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();

        $list = array_merge($total, $list);
        $Super_list = $this->getSuper();//获取超级管理员列表

        $html = '';
        $pWhere .= " AND {$prefix}pay_log.pay_amount > 1";
        foreach ($list as $key => $value) {
            $where = '1=1';
            $ksum = 0;
            $ci_fsum = 0;//次一级推广的资金
            $title_kz = '';
            if ($value['gid'] == -1) {
                $where .= ' AND account>=1';
            } elseif ($value['gid'] == 1) {
                $where .= ' AND account>=1';
            } elseif ($value['gid'] == 2 && !in_array($value['cid'], $Super_list)) {//次一级推广
                $title_kz = '<font color="red">(次一级推广)</font>';
                $where .= " AND account>=1 AND ref1_id={$value['uid']}";
            } elseif ($value['gid'] == 2) {
                $ci_fsum = $this->countUserMoney($value['uid'], $pWhere);//获取次一级下的充值
                $where .= " AND account>=1 AND ref1_id={$value['uid']}";
                $ksum = M('pay_log')->where("account=2 AND ref1_id={$value['uid']}" . $pWhere)->sum('pay_amount');

            } elseif ($value['gid'] == 3) {
                $where .= " AND account=1 AND ref2_id={$value['uid']}";
            } elseif ($value['gid'] === 0) {
                $where .= " AND account>=1 AND ref1_id=0 AND ref1_id='' AND ref1_id IS NULL";
            }

            $rcount = M('register_log')->where($where . $regWhere)->count();
            $pcount = M('pay_log')->where($where . $pWhere)->count();
            $fsum = M('pay_log')->where($where . $pWhere)->sum('pay_amount');

            $fsum += $ci_fsum;//总金额等与 帐号二级推广加上次一级推广的资金
            if ($ajax) {
                empty($fsum) && $fsum = 0;
                $html .= '<tr class="child_' . $user['uid'] . '">';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;' . $value['uid'] . '</td>';
                $html .= '<td>&nbsp;&nbsp;--&nbsp;' . $value['user'] . $title_kz . '</td>';
                $html .= '<td>' . $rcount . '</td>';
                $html .= '<td>' . $pcount . '</td>';
                $html .= '<td>&yen;' . $fsum . '</td>';
                $html .= '</tr>';
            } else {
                $child_count = 0;
                if ($user['uid'] != $value['uid'] && $value['gid'] > 1) {
                    $child_count = M('member')->where("cid={$value['uid']}")->count();
                }
                $list[$key]['rcount'] = $rcount;
                $list[$key]['pcount'] = $pcount;
                $list[$key]['fsum'] = $fsum == null ? 0 : $fsum;
                $list[$key]['ksum'] = empty($ksum) ? 0 : $ksum;
                $list[$key]['has_child'] = empty($child_count) ? false : true;
            }
        }
        //echo M('member')->getLastSql();exit;
        if ($ajax) {
            echo json_encode(['html' => $html]);
            exit;
        }


        $page = new \Think\Page($count, $pagesize);

        $page = $page->show();
        $this->assign('gid', $user['gid']);
        $this->assign('list', $list);
        $this->assign('date', $date);
        $this->assign('page', $page);

        $this->display();
    }
    /**
     * 导出收益
     */
    public function export_earnings(){
        $prefix = C('DB_PREFIX');
        $users = $this->getuserid();
        if($users['gid'] != 1){
            $this->error('没有权限!');exit;
        }
        $get = I('get.','','trim');
        $where = " account>=1 ";
        if($get['stime']){
            $stime = strtotime($get['stime']);
            $where .= " and pay_time >= {$stime} ";
        }
        if($get['etime']){
            $etime = strtotime($get['etime']);
            $where .= " and pay_time< {$etime} ";
        }

        $memberList = M('member')->join("{$prefix}auth_group_access as g on g.uid={$prefix}member.uid",'left')
            ->field("{$prefix}member.uid,g.group_id as gid,user,alipay_number,scale,phone,t,cid")
            ->where("g.group_id=2")->select();
        $str = "推广商ID\t推广商名称\t电话\t支付宝账号\t金额\t分成比例\t注册时间\t\n";
        //  $str .= iconv('utf-8','gb2312',$str);

        $Super_list = $this->getSuper();//获取超级管理员列表
        $list = [];
        foreach ($memberList as $k=>$v){
            $money = M('pay_log')->where($where ." and ref1_id = {$v['uid']} ")->sum('pay_amount');
            if($money >0){
                $v['money'] = $money;
                if(!in_array($v['cid'],$Super_list)){//次一级
                    if(!isset($list[$v['cid']])){
                        /* $parent = M('member')->join("{$prefix}auth_group_access as g on g.uid={$prefix}member.uid",'left')
                             ->field("{$prefix}member.uid,g.group_id as gid,user,alipay_number,scale,phone,t,cid")
                             ->where("g.group_id=2 and {$prefix}member.uid={$v['cid']}")->find();*/
                        $parent = M('member')->where(['uid' => $v['cid']])->find();

                        $list[$v['cid']] = $parent;
                    }
                }
                $list[$v['uid']] = $v;
            }
        }

        foreach ($list as $v){
            if( isset($list[$v['cid']])){
                $list[$v['cid']]['money'] += $v['money'];
                $list[$v['uid']]['user'] = $v['user']."(次一级推广商)上级推广商是({$list[$v['cid']]['user']})";
            }
        }

        foreach ($list as $v){
            $t = $v['t'] ? date('Y-m-d',$v['t']) : 0;
            $str  .= "{$v['uid']}\t{$v['user']}\t{$v['phone']}\t{$v['alipay_number']}\t{$v['money']}\t{$v['scale']}\t{$t}\t\n";
        }

        $total = M('pay_log')->where($where)->sum('pay_amount');
        $footer = "总计:\t{$total}\t\n";
        $str .= $footer;
        $s = $get['stime'] ? $get['stime'] : date('Y-m-d',time());
        $e = $get['etime'] ? $get['etime'] : date('Y-m-d',(time()+3600*24) );
        $filename = "{$s}--{$e}疯狂赚收益数据表.xls";
        $this->exportExcel($filename,$str);

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

    /*
    *@注册详细日志
    *
    */
    public function register()
    {

        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';

        $prefix = C('DB_PREFIX');

        $pagesize = 10;#每页数量

        //$field = isset($_GET['field'])?$_GET['field']:'';

        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

        $ref = isset($_GET['ref']) ? $_GET['ref'] : '';

        //$level = isset($_GET['level'])?$_GET['level']:'()';

        $startime = strtotime($_GET['startime']);

        $endtime = strtotime($_GET['endtime']);

        $where = '1=1 ';


        $user = $this->getuserid();

        if ($user['gid'] == 1) {

            $where .= '';

        } elseif ($user['gid'] == 2) {//一级渠道

            $where .= " AND {$prefix}register_log.ref1_id={$user['uid']}";

        } elseif ($user['gid'] == 3) {//二级渠道

            $where .= " AND {$prefix}register_log.ref2_id={$user['uid']}";

        }


        $order = "{$prefix}register_log.id desc";


        /*if($keyword <>''){

            if($field=='mobile'){

                $where .= " AND {$prefix}register_log.mobile LIKE '%$keyword%' ";

            }

            if($field=='nicheng'){

                $where .= " AND {$prefix}register_log.username LIKE '%$keyword%' ";

            }

            if($field=='resideprovince'){

                $where .= " AND {$prefix}register_log.resideprovince LIKE '%$keyword%' ";

            }

        }*/

        if ($keyword <> '') {

            $where .= " AND {$prefix}register_log.username LIKE '%$keyword%' ";

        }


        if (!empty($startime) && !empty($endtime)) {

            $where .= " AND {$prefix}register_log.reg_time >= $startime AND {$prefix}register_log.reg_time <= $endtime";


        } elseif (!empty($startime)) {

            $where .= " AND {$prefix}register_log.reg_time >= $startime ";

        } elseif (!empty($endtime)) {

            $where .= " AND {$prefix}register_log.reg_time <= $endtime ";

        }


        /*if($level == '2'){

            $where .= " AND qw_auth_group_access.group_id = 2 ";

        }elseif($level == '3'){

            $where .= " AND qw_auth_group_access.group_id = 3 ";

        }*/


        if (!empty($ref)) {
            $where .= " AND ({$prefix}register_log.ref1_name like '%{$ref}%' OR {$prefix}register_log.ref2_name like '%{$ref}%')";
        }

        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = M('register_log')->field("{$prefix}register_log.*")
            ->where($where)
            ->count();

        $list = M('register_log')->field("{$prefix}register_log.*")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->order($order)
            ->select();

        //echo M('Ln.jy_ppp_member','ims_')->getLastSql();exit;

        $sex = ['0' => '保密', '1' => '男', '2' => '女'];

        $type = ['1' => '微信', '2' => 'QQ', '3' => '网站注册'];

        foreach ($list as $key => $value) {

            $list[$key]['reg_time'] = date("Y年m月d日 H:i", $value['reg_time']);

            $list[$key]['user'] = $value['ref2_name'] ? $value['ref2_name'] : $value['ref1_name'];

            $list[$key]['sex'] = $sex[$value['sex']];

            $list[$key]['type'] = $type[$value['reg_type']];

        }


        $page = new \Think\Page($count, $pagesize);

        $page = $page->show();

        $this->assign('list', $list);

        $this->assign('page', $page);

        $this->display();


    }

    /**
     * 今日昨日统计
     */
    public function total()
    {
        header("Content-type: text/html; charset=utf-8");

        $arr = [
            [
                'title' => '今天',
                'd_where' => ' AND pay_time>=' . strtotime(date('Y-m-d')),
            ],
            [
                'title' => '昨天',
                'd_where' => ' AND pay_time>=' . strtotime(date('Y-m-d', strtotime('-1 day'))) . ' AND pay_time<' . strtotime(date('Y-m-d')),
            ],
            [
                'title' => '前天',
                'd_where' => ' AND pay_time>=' . strtotime(date('Y-m-d', strtotime('-2 day'))) . ' AND pay_time<' . strtotime(date('Y-m-d', strtotime('-1 day'))),
            ]
        ];
        echo "直链推广:<br>";
        foreach ($arr as $v) {
            echo $v['title'] . "<br>";

            $where = 'pay_amount>1' . $v['d_where'];
            $sum = M('pay_log')->where($where)->sum('pay_amount');
            //	echo M('pay_log')->getLastSql();
            //		echo "<br/>";
            $k_sum = M('pay_log')->where($where . ' AND account=0')->sum('pay_amount');
            $u_sum = M('pay_log')->where($where . ' AND account=-1')->sum('pay_amount');
            $n_sum = M('pay_log')->where($where . ' AND account>=1')->sum('pay_amount');

            echo '参数丢失：' . $u_sum . '<br>';
            echo '扣量：' . $k_sum . '<br>';
            echo '未扣量：' . $n_sum . '<br>';
            echo '合计：' . $sum . '<br><br><br>';
        }
    }

    public function checkdomain()
    {
        $domain = M('domain');
        $domainlist = $domain->field('*')->where(array('look' => '2', 'type' => '0'))->select();
        $html = "";
        foreach ($domainlist as $val) {
            $html = "<label>";
            $html = "<input type='radio' value=" . $val['id'] . " name='domain'>";
            $html .= "<span>{$val[url]}</span>";
            $html .= "</label>";
        }
        echo $html;
    }

    public function check_wechat()
    {
        die;
        $we_id = I('get.we_id', 0, 'intval');
        $domain = M('domain');
        $olddomain = $domain->field('*')->where(array('id' => $we_id))->find();
        $url = "http://" . $olddomain['url'];
        // if( $this->chackurl($url)  == false){
        $chat_data = $olddomain->field('id')->where(array('look' => '2', 'type' => '0'))->order('id')->find();
        if ($chat_data) {
            $packet = M('packet')->field("wechart_id,id")->select();
            foreach ($packet as $index => $v) {/// 替换异常的微信
                $wechart_arr = unserialize($v['wechart_id']);
                if (in_array($wechat['id'], $wechart_arr)) {
                    $pet_key = array_search($wechat['id'], $wechart_arr);
                    $wechart_arr[$pet_key] = $chat_data['id'];
                    M('packet')->data(array('wechart_id' => serialize($wechart_arr)))->where(array("id" => $v['id']))->save();
                }
            }
            $wmodel->data(array('status' => 1))->where(array("id" => $chat_data['id']))->save(); //改变新账号状态
            $wmodel->data(array('status' => 2))->where(array("id" => $wechat['id']))->save();//改变旧账号状态
            $back_arr = array('status' => 1, 'msg' => '替换成功！');
        } else {
            $back_arr = array('status' => 1, 'msg' => '暂无备用号！');
        }
        /* }else{
             $back_arr = array('status'=>0,'msg' => '此微信号可用！');
         }*/

        echo json_encode($back_arr);

    }

    /*
    *@充值详细日志
    *
    */
    public function recharge()
    {

        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $prefix = C('DB_PREFIX');
        $pagesize = 15;#每页数量
        //$field = isset($_GET['field'])?$_GET['field']:'';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'qw_pay_log.pay_time DESC';
        $ref = isset($_GET['ref']) ? $_GET['ref'] : '';
        //$level = isset($_GET['level'])?$_GET['level']:'()';
        $startime = strtotime($_GET['startime']);
        $endtime = strtotime($_GET['endtime']);
        $where = "1=1";
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $user = $this->getuserid();
        if ($user['gid'] == 1) {
            $where .= '';
        } elseif ($user['gid'] == 2) {//一级渠道
            $where .= " AND {$prefix}pay_log.ref1_id={$user['uid']}";
        } elseif ($user['gid'] == 3) {//二级渠道
            $where .= " AND {$prefix}pay_log.ref2_id={$user['uid']}";
        }
        $order = "{$prefix}pay_log.id desc";
        if ($keyword <> '') {
            $where .= " AND {$prefix}pay_log.username LIKE '%$keyword%' ";
        }
        if (!empty($startime) && !empty($endtime)) {
            $where .= " AND {$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time <= $endtime";
        } elseif (!empty($startime)) {
            $where .= " AND {$prefix}pay_log.pay_time >= $startime ";
        } elseif (!empty($endtime)) {
            $where .= " AND {$prefix}pay_log.pay_time <= $endtime ";
        }
        if (!empty($ref)) {
            $where .= " AND ({$prefix}pay_log.ref1_name like '%{$ref}%' OR {$prefix}pay_log.ref2_name like '%{$ref}%')";
        }
        $where .= " AND {$prefix}pay_log.pay_amount > 1";
        $count = M('pay_log')->field("{$prefix}pay_log.*")
            ->where($where)
            ->count();
        $list = M('pay_log')->field("{$prefix}pay_log.*")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            //->order($order)
            ->order('qw_pay_log.pay_time DESC')
            ->select();
//echo M('pay_log')->getLastSql();
        //echo M('love.jy_ppp_pay_log','ims_')->getLastSql();exit;
        $log = ['1' => '购买虚拟货币', '2' => '购买包月服务', '3' => '购买收信宝'];
        foreach ($list as $key => $value) {
            $list[$key]['pay_time'] = $value['pay_time'] ? date("Y年m月d日 H:i", $value['pay_time']) : '';
            if(empty($value['ref1_name']) && empty($value['ref2_name'])){
                $refid = $value['ref1_id'] ? $value['ref1_id'] : ($value['ref2_id'] ? $value['ref2_id'] : '无id');
                $list[$key]['user'] = "推广商不存在(".$refid.")";
            }else{
                $list[$key]['user'] = $value['ref2_name'] ? $value['ref2_name'] : $value['ref1_name'];
            }
            $list[$key]['log'] = $log[$value['pay_type']];
        }

        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    /*
   *@订单故障排查
   *
   */
    public function lostorder()
    {
        $search = [];
        $rows = [];
        if (IS_POST) {
            $post = I('post.','','trim');
            $strExp = '/^[a-zA-Z0-9]+$/';
            if($post['pay_list']) {
                preg_match($strExp, $post['pay_list'], $str);
                if (!$str) {
                    $this->error('单号格式不正确！', U('lostorder'));
                    exit;
                }
            }
            $where = "1=1 ";
            if($post['pay_list']) {
                if ($post['pay'] == 1) {
                    $where .= " and  pay_serial like '%{$post['pay_list']}%' ";
                } elseif ($post['pay'] == 2) {
                    $where .= " and pay_channel_serial like '%{$post['pay_list']}%' ";
                }
            }
            if($post['ref_id']){
                $refid = $post['ref_id'];
                if( !is_numeric($refid) ){
                    $this->error('推广商填数字!');exit;
                }
                $where .= " and (ref1_id = {$refid} or ref2_id = {$refid} ) ";
            }
            if($post['stime']){
                $stime = strtotime($post['stime']);
                $where .= " and pay_time > {$stime} ";
            }
            if($post['etime']){
                $etime = strtotime($post['etime']);
                $where .= " and pay_time < {$etime} ";
            }
            if($post['id']){
                $where = ['id' => $post['id'] ];
            }
            $rows = M('pay_log')->where($where)->find();
            if (!$rows) {
                $this->error('订单不存在！', U('lostorder'));
                exit;
            }
            $rows['pay_time'] = $rows['pay_time'] ? date("Y-m-d H:i:s", $rows['pay_time']) : '';
            $account['1'] = "没扣";
            $account['0'] = "扣量";
            $account['-1'] = "数据丢失";

            $rows['kouliang'] = $account[$rows['account']];
            $search = [
                'pay' => $post['pay'],'pay_list' => $post['pay_list'] , 'ref_id' => $post['ref_id'] , 'stime' => $post['stime'] , 'etime' => $post['etime']
            ];

        }
        $this->assign('search',$search);
        $this->assign('order', $rows);
        $this->display();
    }

    /**
     * 修改扣量
     */
    public function updateaccount(){
        $id = I('param.id','');
        $post = I('post.','','trim');
        $order = M('pay_log')->find($id);
        if(!$id){
            $this->error('参数错误!');exit;
        }
        if(IS_POST){
            if( !$post['ref0_id'] && !$post['ref1_id'] && !$post['ref2_id'] ){
                $this->error('参数错误!');exit;
            }

            $ref1_result = M('member')->field('uid,user,cid')->where(['uid' => $post['ref1_id']])->find();
            $update = [
                'ref1_id' => $post['ref1_id'],
                'ref1_name' => $ref1_result['user'],
                'account'   => $post['account']
            ];
            if($post['ref0_id'] && $post['ref1_id'] ){
                if($post['ref0_id'] != $ref1_result['uid']){
                    $this->error('推广商id填写错误!');exit;
                }
                $ref0_name = M('member')->where(['uid' => $post['ref0_id']])->getField('user');
                $update['ref0_id'] = $post['ref0_id'];
                $update['ref0_name'] = $ref0_name;
            }

            if($post['ref1_id'] && $post['ref2_id']){
                $ref2_result = M('member')->field('uid,user,cid')->where(['uid' => $post['ref2_id']])->find();
                if($post['ref1_id'] != $ref2_result['cid']){
                    $this->error('推广商id填写错误!');exit;
                }
                $update['ref2_id'] = $post['ref2_id'];
                $update['ref2_name'] = $ref2_result['user'];
            }
            if(!$post['ref2_id']){
                $update['ref2_id'] = 0;
                $update['ref2_name'] = '';
            }

            $ures = M('pay_log')->where(['id' => $id])->save($update);
            if( $ures  ){
                $this->success('修改成功!');exit;
            }
            $this->error('修改失败!');exit;
        }
        $this->assign('order',$order);
        $this->display();

    }

    /*
  *@订单故障排查
  *
  */
    public function orderaccount()
    {

        $data = I('param.','');
        if (isset($data)) {
            $where = array("id" => $data['id']);

            if (isset($data["account"])) {
                $save["account"] = $data["account"];
            }

            if ($data["refid"]) {
                $member = M('member')->field("*")
                    ->where("uid = " . $data["refid"])
                    ->find();
                if ($member['cid']) {
                    $pmember = M('member')->field("*")
                        ->where("uid = " . $member["cid"])
                        ->find();
                }
                if (!$pmember) {
                    $this->success('请输入二级经销商');
                    exit;
                }

                $save["ref2_id"] = $member["uid"];
                $save["ref2_name"] = $member["user"];

                $save["ref1_id"] = $pmember["uid"];
                $save["ref1_name"] = $pmember["user"];

            }

            $order = M('pay_log')
                ->where($where)
                ->data($save)
                ->save();

            if ($order) {
                $this->success('修改成功');
                exit;
            }
        }
        $this->success('修改失败');
    }

    /**
     * 测试充值日志
     */
    public function test_pay()
    {
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $prefix = C('DB_PREFIX');
        $pagesize = 10;#每页数量
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $ref = isset($_GET['ref']) ? $_GET['ref'] : '';
        $startime = strtotime($_GET['startime']);
        $endtime = strtotime($_GET['endtime']);
        $where = "1=1";

        $offset = $pagesize * ($p - 1);//计算记录偏移量

        $user = $this->getuserid();

        if ($user['gid'] == 1) {
            $where .= '';
        } elseif ($user['gid'] == 2) {//一级渠道
            $where .= " AND {$prefix}pay_log.ref1_id={$user['uid']}";
        } elseif ($user['gid'] == 3) {//二级渠道
            $where .= " AND {$prefix}pay_log.ref2_id={$user['uid']}";
        }

        $order = "{$prefix}pay_log.id desc";

        if ($keyword <> '') {
            $where .= " AND {$prefix}pay_log.username LIKE '%$keyword%' ";
        }

        if (!empty($startime) && !empty($endtime)) {
            $where .= " AND {$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time <= $endtime";
        } elseif (!empty($startime)) {
            $where .= " AND {$prefix}pay_log.pay_time >= $startime ";
        } elseif (!empty($endtime)) {
            $where .= " AND {$prefix}pay_log.pay_time <= $endtime ";
        }

        if (!empty($ref)) {
            $where .= " AND ({$prefix}pay_log.ref1_name like '%{$ref}%' OR {$prefix}pay_log.ref2_name like '%{$ref}%')";
        }

        $where .= " AND {$prefix}pay_log.pay_amount <= 1";

        $count = M('pay_log')->field("{$prefix}pay_log.*")
            ->where($where)
            ->count();

        $list = M('pay_log')->field("{$prefix}pay_log.*")
            ->where($where)
            ->limit($offset . ',' . $pagesize)
            ->order($order)
            ->select();

        $log = ['1' => '购买虚拟货币', '2' => '购买包月服务', '3' => '购买收信宝'];
        foreach ($list as $key => $value) {
            $list[$key]['pay_time'] = $value['pay_time'] ? date("Y年m月d日 H:i", $value['pay_time']) : '';
            $list[$key]['user'] = $value['ref2_name'] ? $value['ref2_name'] : $value['ref1_name'];
            $list[$key]['log'] = $log[$value['pay_type']];
        }

        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display('recharge');
    }

    /**
     * @域名检测
     */
    public function urllist()
    {

        $user = $this->getuserid();

        if (I('user') && $user['gid'] == 1) {
            $usergroupaccess = M('auth_group_access')->field('uid,group_id')->where("uid=" . I('user'))->find();
            $user = array('uid' => I('user'), 'gid' => $usergroupaccess['group_id']);
        }
        $member = M('member')->where("uid={$user['uid']}")->find();
        $cpstype = $member['cps_type'];

        //dump($member);exit;
        $prefix = C('DB_PREFIX');

        $api = A('Api');
        //$api->updateJumpDomain();
        //$api->updatemoreDomain(1);
        //$api->updatemoreDomain(4);
        $Mdomain = M('domain');
        $moive_domain = $Mdomain->field('url')->where('type=0 AND look < 3')->select();
        $skip_domain = $Mdomain->field('url')->where('type=2 AND look < 3')->select();
        $group_domain = $Mdomain->field('url')->where('type=3 AND look < 3')->select();
        $frend_domain = $Mdomain->field('url')->where('type=4 AND look < 3')->select();
        foreach ($skip_domain as $k => $v) {
            $skip_domain[$k]['chk'] = $this->chkUrl($v['url']);
        }
        foreach ($group_domain as $k => $v) {
            $group_domain[$k]['chk'] = $this->chkUrl($v['url']);
        }
        foreach ($frend_domain as $k => $v) {
            $frend_domain[$k]['chk'] = $this->chkUrl($v['url']);
        }
        foreach ($moive_domain as $v) {
            $host = $v['url'];//current(explode('/',$v['url']));
            $path = dirname(APP_PATH);
            $url = 'http://' . $v['url'] . '/' . date('YmdHis') . '/' . $user['uid'] . '.gif';
            $t_path = '/Public/Ln/images/';
            $filename = 'chenqrcode/' . $v['url'] . '/' . $user['uid'] . '_' . $v['url'] . '.png';

            if (file_exists($path . $t_path . $filename)) {
                $img_url = $t_path . $filename;
            } else {
                $img_url = '';
            }
            $share_url = C('SHARE_URL') . "?uid={$user['uid']}&url={$host}";
            $domain[] = array('url' => $url, 'img_url' => $img_url, 'share_url' => $share_url, 'source_url' => $v['url'], 'churl' => $user['uid'] . '_' . $v['url'], 'chk' => $this->chkUrl($v['url']));
        }


        $generalize = $domain['0'];

        $url = C("MOIVE_API_HOST") . "index.php/index/cache/getimgl.html?pic=" . $_SERVER['HTTP_HOST'] . $generalize['img_url'] . "&url=" . $generalize['churl'];
        $res = $this->httpGet($url);
        $tgqc = json_decode($res, true);
        $generalize['qr_url'] = base64_encode(urlencode('http://' . $_SERVER['HTTP_HOST'] . $generalize['img_url']));

        $maxcount = max(count($frend_domain), count($group_domain), count($moive_domain));

        $this->assign('skip_domain', $skip_domain);
        $this->assign('domain', $domain);
        $this->assign('cpstype', $cpstype);
        $this->assign('user', $user);
        $this->assign('tgqc', $tgqc['url']);
        $this->assign('maxcount', $maxcount);
        $this->assign('generalize', $generalize);
        $this->assign('frend_domain', $frend_domain);
        $this->assign('group_domain', $group_domain);
        $this->assign('moive_domain', $moive_domain);

        $this->display();
    }

    public function chkUrl($url)
    {
        $_url = "http://www.yundq.cn/url/wx?key=693E6C9885DC4231&url=" . $url;
        $chk = explode(' ', $this->httpGet($_url));
        return ($chk[1] == '黑名单') ? '<font color="red">' . $chk[1] . '</font>' : $chk[1];
    }

    /**
     * @域名
     */
    public function qrlist()
    {
        $user = $this->getuserid();

        if (I('user') && $user['gid'] == 1) {
            $usergroupaccess = M('auth_group_access')->field('uid,group_id')->where("uid=" . I('user'))->find();
            $user = array('uid' => I('user'), 'gid' => $usergroupaccess['group_id']);
        }

        $prefix = C('DB_PREFIX');
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $where = '1=1';

        $domains = [];
        $type = C('PACKET_TYPE') ? C('PACKET_TYPE') : 0;
        if ($user['gid'] == 1) {
            $where .= ' and g.group_id =3 ';
            $domains = M('domain')->where(array('type' => $type, 'look' => 0))->select();
        } elseif ($user['gid'] == 2) {//一级渠道
            $domain_arr = M('packet')->where("m.uid={$user['uid']}")->join("{$prefix}member m ON m.packet_id=id", "LEFT")->getField('wechart_id');
            $domains = M('domain')->where(array('id' => array('in', unserialize($domain_arr)), 'look' => 0))->select();
            if (count($domains) < 1) {
                $domains = M('domain')->where(array('type' => $type, 'look' => 0))->order('rand()')->limit(1)->select();
            }

            $where .= " AND {$prefix}member.cid={$user['uid']}";
        } elseif ($user['gid'] == 3) {//二级渠道
            $member = M('member')->where("uid={$user['uid']}")->find();
            $cpstype = $member['cps_type'];
            $domains = M('domain')->where(array('id' => array('in', empty($member['wechats']) ? '' : $member['wechats']), 'look' => 0))->select();
            if (count($domains) < 1) {
                $domain_arr = M('packet')->where("m.uid={$member['cid']}")->join("{$prefix}member m ON m.packet_id=id", "LEFT")->getField('wechart_id');
                $domains = M('domain')->where(array('id' => array('in', empty(unserialize($domain_arr)) ? '' : unserialize($domain_arr)), 'look' => 0))->select();
                if (count($domains) < 1) {
                    $domains = M('domain')->where(array('type' => $type, 'look' => 0))->order('rand()')->limit(1)->select();
                }
            }

            $where .= " AND {$prefix}member.uid={$user['uid']}";
        }
        $where .= ' AND g.group_id<>1';

        $pagesize = 20;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = M('member')->join("{$prefix}auth_group_access as g ON {$prefix}member.uid = g.uid")->where($where)->count();

        $list = M('member')->field("{$prefix}member.uid,{$prefix}member.user,{$prefix}member.packet_id,g.group_id as gid")
            ->join("{$prefix}auth_group_access as g ON {$prefix}member.uid = g.uid")
            ->where($where)
            ->order('uid')
            ->limit($offset . ',' . $pagesize)
            ->select();

        foreach ($list as $key => $val) {
            $domain = [];
            foreach ($domains as $v) {
                $host = $v['url'];//current(explode('/',$v['url']));
                $path = dirname(APP_PATH);
                $url = 'http://' . $v['url'] . '/' . date('YmdHis') . '/' . $val['uid'] . '.gif';
                $t_path = '/Public/Ln/images/';
                $filename = 'chenqrcode/' . $v['url'] . '/' . $val['uid'] . '_' . $v['url'] . '.png';

                if (file_exists($path . $t_path . $filename)) {
                    $img_url = $t_path . $filename;
                } else {
                    $img_url = '';
                    if ($user['gid'] == 3) {
                        $img_url = $this->QrCode($filename, $url);;
                    }
                }
                $share_url = C('SHARE_URL') . "?uid={$val['uid']}&url={$host}";
                $domain[] = array('url' => $url, 'img_url' => $img_url, 'share_url' => $share_url, 'source_url' => $v['url'], 'churl' => $val['uid'] . '_' . $v['url']);
            }
            $list[$key]['qrcode'] = $domain;
        }
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();

        $this->assign('domain', $domains);
        //print_r($domains);die;

        $this->assign('cpstype', $cpstype);
        $this->assign('list', $list);
        $this->assign('user', $user);
        $this->assign('page', $page);
//print_r($list['0']['qrcode']['0']);die;
        if ($user['gid'] != 1) {

            $api = A('Api');
            //$api->updateJumpDomain();
            //$api->updatemoreDomain(1);
            //$api->updatemoreDomain(4);
            $Mdomain = M('domain');
            $moive_domain = $Mdomain->where('type=0 AND look=0')->order('rand()')->limit(1)->getField('url');
            $group_domain = $Mdomain->where('type=3 AND look=0')->order('rand()')->limit(1)->getField('url');
            $frend_domain = $Mdomain->where('type=4 AND look=0')->order('rand()')->limit(1)->getField('url');

            $generalize = $list['0']['qrcode']['0'];

            $url = C("MOIVE_API_HOST") . "index.php/index/cache/getimgl.html?pic=" . $_SERVER['HTTP_HOST'] . $generalize['img_url'] . "&url=" . $generalize['churl'];
            $res = $this->httpGet($url);
            $tgqc = json_decode($res, true);
            $generalize['qr_url'] = base64_encode(urlencode('http://' . $_SERVER['HTTP_HOST'] . $generalize['img_url']));
            $this->assign('tgqc', $tgqc['url']);
            $this->assign('generalize', $generalize);

            $this->assign('frend_domain', $frend_domain);
            $this->assign('group_domain', $group_domain);
            $this->assign('moive_domain', $moive_domain);
            $this->display('generalize');

        } else {
            $this->display('qrlist');
        }
    }

    /**
     * @推广链接
     */
    public function generalize()
    {
        $user = $this->getuserid();
        $this->domain_api = M('setting')->where('k="group_domain_api"')->getField('v');
        $this->js_review_domain = M('setting')->where('k="js_review_domain"')->getField('v');
        $packet_id = M('member')->where("uid={$user['uid']}")->getField("packet_id");
        $this->assign("uid",$user["uid"]);
        $this->assign("packet_id",$packet_id);
        $this->display();


    }
    //添加推广商获取域名日志
    public function add_domain_log(){
        $user = $this->getuserid();
        $group_domain = $_GET["group_domain"];
        if(empty($user) || empty($group_domain)){
            echo json_encode(["errno"=>102,"errmsg"=>"参数错误"]);
            exit;
        }
        $time = time();
        $uid = $user['uid'];
        $url = 'http://' . $group_domain. '/' . $time . '/' . $uid . '_'.rand(10,100).'.jpg';
        $share_url = C('SHARE_URL') . "?uid={$uid}&url={$group_domain}";
        // 短连接
        if (S('url') == $group_domain && S('uidd') == $uid) {
            $shorturl = S('shorturl');
        } else {
            S('url', $group_domain);
            S('uid', $uid);
            $content = file_get_contents("http://api.t.sina.com.cn/short_url/shorten.json?source=3213676317&url_long=" . $url);
            $shorturl = json_decode($content, true)[0]['url_short'];
            S('shorturl', $shorturl);
        }
        $generalize = array('url' => $url, 'shorturl' => $shorturl, 'share_url' => $share_url, 'source_url' => 'http://' . $group_domain, 'churl' => $uid . '_' . $group_domain);
        $data["group_domain"] = $group_domain;
        $data["longurl"] = $url;
        $data["shorturl"] = $shorturl;
        $tgqc = [];
        //获取推广海报接口域名
        $tg_domain = M("tghb")->where("state=2")->find();
        if(!$tg_domain){
            $tg_domain = M("tghb")->find();
        }
        $api_domain = $tg_domain["api_domain"];
        $show_domain = $tg_domain["show_domain"];

        $url = "http://" . $api_domain . "/index/cache/get_tghb.html?dm=" . $group_domain . "&url=" . $shorturl ."&uid=" . $uid;
        $res = $this->httpGet($url);
        $arr = json_decode($res, true);

        foreach($arr as $k=>$v){
            $qrarr = explode("chenqrcode", $v);
            $tgqc[] = "http://" . $show_domain . $qrarr[1];
        }

//        $data["js_domain"] = '<script type="text/javascript" src="http://'.$group_domain.'/Index/Js/index?uid='.$uid.'"></script>';
        $data["tg_img"] = $tgqc;
        addlog($group_domain,false,6);
        echo json_encode($data);
        exit;
    }
    public function test_domain(){
        echo json_encode(["group_domain"=>"bmc.wxcst.com.cn"]);
        exit;
    }
    /**
     * 24小时付费统计
     */
    public function counthours()
    {
        $prefix = C('DB_PREFIX');
        $p_where = " AND pay_amount>1";
        $data = array();
        for ($i = 0; $i < 24; $i++) {
            $startime = strtotime(date('Y-m-d H:00', strtotime("-" . ($i + 1) . " hour")));
            $endtime = strtotime(date('Y-m-d H:00', strtotime("-" . $i . " hour")));
            $count = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
            if (empty($count)) $count = '0';
            array_push($data, array('time' => strftime("%H", $endtime) . '点', 'count' => $count, 'mdate' => $endtime));
        }
        krsort($data);
        $data[0]['time'] = strftime("%d", $data[0]['mdate']) . '号' . strftime("%H", $data[0]['mdate']) . '点';
        $data[23]['time'] = strftime("%d", $data[23]['mdate']) . '号' . strftime("%H", $data[23]['mdate']) . '点';
        $this->assign('data', $data);
        $this->display();
    }

    public function counthourm()
    {
        $prefix = C('DB_PREFIX');
        $p_where = " AND pay_amount>1";
        $data = array();
        for ($i = 0; $i < 24 * 3; $i++) {
            $startime = strtotime(date('Y-m-d H:00')) - $i * 20 * 60;
            $endtime = strtotime(date('Y-m-d H:00')) - ($i - 1) * 20 * 60;
            $count = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
            if (empty($count)) $count = '0';
            array_push($data, array('time' => date("H点i分", $endtime), 'count' => $count, 'mdate' => $endtime));
        }
        krsort($data);
        $data[0]['time'] = strftime("%d", $data[0]['mdate']) . '号' . strftime("%H", $data[0]['mdate']) . '点';
        //     print_r($data);die;
        $this->assign('data', $data);
        $this->display();
    }


    /**
     * 24小时发起支付表
     */
    public function paytable(){
        $get = I('get.','','trim');
        $where = " 1=1 ";
        if(!isset($_GET['stime'])){
            $get['stime'] = date('Y-m-d',time());
        }
        if($get['stime']){
            $stime = strtotime($get['stime']);
            $where .= " and time >= {$stime} ";
        }
        if($get['etime']){
            $etime = strtotime($get['etime']);
            $where .= " and time < {$etime} ";
        }
        if($get['uiver']){
            $where .= " and uiver = '{$get['uiver']}' ";
        }
        $success = M('payapi_log')->field("sum(money) as money,count(id) as count")->where($where." and status=1 ")->find();
        $fail = M('payapi_log')->field("sum(money) as money,count(id) as count")->where($where." and status =0 ")->find();
        $total['count'] = $success['count'] + $fail['count'];
        $total['money'] = $success['money'] + $fail['money'];

        $this->assign('date',['stime' => $get['stime'] , 'etime' => $get['etime'] ]);
        $this->assign( 'data' , [ 'fail' => $fail , 'success' => $success ,'total' => $total] );
        $this->display();
    }

    /**
     * 24小时扣量表格图
     */
    public function kltable(){
        $head = [];
        for( $i = 0 ; $i<3 ; $i ++ ){
            if( $i == 0 ){
                $head['day'.(3-$i)] = date('Y-m-d',time());
            }else{
                $head['day'.(3-$i)] = date('Y-m-d',strtotime('-'.$i.'days'));
            }
        }
        ksort($head);
        $todayTime = strtotime(date('Y-m-d',time()));
        $startTime = $todayTime-3600*24*2;
        $endTime = $todayTime+3600*24;
        $where = " pay_time >= {$startTime} and pay_time<{$endTime} and pay_amount > 1";
        $totalList = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as ymd,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where($where)->group("from_unixtime(pay_time,'%Y%m%d%H') ")->select();
        $klList = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as ymd,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where($where." and account <1 ")->group("from_unixtime(pay_time,'%Y%m%d%H') ")->select();
        $_totalList = [];
        $totalArr = [];
        foreach ($totalList as $k => $v){
            foreach ($head as $hk => $hv){
                if( $v['ymd'] == $hv ){
                    $arr = [
                        'money' => $v['sum'],
                        'klmoney' => $klList[$k]['sum']
                    ];
                    if($arr['money'] ){
                        $arr['klbl'] = (round($arr['klmoney']/$arr['money'],4)*100).'%';
                    }else{
                        $arr['klbl'] = '';
                    }
                    if($v['h']){
                        $_totalList[ $v['h']][$v['ymd']] = $arr;
                        $totalArr[$hv]['money'] += $arr['money'];
                        $totalArr[$hv]['klmoney'] += $arr['klmoney'];
                    }
                }
            }
        }

        foreach ($totalArr as $k=>$v){
            if( $v['money'] ){
                $totalArr[$k]['klbl'] = (round($v['klmoney']/$v['money'],4)*100).'%';
            }else{
                $totalArr[$k]['klbl'] = '';
            }
        }

        // pre($totalArr);
        $this->assign('total',$totalArr);
        $this->assign('head',$head);
        $this->assign('list',$_totalList);
        $this->display();
    }


    public function horastr(){
        $head = [];
        $debug = I('get.debug',0);
        $day = 8;
        for( $i = 0 ; $i<$day ; $i ++ ){
            if( $i == 0 ){
                $head['day'.($day-$i)] = date('Y-m-d',time());
            }else{
                $head['day'.($day-$i)] = date('Y-m-d',strtotime('-'.$i.'days'));
            }
        }
        ksort($head);
        $todayTime = strtotime(date('Y-m-d',time()));
        $startTime = $todayTime-3600*24*7;
        $endTime = $todayTime+3600*24;
        M('ip_count')->where("time < {$startTime}")->delete();//删除7天前的ip数量
        $iplist = M('ip_count')->where("time >={$startTime} and time <= {$endTime}")->getField("time,count");

        $_ipList = [];
        foreach ($iplist as $k => $v){
            $ymd = date('Y-m-d',$k);
            $h = date('H',$k);
            $_ipList[$ymd.':'.$h] = $v;
        }
        $where = " pay_time >= {$startTime} and pay_time<{$endTime} and pay_amount >= 1 ";

        $klqtotalList = [];
        if($debug){
            $klqtotalList = M('pay_log')->field("pay_time_ymd as ymd,pay_time_h as h,sum(pay_amount) as klqsum")->where($where)->group("pay_time_ymdh,pay_time_ymd,pay_time_h ")->select();
        }
        $where .= " and account>=1 ";
      //echo $where;exit;
        $totalList = M('pay_log')->field("pay_time_ymd as ymd,pay_time_h as h,sum(pay_amount) as sum")->where($where)->group("pay_time_ymdh,pay_time_ymd,pay_time_h")->select();
       //$totalList = M('pay_log')->field("pay_time_ymd as ymd,pay_time_h as h,sum(pay_amount) as sum")->where($where)->group("pay_time_ymdh,pay_time_ymd,pay_time_h")->buildSql();
      //echo M()->getLastSql();
      //var_dump($totalList);exit;  
      if($debug){
            foreach ($totalList as $k => $v){
                foreach ($klqtotalList as $klk => $klv){
                    if($v['ymd'] == $klv['ymd'] && $v['h'] == $klv['h']){
                        $totalList[$k]['klqsum'] = $klv['klqsum'];
                    }
                }
            }
        }

        //$klList = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as ymd,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where($where." and account <1 ")->group("from_unixtime(pay_time,'%Y%m%d%H') ")->select();
        $flag = 0;
        $todaymoney = 0;
        $klqtodaymoney = 0 ;
        foreach ($totalList as $k=>$v){
            if($flag == $v['ymd']){
                $todaymoney += $v['sum'];
                $v['todaymoney'] = $todaymoney;
                if($debug){
                    $klqtodaymoney += $v['klqsum'];
                    $v['klqtodaymoney'] = $klqtodaymoney;
                }
            }else{
                $v['todaymoney'] = $v['sum'];
                $todaymoney = $v['sum'];
                if($debug){
                    $v['klqtodaymoney'] = $v['klqsum'];
                    $klqtodaymoney = $v['klqsum'];
                }
                $flag = $v['ymd'];
            }
            $totalList[$k] = $v;
        }

        $_totalList = [];
        foreach ($totalList as $k => $v){
            foreach ($head as $hk => $hv){
                if( $v['ymd'] == $hv ){
                    if($v['h']){
                        $arr = [
                            'money' => round($v['sum'],1),
                            'ymd'     => $v['ymd'],
                            'todaymoney' => round($v['todaymoney'],1),
                        ];
                        if($debug){
                            $arr['klqtodaymoney'] = round($v['klqtodaymoney'],1);
                            $arr['klqmoney'] =round($v['klqsum'],1);
                        }
                        $_totalList[$v['h']][$v['ymd']] = $arr;
                    }
                }
            }
        }
        $darr = [];
        foreach ($_totalList as $k=>$v){
            $darr[] = $k;
        }

        sort($darr);
        $karr = [];
        foreach ($darr as $k=>$v){
            foreach ($_totalList[$v] as $k1 => $v1){
                if($v1['ymd']) {
                    /*$st = strtotime($v1['ymd'] . " {$v}:00:00");
                    $qytstime = $st - 3600 * 24;
                    $qytestime = $st - 3600 * 24 + 3600;*/

                    //$qytmoney = M('pay_log')->where(" pay_time >= {$qytstime} and pay_time<{$qytestime} and pay_amount > 1 and {$dwhere} ")->sum("pay_amount");
                    $qytymd = date('Y-m-d', (strtotime($v1['ymd']) - 3600 * 24));
                    $qytmoney = $_totalList[$v][$qytymd]['money'];
                    if($debug){
                        $klqytmoney = $_totalList[$v][$qytymd]['klqmoney'];
                        $_totalList[$v][$k1]['klqytmoney'] = $klqytmoney;
                    }

                    $_totalList[$v][$k1]['qytmoney'] = $qytmoney;
                    $_totalList[$v][$k1]['qytip'] = $_ipList[$qytymd.':'.$v];
                }
            }
            $karr[$v] = $_totalList[$v];
        }
        array_shift($head);
        $this->assign('iplist',$_ipList);
        $this->assign('head',$head);
        $this->assign('list',$karr);
        $this->display();
    }

    public function save_ip(){
        $get = I('get.','','trim');
        if($get['ymd'] && $get['h'] ){
            $time = strtotime($get['ymd'].' '.$get['h'].':00:00');
            $IP = M('ip_count');
            $isExt = $IP->where(['time' => $time])->find();
            if($isExt){
                $IP->where(['id' => $isExt['id']])->save(['count' => $get['ipcount']]);
            }else{
                $IP->add(['time' => $time , 'count' => $get['ipcount']]);
            }
            echo 1 ;exit;
        }
        echo 0 ;exit;
    }


    //24*7每小时统计
    public function horastr2()
    {
        $date = strtotime(date('Y-m-d', strtotime('-7days')));

        $count = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $date . "  and account > 0 ")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        $rows = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as notsum")->where("pay_time > " . $date)->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        $rowsand = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as notsumand")->where("pay_time > " . $date . " and pay_source=2")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        $countand = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sumand")->where("pay_time > " . $date . "  and account > 0 and pay_source=2")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        $money = [];
        $head = [];
        $one = null;
        $sum = null;
        $data = [];

        foreach ($rows as $key => $value) {

            if (!in_array($value['d'], $head)) {
                $head[] = $value['d'];
                $horastr_count = M('horastr')->where(array('date' => $value['d']))->find();
                if ($horastr_count == null) {
                    $data[] = array('date' => $value['d'], 'type' => 1);
                    $data[] = array('date' => $value['d'], 'type' => 2);
                    $data[] = array('date' => $value['d'], 'type' => 3);
                    $data[] = array('date' => $value['d'], 'type' => 4);
                    $data[] = array('date' => $value['d'], 'type' => 5); //Android的浏览iP
                    $data[] = array('date' => $value['d'], 'type' => 6); //android下载ip
                }
            }
        }

        if (!empty($data)) {
            M('horastr')->addAll($data);
        }

        foreach ($rows as $key => $value) {
            $time = strtotime($value['d']) - 3600 * 24;
            $dateLast = date('Y-m-d', $time);

            $money[$value['h']][$value['d']]['notsum'] = ceil($value['notsum']); //未扣量的金额

            foreach ($count as $k => $v) {
                if ($v['d'] == $value['d'] && $v['h'] == $value['h']) {
                    $money[$value['h']][$value['d']]['c'] = ceil($v['sum']);//已扣量金额
                    /*$money[$value['h']][$value['d']]['s'] = round((($money[$value['h']][$value['d']]['c'] - $money[$value['h']][$dateLast]['c']) / $money[$value['h']][$dateLast]['c']), 5) * 100 . "%";//对比前一天数据比例 扣量后的数据*/


                    $newtime = date("Y-m-d");
                    $oldtime = date('Y-m-d', strtotime('-1days'));
                    if (S($value['h'] . $value['d'] . 's') != false && S($value['h'] . $value[$newtime] . 's') != false) {
                        $money[$value['h']][$value['d']]['s'] = S($value['h'] . $value['d'] . 's');
                        $money[$value['h']][$value[$newtime]]['s'] = round((($money[$value['h']][$value[$newtime]]['c'] - $money[$value['h']][$oldtime]['c']) / $money[$value['h']][$dateLast]['c']), 5) * 100 . "%";
                    } else {
                        $money[$value['h']][$value['d']]['s'] = round((($money[$value['h']][$value['d']]['c'] - $money[$value['h']][$dateLast]['c']) / $money[$value['h']][$dateLast]['c']), 5) * 100 . "%";
                        S($value['h'] . $value['d'] . 's', $money[$value['h']][$value['d']]['s'], 3600 * 24 * 2);
                    }


                    unset($count[$k]);
                    break;
                }
            }

            foreach ($rowsand as $k => $v) {
                if ($v['d'] == $value['d'] && $v['h'] == $value['h']) {
                    $money[$value['h']][$value['d']]['notsumand'] = ceil($v['notsumand']);//android充值
                    break;
                }
            }

            foreach ($countand as $k => $v) {
                if ($v['d'] == $value['d'] && $v['h'] == $value['h']) {
                    $money[$value['h']][$value['d']]['sumand'] = ceil($v['sumand']);//android扣量充值
                    break;
                }
            }

            $money[$value['h']][$value['d']]['c'] = empty($money[$value['h']][$value['d']]['c']) ? 0 : $money[$value['h']][$value['d']]['c'];
            $money[$value['h']][$value['d']]['sum'] = empty($money[$value['h']][$value['d']]['sum']) ? 0 : $money[$value['h']][$value['d']]['sum'];
            $money[$value['h']][$value['d']]['s'] = empty($money[$value['h']][$value['d']]['s']) || $money[$value['h']][$value['d']]['s'] == 'INF%' ? 0 : $money[$value['h']][$value['d']]['s'];

            if (!empty(S($value['d'] . ':' . $value['h'] . 'ipnum'))) {
                $money[$value['h']][$value['d']]['ipnum'] = S($value['d'] . ':' . $value['h'] . 'ipnum');
            } else {
                $money[$value['h']][$value['d']]['ipnum'] = '';
            }

            if (!empty(S($value['d'] . ':' . $value['h'] . 'andip'))) {
                $money[$value['h']][$value['d']]['andip'] = S($value['d'] . ':' . $value['h'] . 'andip');
            } else {
                $money[$value['h']][$value['d']]['andip'] = '';
            }
            if (!empty(S($value['d'] . ':' . $value['h'] . 'anddip'))) {
                $money[$value['h']][$value['d']]['anddip'] = S($value['d'] . ':' . $value['h'] . 'anddip');
            } else {
                $money[$value['h']][$value['d']]['anddip'] = '';
            }

            if (!empty(S($value['d'] . ':' . $value['h'] . 'zhl')) & S($value['d'] . ':' . $value['h'] . 'zhl') != 'INF%') {
                $money[$value['h']][$value['d']]['zhl'] = S($value['d'] . ':' . $value['h'] . 'zhl');
            } else {
                $money[$value['h']][$value['d']]['zhl'] = 0;
            }
        }

        ksort($money);

        foreach ($money as $key => $value) {
            foreach ($head as $k => $v) {
                if (empty($value[$v])) {
                    $value[$v]['c'] = 0;
                    $value[$v]['notsum'] = 0;
                    $value[$v]['notsumand'] = 0;
                    $value[$v]['sumand'] = 0;
                    $value[$v]['s'] = 0;
                    $value[$v]['c'] = 0;
                    $value[$v]['andip'] = '';
                    $value[$v]['anddip'] = '';
                    $value[$v]['ipnum'] = '';
                    $value[$v]['zhl'] = 0;
                    $value[$v]['notzhl'] = 0;
                }
                $value[$k]['notsum'] = $value[$v]['notsum'];
                $value[$k]['notsumand'] = $value[$v]['notsumand'];
                $value[$k]['sumand'] = $value[$v]['sumand'];
                $value[$k]['c'] = $value[$v]['c'];
                $value[$k]['s'] = $value[$v]['s'];
                $value[$k]['andip'] = $value[$v]['andip'];
                $value[$k]['anddip'] = $value[$v]['anddip'];
                $value[$k]['ipnum'] = $value[$v]['ipnum'];
                $value[$k]['zhl'] = $value[$v]['zhl'];
                $value[$k]['notzhl'] = $value[$v]['notzhl'];
                $value[$k]['sum'] += $value[$k]['c'];

                unset($value[$v]);
                $horastr_id = M('horastr')->where(array('date' => $v))->select();


                foreach ($horastr_id as $keyh => $valueh) {
                    $info = M('horastr_data')->where(array('horastr_id' => $valueh['id']))->find();
                    if ($info != null) {
                        switch ($valueh['type']) {
                            case 3:
                                $value[$k]['ipnum'] = $info['time' . $key];
                                break;
                            case 4:
                                $value[$k]['zhl'] = $info['time' . $key];
                                break;
                            case 5:
                                $value[$k]['andip'] = $info['time' . $key];
                                break;
                            case 6:
                                $value[$k]['anddip'] = $info['time' . $key];
                                break;
                        }
                        continue;
                    }
                    switch ($valueh['type']) {
                        case 1:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['c'];
                            break;
                        case 2:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['s'];
                            break;
                        case 3:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['ipnum'];
                            break;
                        case 4:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['zhl'];
                            break;
                        case 5:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['andip'];
                            break;
                        case 6:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['anddip'];
                            break;

                    }
                }

            }

            $money[$key] = $value;
            unset($money[$key][0]);
        }

        foreach ($data2 as $value) {
            M('horastr_data')->add($value);
        }
        unset($head[0]);


        //  得到所需数据(扣)
        $yesdate = strtotime(date('Y-m-d', strtotime('-6days')));
        $yescount = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $yesdate . "  and account > 0 ")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        //  当日总充值金额(扣)
        //S(date("Y-m-d") . 'sum',null);
        if (S(date("Y-m-d") . 'sum') == false) {
            $yessum = [];
            foreach ($yescount as $key => $value) {
                if ($value['d'] == $value['d']) {
                    $yessum[$value['d']]['sum'] += $value['sum'];
                }
            }
            S(date("Y-m-d") . 'sum', $yessum, 3500 * 24 * 2);
        } else {
            $yessum = S(date("Y-m-d") . 'sum');
            $newsum = 0;
            foreach ($yescount as $key => $value) {
                if ($value['d'] == date("Y-m-d")) {
                    $newsum += $value['sum'];
                }
            }
            $yessum[date("Y-m-d")]['sum'] = $newsum;
        }

        //  得到当日ip总数
        //S(date("Y-m-d") . 'ipsum',null);
        if (S(date("Y-m-d") . 'ipsum') !== false) {
            ksort($money);
            $ipsum = S(date("Y-m-d") . 'ipsum');
            $newipsum = 0;
            $newandipsum = 0;
            $newanddipsum = 0;
            foreach ($money as $key => $value) {
                $newipsum += $value['7']["ipnum"];
                $newandipsum += $value[$k]["andip"];
                $newanddipsum += $value[$k]["anddip"];
            }
            $ipsum[date("Y-m-d")]['ipsum'] = $newipsum;
            $ipsum[date("Y-m-d")]['andipsum'] = $newandipsum;
            $ipsum[date("Y-m-d")]['andipsum'] = $newanddipsum;
        } else {
            ksort($money);
            $ipsum = [];
            foreach ($money as $key => $value) {
                foreach ($head as $k => $v) {
                    if ($money[$key][$k] == $money[$key][$k]) {
                        $ipsum[$v]['ipsum'] += $value[$k]["ipnum"];
                        $ipsum[$v]['andipsum'] += $value[$k]["andip"];
                        $ipsum[$v]['anddipsum'] += $value[$k]["anddip"];
                    }
                }
            }
            S(date("Y-m-d") . 'ipsum', $ipsum, 3500 * 24 * 2);
        }

        $cipsum = $ipsum;
        $this->assign('ipsum', $ipsum);

        //  计算当日转换率(扣)
        $rate = [];
        foreach ($yessum as $key => $value) {
            foreach ($ipsum as $k => $v) {
                if ($key == $k) {
                    $rate[$key]['rate'] = $yessum[$key]['sum'] / $ipsum[$k]['ipsum'];
                    $rate[$key]['rate'] = round($rate[$key]['rate'], 4);
                    $rate[$key]['rate'] = $rate[$key]['rate'] * 100;
                    $rate[$key]['rate'] = $rate[$key]['rate'] . '%';
                }
            }
        }

        //  当日转换率(扣)
        $this->assign('rate', $rate);


        //  得到Android所需的数据
        $yesdateand = strtotime(date('Y-m-d', strtotime('-6days')));
        $yescountand = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $yesdateand . "  and account > 0 ")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();
        $yescountandnot = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $yesdateand)->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

        $yessumand = [];
        $yessumandnot = [];
        foreach ($yescountand as $key => $value) {
            if ($value['d'] == $value['d']) {
                $yessumand[$value['d']]['sum'] += $value['sum'];
            }
        }

        foreach ($yescountandnot as $key => $value) {
            if ($value['d'] == $value['d']) {
                $yessumandnot[$value['d']]['sum'] += $value['sum'];
            }
        }

        //  计算android当日转换率(扣)
        $rateand = [];
        $rateandnot = [];
        foreach ($yessumand as $key => $value) {
            foreach ($ipsum as $k => $v) {
                if ($key == $k) {
                    $rateand[$key]['rate'] = $yessumand[$key]['sum'] / $ipsum[$k]['andipsum'];
                    $rateand[$key]['rate'] = round($rateand[$key]['rate'], 4);
                    $rateand[$key]['rate'] = $rateand[$key]['rate'] * 100;
                    $rateand[$key]['rate'] = $rateand[$key]['rate'] . '%';
                    unset($ipsumand[$k]);
                    break;
                }
            }
        }
        $this->assign('rateand', $rateand);

        if ($_GET['debug']) {
            //  android日累计转化率(全)
            foreach ($yessumandnot as $key => $value) {
                foreach ($ipsum as $k => $v) {
                    if ($key == $k) {
                        $rateandnot[$key]['rate'] = $yessumandnot[$key]['sum'] / $ipsum[$k]['andipsum'];
                        $rateandnot[$key]['rate'] = round($rateandnot[$key]['rate'], 4);
                        $rateandnot[$key]['rate'] = $rateandnot[$key]['rate'] * 100;
                        $rateandnot[$key]['rate'] = $rateandnot[$key]['rate'] . '%';
                        unset($ipsum[$k]);
                        break;
                    }
                }
            }
            //  将android日累计转化率(全)分配到视图页面
            $this->assign('rateandnot', $rateandnot);

            //  得到所需数据(全)
            $notdate = strtotime(date('Y-m-d', strtotime('-6days')));
            $notcount = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $notdate)->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();

            //  当日总充值金额(全)
            $notsum = [];
            foreach ($notcount as $key => $value) {
                if ($value['d'] == $value['d']) {
                    $notsum[$value['d']]['sum'] += $value['sum'];
                }
            }

            //  计算日累计转化率(全)
            $notrate = [];
            foreach ($notsum as $key => $value) {
                foreach ($cipsum as $k => $v) {
                    if ($key == $k) {
                        $notrate[$key]['rate'] = $notsum[$key]['sum'] / $cipsum[$k]['ipsum'];
                        $notrate[$key]['rate'] = round($notrate[$key]['rate'], 4);
                        $notrate[$key]['rate'] = $notrate[$key]['rate'] * 100;
                        $notrate[$key]['rate'] = $notrate[$key]['rate'] . '%';
                        unset($ipsum[$k]);
                        break;
                    }
                }
            }
            //  将日累计转化率数据(全)分配到视图页面
            $this->assign('notrate', $notrate);
        }

        ksort($head);
        ksort($money);
        $this->assign('head', $head);
        $this->assign('money', $money);
        $this->display();
    }

    public function saveip()
    {
        $date = I('get.date');
        $value = I('get.value');
        $chongzhi = I('get.chongzhi');
        $zhl = sprintf("%.4f", ($chongzhi / $value)) * 100 . '%';
        $dateArray = explode(':', $date);
        //print_r($dateArray);
        $horastr_id1 = M('horastr')->where(array('date' => $dateArray[0], 'type' => 3))->getField('id');
        //echo $horastr_id1;
        $horastr_id2 = M('horastr')->where(array('date' => $dateArray[0], 'type' => 4))->getField('id');
        //echo $horastr_id2;
        M('horastr_data')->where(array("horastr_id" => $horastr_id1))->save(array('time' . $dateArray[1] => $value));
        M('horastr_data')->where(array("horastr_id" => $horastr_id2))->save(array('time' . $dateArray[1] => $zhl));
        if (S($date . 'ipnum', $value, 3600 * 24 * 7) && S($date . 'zhl', $zhl, 3600 * 24 * 7)) exit($zhl); else exit('-1');
    }

    // android ip量
    public function saveipand()
    {
        $date = I('get.date');
        $value = I('get.value');
        $dateArray = explode(':', $date);
        $horastr_id1 = M('horastr')->where(array('date' => $dateArray[0], 'type' => 5))->getField('id');
        $andip = M('horastr_data')->where(array("horastr_id" => $horastr_id1))->save(array('time' . $dateArray[1] => $value));
        // echo  M('horastr_data')->getLastSql();die;
        if (S($date . 'andip', $value, 3600 * 24 * 7)) exit($value); 
        // if (S($date . 'ipnumand', $value, 3600 * 24 * 7) && S($date . 'zhl', $zhl, 3600 * 24 * 7)) exit($zhl); else exit('-1');

    }

    // Android 下载量
    public function saveipandd()
    {
        $date = I('get.date');
        $value = I('get.value');
        $chongzhi = I('get.chongzhi');
        $dateArray = explode(':', $date);
        $horastr_id1 = M('horastr')->where(array('date' => $dateArray[0], 'type' => 6))->getField('id');
        M('horastr_data')->where(array("horastr_id" => $horastr_id1))->save(array('time' . $dateArray[1] => $value));

        if (S($date . 'anddip', $value, 3600 * 24 * 7)) exit($value); else exit('-1');

    }

    /**
     * 七日统计
     */
    public function countshow()
    {
        $prefix = C('DB_PREFIX');
        $p_where = " AND account>=1 AND pay_amount>1";
        $k_where = " AND account<1 AND pay_amount>1";
        //第一天
        $startime = strtotime(date('Y-m-d 00:00:00'));
        $onecount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime" . $p_where)->sum("pay_amount");
        $oneday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime" . $k_where)->sum("pay_amount");
        //第二天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00'));
        $twocount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $twoday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");
        //第三天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-2 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $threecount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $threeday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");
        //第四天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-3 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00', strtotime('-2 day')));
        $fourcount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $fourday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");
        //第五天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-4 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00', strtotime('-3 day')));
        $fivecount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $fiveday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");
        //第六天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-5 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00', strtotime('-4 day')));
        $sixcount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $sixday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");
        //第七天
        $startime = strtotime(date('Y-m-d 00:00:00', strtotime('-6 day')));
        $endtime = strtotime(date('Y-m-d 00:00:00', strtotime('-5 day')));
        $sevencount = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $p_where)->sum("pay_amount");
        $sevenday = M('pay_log')->where("{$prefix}pay_log.pay_time >= $startime AND {$prefix}pay_log.pay_time < $endtime" . $k_where)->sum("pay_amount");

        $this->assign('oneday', $oneday);
        $this->assign('onecount', $onecount);
        $this->assign('twoday', $twoday);
        $this->assign('twocount', $twocount);
        $this->assign('threeday', $threeday);
        $this->assign('threecount', $threecount);
        $this->assign('fourday', $fourday);
        $this->assign('fourcount', $fourcount);
        $this->assign('fiveday', $fiveday);
        $this->assign('fivecount', $fivecount);
        $this->assign('sixday', $sixday);
        $this->assign('sixcount', $sixcount);
        $this->assign('sevenday', $sevenday);
        $this->assign('sevencount', $sevencount);
        $this->display();
    }

    public function countshowhour()
    {
        $date = strtotime(date('Y-m-d', strtotime('-9days')));
        //$date=0;
        $count = M('pay_log')->field("from_unixtime(pay_time,'%Y-%m-%d') as d,from_unixtime(pay_time,'%H') as h,sum(pay_amount) as sum")->where("pay_time > " . $date . "  and account > 0 ")->group(" from_unixtime(pay_time,'%Y%m%d%H') ")->select();
        $money = [];
        $head = [];
        $one = null;
        $sum = null;
        $data = [];
        foreach ($count as $key => $value) {
            //$head[$value['d']] = $value['d'];
            if (!in_array($value['d'], $head)) {
                $head[] = $value['d'];
                $horastr_count = M('horastr')->where(array('date' => $value['d']))->find();
                if ($horastr_count == null) {
                    $data[] = array('date' => $value['d'], 'type' => 1);
                    $data[] = array('date' => $value['d'], 'type' => 2);
                    $data[] = array('date' => $value['d'], 'type' => 3);
                    $data[] = array('date' => $value['d'], 'type' => 4);
                    //$data[] = array('date'=>$value['d'],'type'=>5);
                }
            }
        }
        if (!empty($data)) {
            M('horastr')->addAll($data);
        }
        //print_r($head);
        foreach ($count as $key => $value) {
            $time = strtotime($value['d']) - 3600 * 24;
            $dateLast = date('Y-m-d', $time);
            $money[$value['h']][$value['d']]['c'] = ceil($value['sum']);
            $money[$value['h']][$value['d']]['s'] = round(($money[$value['h']][$value['d']]['c'] - $money[$value['h']][$dateLast]['c']), 5);
            $money[$value['h']][$value['d']]['c'] = empty($money[$value['h']][$value['d']]['c']) ? 0 : $money[$value['h']][$value['d']]['c'];
            $money[$value['h']][$value['d']]['sum'] = empty($money[$value['h']][$value['d']]['sum']) ? 0 : $money[$value['h']][$value['d']]['sum'];
            $money[$value['h']][$value['d']]['s'] = empty($money[$value['h']][$value['d']]['s']) || $money[$value['h']][$value['d']]['s'] == 'INF%' ? 0 : $money[$value['h']][$value['d']]['s'];
            if (!empty(S($value['d'] . ':' . $value['h'] . 'ipnum'))) {
                $money[$value['h']][$value['d']]['ipnum'] = S($value['d'] . ':' . $value['h'] . 'ipnum');
            } else {
                $money[$value['h']][$value['d']]['ipnum'] = '';
            }
            if (!empty(S($value['d'] . ':' . $value['h'] . 'zhl')) & S($value['d'] . ':' . $value['h'] . 'zhl') != 'INF%') {
                $money[$value['h']][$value['d']]['zhl'] = S($value['d'] . ':' . $value['h'] . 'zhl');
            } else {
                $money[$value['h']][$value['d']]['zhl'] = 0;
            }
        }
        ksort($money);
        ksort($head);
        foreach ($money as $key => $value) {
            ksort($value);
            foreach ($head as $k => $v) {
                if (empty($value[$v])) {
                    $value[$v]['c'] = 0;
                    $value[$v]['s'] = 0;
                    $value[$v]['c'] = 0;
                    $value[$v]['ipnum'] = '';
                    $value[$v]['zhl'] = 0;
                }
                $value[$k]['c'] = $value[$v]['c'];
                $value[$k]['s'] = $value[$v]['s'];
                $value[$k]['ipnum'] = $value[$v]['ipnum'];
                $value[$k]['zhl'] = $value[$v]['zhl'];
                $value[$k]['sum'] += $value[$k]['c'];
                unset($value[$v]);
                $horastr_id = M('horastr')->where(array('date' => $v))->select();
                foreach ($horastr_id as $keyh => $valueh) {
                    $info = M('horastr_data')->where(array('horastr_id' => $valueh['id']))->find();
                    if ($info != null) {
                        switch ($valueh['type']) {
                            case 3:
                                $value[$k]['ipnum'] = $info['time' . $key];
                                break;
                            case 4:
                                $value[$k]['zhl'] = $info['time' . $key];
                                break;
                        }
                        continue;
                    }
                    switch ($valueh['type']) {
                        case 1:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['c'];
                            break;
                        case 2:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['s'];
                            break;
                        case 3:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['ipnum'];
                            break;
                        case 4:
                            $data2[$valueh['id']]['horastr_id'] = $valueh['id'];
                            $data2[$valueh['id']]['time' . $key] = $value[$k]['zhl'];
                            break;
                    }
                }
                $value[$k]['zhl'] = str_replace(array("%"), "", $value[$k]['zhl']) / 100;
            }
            $money[$key] = $value;
            unset($money[$key][0]);

        }
        foreach ($data2 as $value) {
            M('horastr_data')->add($value);
        }
        unset($head[0]);
        unset($head[1]);
        unset($head[2]);
        //print_r($money);exit;
        $info = I('get.info');
        switch ($info) {
            case 'c':
                $title = '小时充值图';
                break;
            case 's':
                $title = '对比前一天升降';
                break;
            case 'ipnum':
                $title = '小时IP量';
                break;
            case 'ipnum':
                $title = '小时IP价值';
                break;
            default:
                $title = '小时充值图';
                break;
        }
        if (empty($info)) $info = 'c';
        $this->assign('head', $head);
        $this->assign('money', $money);
        $this->assign('info', $info);
        $this->assign('title', $title);
        $this->display();
    }

    /*获取当前登录账户的下级

        *@id int 当前登录账号ID

        * return array 自己ID和下级ID的数组

        */
    function getlowid($id)
    {

        //判断是不是管理员

        if ($id !== '1') {

            $low = M('member')->field('uid')->where(array('cid' => $id))->select();

            foreach ($low as $key => $value) {

                $arrid[$key] = $value['uid'];

            }

            $arrid = !empty($arrid) ? $arrid : array();

            array_push($arrid, $id);

            $arrid = implode(',', $arrid);

            return $arrid;

        }

    }

    //检查链接
    private function chackurl($url)
    {
        $_url = "http://www.yundq.cn/url/wx?key=693E6C9885DC4231&url=" . $url;
        $data = $this->httpGet($_url);
        // $this->WeixinLog("task url check: {$url} : {$data}");
        if (strpos($data, '黑名单')) {
            return false;
        } else {
            return true;
        }
    }

    public function chenQrCode()
    {
        $we_id = I('get.we_id', '');
        $uid = I('get.uid', 0, 'intval');

        if (!$we_id || !$uid) {
            $back_data = array('status' => '0', 'msg' => '无效的参数！');
        } else {

            $path = dirname(APP_PATH);
            $t_path = '/Public/Ln/images/';
            $filename = 'chenqrcode/' . $we_id . '/' . $uid . '_' . $we_id . '.png';
            $img_url = $t_path . $filename;
            if (!file_exists($path . $t_path . $filename)) {
                $url = 'http://' . $we_id . '/' . date('YmdHis') . '/' . $uid . '.gif';
                $img_url = $this->QrCode($filename, $url);
                if ($img_url !== false) {
                    $back_data = array('status' => '1', 'msg' => '生成成功！', 'url' => $img_url);
                } else {
                    $back_data = array('status' => '0', 'msg' => '生成失败！');
                }
            } else {
                $back_data = array('status' => '1', 'msg' => '已经生成！', 'url' => $img_url);
            }
        }

        echo json_encode($back_data);
    }

    public function generateQrCode()
    {
        $we_id = I('get.we_id', 0, 'intval');
        $uid = I('get.uid', 0, 'intval');
        $domain = M('domain')->field('*')->where(array('id' => $we_id))->find();

        if (!$we_id || !$uid || !$domain['id']) {
            $back_data = array('status' => '0', 'msg' => '无效的参数！');
        } else {
            $path = dirname(APP_PATH);
            $t_path = '/Public/Ln/images/';
            $filename = 'urlqrcode/' . $domain['url'] . '/' . $uid . '_' . $domain['url'] . '.png';
            $img_url = $t_path . $filename;
            if (!file_exists($path . $t_path . $filename)) {
                $url = 'http://' . $domain['url'] . '/index/index/link?refid=' . $uid;
                $img_url = $this->QrCode($filename, $url);
                if ($img_url !== false) {
                    $back_data = array('status' => '1', 'msg' => '生成成功！', 'url' => $img_url);
                } else {
                    $back_data = array('status' => '0', 'msg' => '生成失败！');
                }
            } else {
                $back_data = array('status' => '1', 'msg' => '已经生成！', 'url' => $img_url);
            }
        }

        echo json_encode($back_data);
    }

    /**
     * 统计用户的总充值
     **/
    private function countUserMoney($uid, $pWhere)
    {

        $monies_sum = 0;
        $prefix = C('DB_PREFIX');
        //获取其一级推广下的所有次一级推广
        $user = M('member');
        $list = $user->field("{$prefix}member.uid,{$prefix}member.cid")
            ->join("{$prefix}auth_group_access g ON {$prefix}member.uid = g.uid")
            ->where('cid=' . $uid . ' and g.group_id=2')
            ->order('uid desc')
            ->select();
        foreach ($list as $key => $value) {
            $monies = M('pay_log')->where("ref1_id={$value['uid']} AND account=1 " . $pWhere)->sum('pay_amount');
            if (!empty($monies)) {
                $monies_sum += $monies;
            }
        }
        return $monies_sum;
    }

    /**
     * 获取超级管理员列表
     **/
    private function getSuper()
    {
        //获取超级管理员帐号列表
        $Super_admin_list = M('auth_group_access')->field("uid")->where('group_id=1')->select();
        foreach ($Super_admin_list as $key => $value) {
            $Super_admin[] = $value['uid'];
        }
        return $Super_admin;
    }

}

?>
