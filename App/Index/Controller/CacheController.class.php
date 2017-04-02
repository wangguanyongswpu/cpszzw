<?php
namespace Index\Controller;

use Think\Controller;
use Think;

class CacheController extends Controller
{

    function index(){
        echo 7777;exit;
    }
    /**
     *  获取用户信息
     */
    public function geturl()
    {
        $uid    = intval(I('uid'));

        $domain =  $this->GetDomain(2,1);
        if(true){
            $geturl = "http://{$domain}/share/ad/{$uid}";
        }
        //使用陈经理连接
        else{

            $app_id = C('PAY_API_URL_APP_ID');
            $sign   = $this->get_cps_sign(array('uid' => $uid));
            $url    = C('CPS_API_URL') . "index.php?m=Ln&c=api&a=getJumpDomain&uid={$uid}&app_id={$app_id}&sign={$sign}";
            $date   = json_decode($this->httpGet($url), true);
            $geturl = $date['data'][array_rand($date['data'])];
        }
        $geturl="http://99sds.qbjlsk.com/201609110926/{$uid}.gif";
        $urlname = C('JS_AD_IMG_HOST');
        $arr     = $this->getfilecounts("Public/images/gif");
        $gif     = "http://" . $urlname . "/Public/images/gif/" . $arr[array_rand($arr)];

        echo "document.writeln('<a target=\"_blank\" href=\"" . $geturl . "\" style=\"width:100%;max-width:1962px;\"><img src=\"" . $gif . "\" width=\"100%\" ></a>')";
    }

    //查询文件内容
    public function getfilecounts($ff)
    {
        $dir    = './' . $ff;
        $handle = opendir($dir);
        $i      = 0;
        $arr    = array();
        while (false !== $file = (readdir($handle))) {
            if ($file !== '.' && $file != '..') {
                $arr[] = $file;
            }
        }
        closedir($handle);
        return $arr;
    }

    public function getimgl($pic,$url,$nub)
    {

        $path     = dirname(APP_PATH);
        $pic      = $path . $pic; //二维码地址
        $urlid    = $url;
        $t_path   = '/Public/images/';
        $urls=explode("/",$pic);
        $flog=false;
        $nub=$nub?(int)$nub:rand(1,3);
        if($urlid==250){
            $flog=true;
            $nub=3;
        }

        // $filename = 'chenqrcode/' . $urlid .'/'.$urls[0].$nub.'.png';
        $filename = 'chenqrcode/' . md5($urlid) .'/'.md5($urls[0]).$nub.'.png';
        $img_url  = $t_path . $filename;
        if (!file_exists($path . $t_path . $filename)) {

            $background=$path.$t_path."bgimg".$nub.".jpg";
            $imgdata=[];
            $imgdata[1]['dst_x']=10;
            $imgdata[1]['dst_y']=750;
            $imgdata[1]['src_x']=0;
            $imgdata[1]['src_y']=0;
            $imgdata[1]['dst_w']=270;
            $imgdata[1]['dst_h']=270;

            $imgdata[2]['dst_x']=302;
            $imgdata[2]['dst_y']=543;
            $imgdata[2]['src_x']=0;
            $imgdata[2]['src_y']=0;
            $imgdata[2]['dst_w']=220;
            $imgdata[2]['dst_h']=220;

            $imgdata[3]['dst_x']=75;
            $imgdata[3]['dst_y']=240;
            $imgdata[3]['src_x']=0;
            $imgdata[3]['src_y']=0;
            $imgdata[3]['dst_w']=500;
            $imgdata[3]['dst_h']=500;


            $img_url = $this->newimg($t_path.$filename,$pic,$background,$imgdata[$nub],"jpg",$flog);

        }
        if(!$flog){
            return $img_url;
            //echo json_encode($img_url);
        }

    }

    public function imgstyle($str){
        $arr=explode(".",$str);
        return $arr[count($arr)-1];
    }

