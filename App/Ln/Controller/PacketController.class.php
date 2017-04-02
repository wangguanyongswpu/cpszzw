<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/1
 * Time: 16:25
 */

namespace Ln\Controller;

use Ln\Controller\ComController;

class PacketController extends ComController{

    public function index(){
        $data['type'] = 2;//C('PACKET_TYPE') ? C('PACKET_TYPE') : 0;//2跳转，0为入口
        $data['action'] = 'index';
        $packet = M('packet')->where("type={$data['type']}")->field('*')->select();

        $wechat = M('domain')->where("type={$data['type']} AND look=0")->field('id,url')->select();

        $wechar_arr = array();
        foreach($wechat as $v){
            $wechar_arr[$v['id']] = $v['url'];
        }
        $list = array();
        if($packet){
            foreach($packet as $v){
                $wechar_str = '';
                if($v['wechart_id'] && is_array(unserialize($v['wechart_id']))){
                    $wechar_id_arr = unserialize($v['wechart_id']);
                    foreach($wechar_id_arr as $value){
                        $wechar_str  .= $wechar_arr[$value].", ";
                    }

                }
                $list[] = array('alias' =>$v['alias'], 'name' =>$wechar_str,'id' => $v['id'] );
            }
        }
        $this->assign($data);
        $this->assign('list',$list);

        $this -> display();
    }

    /**
     * 分享域名分组
     */
    public function share(){
        $data['type'] = 4;//C('PACKET_TYPE') ? C('PACKET_TYPE') : 0;//2跳转，0为入口
        $data['action'] = 'share';
        $packet = M('packet')->where("type={$data['type']}")->field('*')->select();

        $wechat = M('domain')->where("type={$data['type']} AND look=0")->field('id,url')->select();

        $wechar_arr = array();
        foreach($wechat as $v){
            $wechar_arr[$v['id']] = $v['url'];
        }
        $list = array();
        if($packet){
            foreach($packet as $v){
                $wechar_str = '';
                if($v['wechart_id'] && is_array(unserialize($v['wechart_id']))){
                    $wechar_id_arr = unserialize($v['wechart_id']);
                    foreach($wechar_id_arr as $value){
                        $wechar_str  .= $wechar_arr[$value].", ";
                    }

                }
                $list[] = array('alias' =>$v['alias'], 'name' =>$wechar_str,'id' => $v['id'] );
            }
        }
        $this->assign($data);
        $this->assign('list',$list);

        $this->display('index');
    }

    /**
     * 微信群域名分组
     */
    public function wechat(){
        $data['type'] = 3;//C('PACKET_TYPE') ? C('PACKET_TYPE') : 0;//2跳转，0为入口
        $data['action'] = 'wechat';
        $packet = M('packet')->where("type={$data['type']}")->field('*')->select();

        $wechat = M('domain')->where("type={$data['type']} AND look=0")->field('id,url')->select();

        $wechar_arr = array();
        foreach($wechat as $v){
            $wechar_arr[$v['id']] = $v['url'];
        }
        $list = array();
        if($packet){
            foreach($packet as $v){
                $wechar_str = '';
                if($v['wechart_id'] && is_array(unserialize($v['wechart_id']))){
                    $wechar_id_arr = unserialize($v['wechart_id']);
                    foreach($wechar_id_arr as $value){
                        $wechar_str  .= $wechar_arr[$value].", ";
                    }
                }
                $list[] = array('alias' =>$v['alias'], 'name' =>$wechar_str,'id' => $v['id'] );
            }
        }
        $this->assign($data);
        $this->assign('list',$list);

        $this->display('index');
    }

    public function add(){
        $type = I('get.type', 0);
        $wechat = M('domain')->field('*')->where(array('type'=> $type, 'look' => 0))->select();
        $this->assign('wechat',$wechat);
        $this->display();
    }

    public function edit(){
        $id = isset($_GET['id'])?intval($_GET['id']):false;
        if($id){
            $packet = M('packet')->where(array('id' => $id))->find();
            $wechat = M('domain')->field('id,url')->where("type={$packet['type']} AND look=0")->select();

            $wechar_arr = array();
            foreach($wechat as $v){
                $wechar_arr[$v['id']] = $v['url'];
            }

            $packet['wechart_id'] = unserialize($packet['wechart_id']);
            $this->assign('wechat',$wechat);
            $this->assign('wechar_arr',$wechar_arr);
            $this->assign('packet',$packet);
        }else{
            $this->error('参数错误！');
        }
        $this -> display();
    }

    public function update(){

        $id = I('post.id','','intval');
        $action = I('post.action', 'index');

        $data['alias'] = I('post.alias');
        $wechart_arr = $_POST['wechar_id'];
        $data['wechart_id'] = serialize($wechart_arr);

        if($id){

            M('packet')->data($data)->where( array('id' => $id))->save();

            addlog('编辑域名分组 ID：'.$id.'  名称：'.$data['alias'],false,1);
        }else{
            $data['type'] = I('post.type', 0);
            M('packet')->data($data)->add();

            addlog('新增域名分组 ID：'.$id.'  名称：'.$data['alias'],false,1);
        }
        $this->success('操作成功！',U($action));
    }

    public function del(){

        $id = isset($_REQUEST['id'])?$_REQUEST['id']:false;
        if(is_array($id))
        {
            foreach($id as $k=>$v){
                $id[$k] = intval($v);
            }
            if(!$id){
                $this->error('参数错误！');
            }
        }

        $map['id']  = array('in',$id);

        if(M('member')->where('packet_id = '.$id)->count()){
            $this->error('分销商已经引用域名分组！');
            exit();
        }

        $pact = M('packet')->where($map)->find();
        if(M('packet')->where($map)->delete()){

            addlog('删除域名分组ID：'.$id. '  名称：'.$pact['alias'],false,1);
            $this->success('恭喜，域名分组删除成功！');
        }else{
            $this->error('参数错误！');
        }
    }


}