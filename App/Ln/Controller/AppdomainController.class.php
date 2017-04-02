<?php
namespace Ln\Controller;

use Think\Page;

class AppdomainController extends ComController
{
    //  App域名列表
    public function lists()
    {
        $PageCount = M('zyxz_domain')->count();

        $Page = new Page($PageCount,15);

        $rows = M('zyxz_domain')->limit($Page->firstRow, $Page->listRows)->select();

        $this->assign([
            'rows' => $rows,
            'page' => $Page->show()
        ]);

        $this->display();
    }

    //  状态修改
    public function domain_save()
    {
        if (I('get.status') . '' == '1') {
            $saveAll = M('zyxz_domain')->where(['status'=>1])->save([
                'status' => '2'
            ]);

            if ($saveAll === false) {
                $this->error('更新失败！', '', 1);
                exit;
            }
        }

        $save = M('zyxz_domain')->where(['id' => intval(I('get.id'))])->save([
            'status' => I('get.status')
        ]);

        if ($save == false) {
            $this->error('更新失败！', '', 1);
            exit;
        }

        $this->success('更新成功！', '', 1);
        exit;
    }

    //  App域名增加
    public function add()
    {
        $url = I('post.domain_name');

        $status = I('post.status');

        $data = [];

        foreach(explode(",", $url) as $v){
            $data[] = [
                'domain_name' => trim($v),
                'type' => $status,
            ];
        }

        if(M('zyxz_domain')->addAll($data) !== false){
            $this->success('添加成功！');
            exit;
        };

        $this->error('添加失败！');
        exit;
    }

    //  App域名删除
    public function delete()
    {
        if (M('zyxz_domain')->delete(intval(I('get.id'))) == 0) {
            $this->error('数据错误！');
            exit;
        };
        $this->success('删除成功！');
        exit;
    }

}