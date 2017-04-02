<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/27
 * Time: 12:25
 */

namespace Ln\Controller;

use Think\Controller;

class OtherController extends Controller
{
    public $data;
    public function _initialize(){
        if(IS_POST){
            $this->data = I('post.','');
        }else{
            $this->data = I('get.','');
        }
    }

    /**
     * ajax 异步获取百度统计Id
     */
    public function get_baidu_id(){
        $msg = ['error' => 1];
        if($this->data['refid']){
            $baidu_id = M('member')->where(['uid' => $this->data['refid']])->getField('baidu_statistics_id');
            if($baidu_id){
                $msg = [ 'error' => 0 , 'baidu_id' => $baidu_id ];
            }
        }
        $this->ajaxReturn($msg,'jsonp');
    }
}