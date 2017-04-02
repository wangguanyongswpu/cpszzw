<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-8-6
 * Time: 下午2:36
 */
namespace Ln\Controller;

use Ln\Controller\ComController;

class DoMainController extends ComController
{
    public $status;
    public $typeurl;

    public function __construct()
    {
        parent::__construct();

        $status[0] = '启用';
        $status[1] = '关闭';
        $status[2] = '备用';
        $status[3] = '弃用';

        $this->status = $status;

        $type[0] = '入口域名';
        $type[1] = 'CDN域名';
        $type[2] = '跳转域名';
        $type[3] = '微信群域名';
        $type[4] = '朋友圈域名';

        $this->typeurl = $type;
    }

    public function cdn_domain()
    {
        $data['type'] = 1;
        $data['domainlist'] = M('domain')->where("type=" . $data['type'] . " and look < 3")->select();
        $this->assign($data);
        $this->display();
    }

    /*
     * 域名添加
     */
    public function add_domain()
    {
        $url = I("post.url");
        $type = I("post.type", 0);
        $action = I('post.action', 'index');
        if (empty($url)) {
            $this->error("请输入完整");
        }

        if (I('post.look') == '0') {
            $_POST['open_time'] = time();
        } else {
            $_POST['open_time'] = '';
        }

        /*$data = [];
        if(!I('post.open_time')){
            //  0 启用 1关闭 2备用
            foreach (explode(",", $url) as $v) {
                $data[] = [
                    'url' => trim($v),
                    'type' => $type,
                    'look' => I('post.look', 2),
                ];
            }
        }else{
            //  0 启用 1关闭 2备用
            foreach (explode(",", $url) as $v) {
                $data[] = [
                    'url' => trim($v),
                    'type' => $type,
                    'look' => I('post.look', 2),
                    'open_time' => I('post.open_time')
                ];
            }
        }*/

        foreach (explode(",", $url) as $k => $v) {
            if (I('post.open_time')) {
                $data[$k]['open_time'] = I('post.open_time');
            }
            $data[$k]['url'] = trim($v);
            $data[$k]['type'] = $type;
            $data[$k]['look'] = I('post.look', 2);
        }

        $label = array(
            '0' => array('type' => 'domain', 'link' => U('index')),
            '1' => array('type' => 'cdndomain', 'link' => U('cdn_domain')),
            '2' => array('type' => 'jump', 'link' => U('jump')),
        );

        if (M("domain")->addAll($data) !== false) {
            foreach ($data as $k) {
                addlog($this->typeurl[$type] . '添加成功' . $k['url'], '', 1);
                //logrecord($k['url'],1,$label[$type]['type']);
            }
            $this->success("添加成功", U($action));
        } else {
            $this->error("添加错误", U($action));
        }
    }

    /*
     * 域名更新
     */
    public function domain_save()
    {
        //  0 启用 1关闭 2备用 3弃用
        $data = I('get.');

        if ($data && $data['look'] == 0 . '') {

            $data['open_time'] = time();
            //$data['time'] = time();

        } else if ($data && $data['look'] == 3 . '') {

            $data['save_time'] = time();
            //$data['time'] = time();

        }

        if (M('domain')->where(array('id' => $data['id']))->save($data) !== false) {

            $domain = M('domain')->where(array('id' => $data['id']))->find();
            addlog($this->typeurl[$domain['type']] . '被' . $this->status[$data['look']] . ':' . $domain['url'], '', 1);

            $this->success("更新成功");
        }
    }

    public function index()
    {
        $data['type'] = 0;
        $data['action'] = 'index';
        $data['domainlist'] = M('domain')->where("type=" . $data['type'] . " and look < 3")->select();

        foreach ($data['domainlist'] as $k => $v) {
            $data['domainlist'][$k]['statu'] = $this->status[$v['look']];
            $data['domainlist'][$k]['status'] = $this->status;
        }

        $this->assign($this->status);
        $this->assign($data);
        $this->display();
    }

    /**
     * 跳转域名
     */
    public function jump()
    {
        $data['type'] = 2;
        $data['action'] = 'jump';
        $data['domainlist'] = M('domain')->where("type=" . $data['type'] . " and look < 3")->select();


        foreach ($data['domainlist'] as $k => $v) {
            $data['domainlist'][$k]['statu'] = $this->status[$v['look']];
            $data['domainlist'][$k]['status'] = $this->status;
        }
        $this->assign($this->status);
        $this->assign($data);
        $this->display('index');
    }

    /**
     * 朋友圈域名
     */
    public function share()
    {
        $data['type'] = 4;
        $data['action'] = 'share';
        $data['domainlist'] = M('domain')->where("type=" . $data['type'] . " and look < 3")->select();


        foreach ($data['domainlist'] as $k => $v) {
            $data['domainlist'][$k]['statu'] = $this->status[$v['look']];
            $data['domainlist'][$k]['status'] = $this->status;
        }
        $this->assign($this->status);
        $this->assign($data);
        $this->display('index');
    }

    /**
     * 微信群域名
     */
    public function wechat()
    {
        $data['type'] = 3;
        $data['action'] = 'wechat';
        $data['domainlist'] = M('domain')->where("type=" . $data['type'] . " and look < 3")->select();

        foreach ($data['domainlist'] as $k => $v) {
            $data['domainlist'][$k]['statu'] = $this->status[$v['look']];
            $data['domainlist'][$k]['status'] = $this->status;
        }

        $this->assign($this->status);
        $this->assign($data);
        $this->display('index');
    }

    /**
     * 微信群域名拦截查询日志
     */
    public function wechatlists()
    {
        $time = strtotime(date('Y-m-d', strtotime('-3days')));

        $rows = M('domain')
            ->where(['save_time>' . $time])
            ->order('save_time desc')
            ->select();

        $this->assign('rows', $rows);

        $this->display();
    }

    /**
     * 检查域名是否被拦截
     **/
    public function checkDoMain()
    {
        $url = $_GET['url'];
        $ret_data = array('code' => 3);
        if (!empty($url)) {
            $ret_code = file_get_contents('http://hao.wxyun.org/hao.do?url=' . $url);
            $ret_code = json_decode($ret_code, true);
            if ($ret_code[0] == 0 || $ret_code[0] == 1) {
                $ret_data['code'] = 0;
            } elseif ($ret_code[0] == 2) {
                $ret_data['code'] = 1;
            }
        }
        echo json_encode($ret_data);
    }


}