    public function get_tghb(){
        $group_domain = I("get.group_domain");
        $uid = I("get.uid");
        $get_url = I("get.url");
        $time = time();
        //根据域名组合推广域名、推广海报
        $path = dirname(APP_PATH);
        $url = 'http://' . $group_domain. '/' . $time . '/' . $uid . '_'.rand(10,100).'.jpg';
        $t_path = '/Public/Ln/images/';
        $filename = 'chenqrcode/' . $group_domain . '/' . $uid . '_' . $group_domain . '.png';
        if (file_exists($path . $t_path . $filename)) {
            $img_url = $t_path . $filename;
        } else {
            $img_url = $this->QrCode($filename, $url);
        }
        $img_arr = [];
        for($i=1;$i<4;$i++){
            $img_arr[] = $this->getimgl($img_url,$get_url,$i);
        }
        echo json_encode($img_arr);
        die;
    }
    /**
     * 生成二维码
     * @param $filename 二维码图片保存地址
     * @param $url 二维码Url地址
     * @return string
     */
    public function QrCode($filename, $url){
        vendor('phpqrcode.phpqrcode');
        if(empty($filename) || empty($url)){
            return false;
        }


        $path = './Public/Ln/images/';

        // 二维码数据
        // 生成的文件名
        $file_name = $path . $filename;

        if(!is_dir(dirname($file_name))){
            if(!@mkdir(iconv("UTF-8", "GBK", dirname($file_name)),0777,true)){
                return false;
            };
        }

        $errorCorrectionLevel =intval(3) ;//容错级别
        $matrixPointSize = intval(5);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        $object::png($url, $file_name, $errorCorrectionLevel, $matrixPointSize, 2);

        return '/Public/Ln/images/'.$filename;
    }
    public function newimg($filename,$pic,$background,$imgdata,$showtype="",$flog)
    {
        //$path = dirname(__FILE__);
        $filenames=$_SERVER['DOCUMENT_ROOT'].$filename;
        //绘图技术 基本步骤 前提:在php.ini文件中启用gd库
        //创建画布 默认背景是黑色的
        $bgsty=$this->imgstyle($background);
        if($bgsty=="jpg"){
            $img=imagecreatefromjpeg($background);
        }elseif($bgsty=="png"){
            $img=imagecreatefrompng($background);
        }elseif($bgsty=="gif"){
            $img=imagecreatefromgif($background);
        }


        $picsty=$this->imgstyle($pic);
        //拷贝图片到画布
        if($picsty=="jpg"){
            $scrImg=imagecreatefromjpeg($pic);
        }elseif($picsty=="png"){
            $scrImg=imagecreatefrompng($pic);
        }elseif($picsty=="gif"){
            $scrImg=imagecreatefromgif($pic);
        }

        $scrImgInfo=getimagesize($pic);

        imagecopyresampled ($img,$scrImg,$imgdata['dst_x'],$imgdata['dst_y'],$imgdata['src_x'],$imgdata['src_y'],$imgdata['dst_w'],$imgdata['dst_h'],$scrImgInfo[0],$scrImgInfo[1]);

        //输出图像到网页(或者另存为)


        if($flog){
            header("content-type: image/png");
            if($showtype=="png"){
                imagepng($img,null,2);
            }elseif($showtype=="gif"){
                imagegif($img);
            }elseif($showtype=="jpg"){
                imagejpeg($img,null,75);
            }

            //imagedestroy($img);
        }else{
            if(!is_dir(dirname($filenames))){
                $flog=mkdir(dirname($filenames),0777,true);
                if(!$flog){
                    return false;
                };
            }

            if($showtype=="png"){
                imagepng($img,$filenames,2);
            }elseif($showtype=="gif"){
                imagegif($img,$filenames);
            }elseif($showtype=="jpg"){
                imagejpeg($img,$filenames,75);
            }

            //销毁该图片(释放内存)
            imagedestroy($img);
            return  $filename;
        }

    }

}
