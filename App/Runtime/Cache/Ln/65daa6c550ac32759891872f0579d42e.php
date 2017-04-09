<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8"/>
    <title><?php echo ($current['title']); ?>-<?php echo (C("title")); ?></title>
    <meta name="keywords" content="<?php echo (C("keywords")); ?>"/>
    <meta name="description" content="<?php echo (C("description")); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <script src="/Public/Ln/js/jquery-1.9.1.min.js"></script>
    <script src="/Public/Ln/js/clipboard.min.js"></script>
    		<!-- bootstrap & fontawesome -->

		<link rel="stylesheet" href="/Public/Ln/css/bootstrap.css" />

		<link rel="stylesheet" href="/Public/Ln/css/font-awesome.css" />



		<!-- page specific plugin styles -->



		<!-- text fonts -->

		<link rel="stylesheet" href="/Public/Ln/css/ace-fonts.css" />



		<!-- ace styles -->

		<link rel="stylesheet" href="/Public/Ln/css/ace.css?v1" class="ace-main-stylesheet" id="main-ace-style" />



		<!--[if lte IE 9]>

			<link rel="stylesheet" href="/Public/Ln/css/ace-part2.css" class="ace-main-stylesheet" />

		<![endif]-->



		<!--[if lte IE 9]>

		  <link rel="stylesheet" href="/Public/Ln/css/ace-ie.css" />

		<![endif]-->



		<!-- inline styles related to this page -->



		<!-- ace settings handler -->

		<script src="/Public/Ln/js/ace-extra.js"></script>



		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->



		<!--[if lte IE 8]>

		<script src="/Public/Ln/js/html5shiv.js"></script>

		<script src="/Public/Ln/js/respond.js"></script>

		<![endif]-->
    <style type="text/css">
        .media-table tbody tr td {
            border: none;
        }

        button,input, optgroup, select, textarea {
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border: none !important;
        }

        a {
            cursor: pointer;
            text-decoration: none;
        }

        a:link {
            text-decoration: none;
            cursor: pointer;
        }

        .table {
            margin-bottom: 0;
        }

        .btn-blue {
            border-radius: 2px;
            cursor: pointer;
            border: none;
            background-color: #438eb9 !important;
        }

        .margin-top {
            /*margin-top: 30px;*/
        }

        .photo {
            width: 80%;
            /*height: 150px;*/
            display: block;
            margin: 5px auto;
            border: none!important;
        }
        .photo1 {
            text-align: center;
            /*width: 80%;*/
            /*height: 150px;*/
            display: block;
            margin: 5px auto;
            border: none!important;
        }

        .photo img {
            border: none !important;
        }
        .photo1 img{
            /*width: 100%;*/
            border: none !important;
        }
        .panel-group img{height:90%!important;width:90%}
		#wxg-link2{
			height:60px;
			line-height:60px;
			background:#c9cc63;
			padding-left:10px;
			border-radius:10px;
			font-size:16px;
			color:red;
			font-weight:bold;
			outline:none;
			border:none;
		}
    </style>
