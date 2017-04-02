<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-8-6
 * Time: 下午2:36
 */
namespace Ln\Controller;

use EasyWeChat\Core\Exception;
use Ln\Controller\ComController;

class SparedController extends ComController {

    public function cdn_Spared(){
        $data['type'] = 1;
        $data['domainlist'] = M('domain')->where(array("type" => $data['type']))->select();
        $this->assign($data);
        $this->display();
    }

    /*
     * 域名恢复
     */
    public function Spared_save()
    {
        $data = I('get.');
        $data['look']=2;
        if (M('domain')->where(array('id' => $data['id'],'look'=>3))->save($data) !== false) {
            $this->success("更新成功");
        }
    }

    public function index(){

        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $pagesize = 30;#每页数量
        $offset = $pagesize*($p-1);//计算记录偏移量

        $data['type'] =I('get.type')?I('get.type'):0;
        $count= $data['domainlist'] =  M('domain')
            ->where(array("type" => $data['type'],"look"=>3))
            ->count();
        $data['domainlist'] =  M('domain')
            ->where(array("type" => $data['type'],"look"=>3))
            ->limit($offset.','.$pagesize)
            ->select();

        $page    =    new \Think\Page($count,$pagesize);
        $page = $page->show();


        $this->assign('page',$page);
        $this->assign($data);
        $this->display();
    }
} 