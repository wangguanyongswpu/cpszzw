<?php
/**
 * 网站公告相关
 * User: ZhangHR
 * Date: 2016/11/3
 */
namespace Ln\Controller;
use Ln\Controller\ComController;

class NoticeController extends ComController {
    public function index()
    {
        $notice = M('article')->field('aid,content,t')->where('sid=15000')->order('aid desc')->select();
		foreach($notice as $k=>$v){
			$notice[$k]['t']=date('Y-m-d H:i:s',$v['t']);
		}
        $this->assign('list', $notice);

        $this -> display();
    }

    public function add(){
        $this -> display();
    }

    public function del(){
        $ids = isset($_REQUEST['ids'])?$_REQUEST['ids']:false;
        if($ids){
            if(is_array($ids)){
                $ids = implode(',',$ids);
                $map['aid']  = array('in',$ids);
				$map['sid']  =15000;
            }else{
                $map = 'aid='.$ids.' and sid=15000';
            }

            if(M('article')->where($map)->delete()){
                addlog('删除网站公告，ID：'.$ids);
                $this->success('恭喜，网站公告删除成功！');
            }else{
                $this->error('参数错误！');
            }
        }else{
            $this->error('参数错误！');
        }
    }

    public function edit($id = 0)
    {
        $id = intval($id);
        $notice = M('article')->where('aid='.$id.' and sid=15000')->find();
        if($notice){
            $this->assign('article',$notice);
        }else{
            $this->error('参数错误！');
        }

        $this -> display();
    }

    public function update($id=0){
        $id = intval($id);
        $data['content'] = isset($_POST['content'])?$_POST['content']:'';

        if(!$data['content']){
            $this->error('警告！必填项不能为空。');
        }

        if($id){
            M('article')->data($data)->where('aid='.$id.' and sid=15000')->save();
            addlog('编辑网站公告，ID：'.$id);
            $this->success('恭喜！网站公告编辑成功！');

        }else{
			$data['sid']=15000;
			$data['t']=time();
            $id = M('article')->data($data)->add();
            if($id){
                addlog('新增网站公告，ID：'.$id);
                $this->success('恭喜！网站公告新增成功！');
            }else{
                $this->error('抱歉，未知错误！');
            }
        }
    }

    public function getshow()
    {
        $notice = M('article')->field('aid,content')->where('sid=15000')->order('aid desc')->limit(5)->select();
		foreach($notice as $k=>$v){
			$notice[$k]['t']=date('Y-m-d H:i:s',$v['t']);
		}
        $this->assign('list', $notice);
    }
}