</head>
<body class="no-skin">
		<!-- #section:basics/navbar.layout -->
		<div id="navbar" class="navbar navbar-default">
			<script type="text/javascript">
				try{ace.settings.check('navbar' , 'fixed')}catch(e){}
			</script>

			<div class="navbar-container" id="navbar-container">
				<!-- #section:basics/sidebar.mobile.toggle -->
				<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
					<span class="sr-only">Toggle sidebar</span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>
				</button>

				<!-- /section:basics/sidebar.mobile.toggle -->
				<div class="navbar-header pull-left">
					<!-- #section:basics/navbar.layout.brand -->
					<a href="<?php echo U('Tg/index');?>" class="navbar-brand">
						<small>
							<i class="fa fa-home" style="font-size: 40px;"></i>
							<?php echo (C("title")); ?>
						</small>
					</a>

					<!-- /section:basics/navbar.layout.brand -->

					<!-- #section:basics/navbar.toggle -->

					<!-- /section:basics/navbar.toggle -->
				</div>
				<!--<?php if($notice_list_bool): ?><div class="navbar-header jcarousel-clip jcarousel-clip-horizontal">
					<i class="menu-icon fa fa-bullhorn"></i>
					<marquee class="notice_list" scrollamount="3" direction="left" behaviour="Scroll">
						<?php if(is_array($notice_list)): $i = 0; $__LIST__ = $notice_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><b>&middot;</b>&nbsp;<?php echo ($val['content']); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php endforeach; endif; else: echo "" ;endif; ?>
					</marquee>
				</div><?php endif; ?>-->
				<!-- #section:basics/navbar.dropdown -->
				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
						<!-- #section:basics/navbar.user_menu -->
						<!--
						<li class="red">
							<a href="/" title="前台首页" target="_blank">
								<i class="ace-icon fa fa-home"></i>
							</a>
						</li>
						-->
						<!--<li class="light-blue">
							<a data-toggle="dropdown" href="#" class="dropdown-toggle">
								<img class="nav-user-photo" src="<?php if( $user["head"] == '' ): ?>/Public/Ln/avatars/avatar2.png<?php else: ?><?php echo ($user["head"]); endif; ?>" alt="<?php echo ($user["user"]); ?>" />
								<span class="user-info">
									<small>欢迎光临，<?php echo ($user["user"]); ?></small>
								</span>
								<i class="ace-icon fa fa-caret-down"></i>
							</a>
							<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
								<li>
									<a href="<?php echo U('Setting/setting');?>">
										<i class="ace-icon fa fa-cog"></i>
										设置
									</a>
								</li>

								<li>
									<a href="<?php echo U('Personal/profile');?>">
										<i class="ace-icon fa fa-user"></i>
										个人资料
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?php echo U('logout/index');?>">
										<i class="ace-icon fa fa-power-off"></i>
										退出
									</a>
								</li>
							</ul>
						</li>-->
                      	<a href="<?php echo U('logout/index');?>">
							<i class="ace-icon fa fa-sign-out"></i><br>
							退出
						</a>
						<!-- /section:basics/navbar.user_menu -->
					</ul>
				</div>

				<!-- /section:basics/navbar.dropdown -->
			</div><!-- /.navbar-container -->
		</div>
		<!-- /section:basics/navbar.layout -->
<div class="main-container" id="main-container">
    <script type="text/javascript">
        try {
            ace.settings.check('main-container', 'fixed')
        } catch (e) {
        }
    </script>
    <!-- #section:basics/sidebar -->
<div id="sidebar" class="sidebar responsive">
    <script type="text/javascript">
        try {
            ace.settings.check('sidebar', 'fixed')
        } catch(e){}
    </script>
    <!-- /.sidebar-shortcuts -->
    <?php if($user['gid'] == 1){ ?>
    <ul class="nav nav-list">
		<li style="text-align:center;height:120px;line-height:120px;color:#fff;background:#30343a;border-bottom: 1px solid #fff;"><img src="/Public/Ln/avatars/avatar2.png" style="margin:0 10px;border-radius:50%;">欢迎你</li>
        <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li <?php if(($v['id'] == $current['id']) OR ($v['id'] == $current['pid']) OR ($v['id'] == $current['ppid'])): ?>class="active
            <?php if($current['pid'] != '0'): ?>open<?php endif; ?>"<?php endif; ?>>
            <a href="<?php if(empty($v["name"])): ?>#<?php else: echo U($v['name']); endif; ?>" <?php if(!empty($v["children"])): ?>class="dropdown-toggle"<?php endif; ?>>
            <i class="<?php echo ($v["icon"]); ?>"></i>
            <span class="menu-text"><?php echo ($v["title"]); ?></span>
            <?php if(!empty($v["children"])): ?><b class="arrow fa fa-angle-down"></b><?php endif; ?>
            </a>
            <b class="arrow"></b>
            <?php if(!empty($v["children"])): ?><ul class="submenu">
                    <?php if(is_array($v["children"])): $i = 0; $__LIST__ = $v["children"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($i % 2 );++$i;?><li <?php if(($vv['id'] == $current['id']) OR ($vv['id'] == $current['pid'])): ?>class="active
                        <?php if($current['ppid'] != '0'): ?>open<?php endif; ?>"<?php endif; ?>>
                        <a href="<?php if(empty($vv["children "])): if($vv['name'] == 'Tg/index/yestotay' ): echo U(str_replace('/yestotay', '?startime=' . date('Y-m-d 00:00:00', strtotime('-1 day')) . '&endtime=' . date('Y-m-d 00:00:00'),$vv['name'])); else: echo U($vv['name']); endif; else: ?>#<?php endif; ?>" <?php if(!empty($vv["children"])): ?>class="dropdown-toggle"<?php endif; ?>>
                        <i class="<?php echo ($vv["icon"]); ?>"></i><?php echo ($vv["title"]); ?>
                        <?php if(!empty($vv["children"])): ?><b class="arrow fa fa-angle-down"></b><?php endif; ?>
                        </a>
                        <b class="arrow"></b>
                        <?php if(!empty($vv["children"])): ?><ul class="submenu">
                                <?php if(is_array($vv["children"])): $i = 0; $__LIST__ = $vv["children"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vvv): $mod = ($i % 2 );++$i;?><li <?php if($vvv['id'] == $current['id']): ?>class="active"<?php endif; ?>>
                                    <a href="<?php echo U($vvv['name']);?>">
                                        <i class="<?php echo ($vvv["icon"]); ?>"></i><?php echo ($vvv["title"]); ?></a>
                                    <b class="arrow"></b>
                                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul><?php endif; ?>
                        </li><?php endforeach; endif; else: echo "" ;endif; ?>
                </ul><?php endif; ?>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
    <?php } ?>
    <?php if($user['gid'] == 2 || $user['gid'] == 3){ ?>
    <ul class="nav nav-list">
		<li style="text-align:center;height:120px;line-height:120px;color:#fff;background:#30343a;border-bottom: 1px solid #fff;"><img src="/Public/Ln/avatars/avatar2.png" style="margin:0 10px;border-radius:50%;">欢迎你</li>
        <li>
            <a  href="<?php echo U('Tg/generalize');?>" >
                <i class="menu-icon fa fa-laptop"></i>
                <span class="menu-text">推广链接</span>
            </a>
        </li>
        <li>
            <a href="<?php echo U('Tg/index');?>">
                <i class="menu-icon fa fa-th-list"></i>
                <span class="menu-text">充值列表</span>
            </a>
        </li>
		<?php if($user['gid'] == 2){ ?>
        <li>
            <a  href="<?php echo U('Member/index');?>" >
                <i class="menu-icon fa fa-renren"></i>
                <span class="menu-text">推广商</span>
            </a>
        </li>
		<?php } ?>
        <li >
            <a href="<?php echo U('personal/profile');?>">
                <i class="menu-icon fa fa-user"></i>
                <span class="menu-text">个人中心</span>
            </a>
        </li>
    </ul>
    <?php } ?>


    <!-- /.nav-list -->
    <!-- #section:basics/sidebar.layout.minimize -->
    
    <!-- /section:basics/sidebar.layout.minimize -->
    
</div>
<!-- /section:basics/sidebar -->
    <!-- /section:basics/sidebar -->
    <div class="main-content">
        <div class="main-content-inner">
            <!-- /section:basics/content.breadcrumbs -->
            <div class="main-content">
                <div class="main-content-inner">                    <!-- #section:basics/content.breadcrumbs -->
                    <div class="breadcrumbs" id="breadcrumbs">
						<script type="text/javascript">
							try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
						</script>

						<ul class="breadcrumb">
							<li>
								<i class="ace-icon fa fa-home home-icon"></i>
								<a href="<?php echo U('Tg/index');?>">首页</a>
							</li>
							<li class="active"><?php echo ($current['title']); ?></li>
						</ul><!-- /.breadcrumb -->
					</div>                    <!-- /section:basics/content.breadcrumbs -->
                    <div class="page-content">
<div class="panel-group" id="accordion">

                <div class="panel panel-default">
					<div class="panel-heading"><h4><span class="fa fa-link"> 直链推广</span></h4></div>
					<div class="row detail-package navbar-btn" style="margin:0px">

                        <div class="get-url-div get-url-div1" div-num="1" style="padding: 6px 10px;background: #893cb5;color:#fff;width: 118px;border-radius: 5px;font-size: 16px;margin-left: 35px;">点击获取链接</div>

                        <div class="col-xs-12 url-div1" style="display:none;">
							<div class="ml-lg col-sm-11">
								<table class="table media-table">
									<tbody>
									<!--<tr>
										<td class="wxg-link" colspan="2">
											<div class="help-block mt-lg">直链推广（可以分享到微信群推广）</div>
											<input id="wxg-link1" class="copy-text longlink"  value="<?php echo ($v["url"]); ?>">
											<div class="btn-group margin-top">
											<br>
											<button class="btn btn-blue J-copy-tbn"  data-clipboard-text="<?php echo ($v["url"]); ?>"
													data-clipboard-target="#wxg-link1">
												复制地址
											</button>
											</div>
										</td>
									</tr>-->

                                    <tr>
                                        <td class="wxg-link" colspan="2" style="text-align:left;">
                                            <div class="help-block mt-lg" style="text-align:left;">短链推广（可以分享到微信群推广）</div>
											<div style="position:relative;">
                                            <input id="wxg-link2" class="copy-text shortlink"  value="<?php echo ($v["shorturl"]); ?>">
											
                                            <div class="btn-group margin-top" style="position:absolute;top:0;right:0">
                                            
                                            <button class="btn btn-blue J-copy-tbn"  data-clipboard-text="<?php echo ($v["shorturl"]); ?>"
                                                    data-clipboard-target="#wxg-link2" style="float:left;height:60px;padding:0 30px;">
                                                复制地址
                                            </button>
                                            </div>
											</div>
                                        </td>
                                    </tr>

									</tbody>
								</table>
							</div>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4><span class="fa fa-link"> 推广图片</span></h4></div>
					<div class="row detail-package navbar-btn" style="margin:0px">

                        <div class="get-url-div get-url-div3" div-num="3" style="padding: 6px 10px;background: #893cb5;color:#fff;width: 118px;border-radius: 5px;font-size: 16px;margin-left: 35px;">点击获取链接</div>

                        <div class="col-xs-12 url-div3" style="display: none;padding: 4px;">
							<div class="ml-lg col-sm-12" style="padding: 0;">

								<div class="col-md-3 center panel-group">
									<img class="img-responsive" src="<?php echo ($tgqc[0]); ?>" style="margin: 0 auto;">
									<input id='img-link-0' class="copy-text" value="<?php echo ($tgqc[0]); ?>">
									<button class="btn btn-blue J-copy-tbn"  data-clipboard-text="<?php echo ($tgqc[0]); ?>"
											data-clipboard-target="#img-link-0">
										复制地址
									</button>
								</div>
								<div class="col-md-9 center panel-group">
									<img class="img-responsive" src="<?php echo ($tgqc[1]); ?>" style="margin: 0 auto;">
									<input id='img-link-1' class="copy-text" value="<?php echo ($tgqc[1]); ?>">
									<button class="btn btn-blue J-copy-tbn"  data-clipboard-text="<?php echo ($tgqc[1]); ?>"
											data-clipboard-target="#img-link-1">
										复制地址
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

</div>
    </div><!-- /.main-content -->
            
        </div><!-- /.main-container -->
        <div class="alertMassage" style="display:none;">
            <span>是否复制到剪切板</span>
            <span id="msg"></span>

            <p>
                <button class="enter" value="1" onclick="">确认</button>
                <button class="del" value="0">取消</button>
            </p>
        </div>
        <div class="modal fade" id="loading" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop='static'>
            <div role="document" style="position: relative;width: 100%;height: 100%;text-align: center;display: table;">
                <div style="display: table-cell;vertical-align: middle;">
					<img style="width:5%;min-width:150px" src="/Public/img/loading.gif"/>
				</div>
            </div>
        </div>
        <script>
            var domain_api = "<?php echo ($domain_api); ?>?packet_id=<?php echo ($packet_id); ?>";//域名综合管理后台请求接口
            var log_api = "<?php echo U('Tg/add_domain_log');?>";
         
            function get_domain(div_num){
                $('#loading').modal('show');
                $.ajax({
                    url:domain_api,
                    dataType:'jsonp',
                    success:function(data){
                        if(data.group_domain){
                            localStorage.setItem("group_domain",data.group_domain);
                            //添加域名获取日志
                            $.getJSON(log_api+'&group_domain='+data.group_domain,function(json){
                                //$("#wxg-link1").val(json.longurl);/*长链接*/
                                $("#wxg-link2").val(json.shorturl);/*长链接*/
                                /*$("#js-link").val(json.js_domain);
                                $("#js-link1").attr('data-clipboard-text',json.js_domain);*/
                                /*推广海报*/
                                $(".url-div3 .panel-group").each(function(k,v){
                                    $(this).find("img").attr("src",json.tg_img[k]);
                                    $(this).find("input").val(json.tg_img[k]);
                                    $(this).find("button").attr("data-clipboard-text",json.tg_img[k]);
                                });
                                $('#loading').modal('hide');
                            });
                        }
                    }
                });
            }
            $(function () {
                /***************** 点击显示推广链接 ******************/
                localStorage.setItem("group_domain",'');
                $(".get-url-div").on("click",function(){
                    var div_num = $(this).attr("div-num");
                    var group_domain = localStorage.getItem("group_domain");
                    if(!group_domain){
                        get_domain(div_num);
                    }
                    $(".get-url-div"+div_num).hide();
                    $(".url-div"+div_num).show();
                });
                /***************** 点击显示推广链接 end ******************/

                $(".btn-blue").click(function () {
                    var Url = $(this).parents("tr").prev().find("input").val();
                    console.log(Url);
                    //copyToClipboard(Url);
                    //$(".alertMassage").css({display:"block"});
                });
                
                $(".copy-text").on("click", function () {
                   $(this).select();
                });
                $(".copy-text").on("focus", function () {
                   $(this).select();
                });

                var clipboard = new Clipboard('.J-copy-tbn');
                clipboard.on('success', function(e) {
                    alert('复制成功');

                    e.clearSelection();
                });

                clipboard.on('error', function(e) {
                    alert('复制失败,请收工选择复制');
                });
            })
            function copyToClipboard(maintext) {
                if (window.clipboardData){
                    window.clipboardData.setData("Text", maintext);
                }else if (window.netscape){
                    try{
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                    }catch(e){
                        alert("该浏览器不支持一键复制！n请手工复制文本框链接地址～");
                    }

                    var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
                    if (!clip) return;
                    var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
                    if (!trans) return;
                    trans.addDataFlavor('text/unicode');
                    var str = new Object();
                    var len = new Object();
                    var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
                    var copytext=maintext;
                    str.data=copytext;
                    trans.setTransferData("text/unicode",str,copytext.length*2);
                    var clipid=Components.interfaces.nsIClipboard;
                    if (!clip) return false;
                    clip.setData(trans,null,clipid.kGlobalClipboard);
                }
                $("#msg").text(maintext);
                $(".del").click(function(){
                    $(".alertMassage").hide(2);
                    $("#msg").text("");
                });
                $(".enter").click(function(){
                    $(".alertMassage").hide(2);
                })
            }
        </script>
        		<!-- basic scripts -->

		<!--[if !IE]> -->
		<script type="text/javascript">
			window.jQuery || document.write("<script src='/Public/Ln/js/jquery.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='/Public/Ln/js/jquery1x.js'>"+"<"+"/script>");
</script>
<![endif]-->
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='/Public/Ln/js/jquery.mobile.custom.js'>"+"<"+"/script>");
		</script>
		<script src="/Public/Ln/js/bootstrap.js"></script>

		<!-- page specific plugin scripts -->
		<script charset="utf-8" src="/Public/kindeditor/kindeditor-min.js"></script>
		<script charset="utf-8" src="/Public/kindeditor/lang/zh_CN.js"></script>
		<script src="/Public/Ln/js/bootbox.js"></script>
		<!-- ace scripts -->
		<script src="/Public/Ln/js/ace/elements.scroller.js"></script>
		<script src="/Public/Ln/js/ace/elements.colorpicker.js"></script>
		<script src="/Public/Ln/js/ace/elements.fileinput.js"></script>
		<script src="/Public/Ln/js/ace/elements.typeahead.js"></script>
		<script src="/Public/Ln/js/ace/elements.wysiwyg.js"></script>
		<script src="/Public/Ln/js/ace/elements.spinner.js"></script>
		<script src="/Public/Ln/js/ace/elements.treeview.js"></script>
		<script src="/Public/Ln/js/ace/elements.wizard.js"></script>
		<script src="/Public/Ln/js/ace/elements.aside.js"></script>
		<script src="/Public/Ln/js/ace/ace.js"></script>
		<script src="/Public/Ln/js/ace/ace.ajax-content.js"></script>
		<script src="/Public/Ln/js/ace/ace.touch-drag.js"></script>
		<script src="/Public/Ln/js/ace/ace.sidebar.js"></script>
		<script src="/Public/Ln/js/ace/ace.sidebar-scroll-1.js"></script>
		<script src="/Public/Ln/js/ace/ace.submenu-hover.js"></script>
		<script src="/Public/Ln/js/ace/ace.widget-box.js"></script>
		<script src="/Public/Ln/js/ace/ace.settings.js"></script>
		<script src="/Public/Ln/js/ace/ace.settings-rtl.js"></script>
		<script src="/Public/Ln/js/ace/ace.settings-skin.js"></script>
		<script src="/Public/Ln/js/ace/ace.widget-on-reload.js"></script>
		<script src="/Public/Ln/js/ace/ace.searchbox-autocomplete.js"></script>
		<script src="/Public/Ln/js/qrcode.js"></script>
</body>
</html>