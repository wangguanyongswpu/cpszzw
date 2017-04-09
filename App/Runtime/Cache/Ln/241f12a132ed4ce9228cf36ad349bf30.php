<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title><?php echo ($current['title']); ?>-<?php echo (C("title")); ?></title>

		<meta name="keywords" content="<?php echo (C("keywords")); ?>" />
		<meta name="description" content="<?php echo (C("description")); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

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
		
		<style>
			.form-horizontal .control-label{
				padding-top:10px !important;
			}
			textarea, input[type="text"]{
				border:1px solid #36648B;
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
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
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
			<div class="main-content">
				<div class="main-content-inner">
					<!-- #section:basics/content.breadcrumbs -->
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
					</div>

					<!-- /section:basics/content.breadcrumbs -->
					<div class="page-content">
						<!--&lt;!&ndash; #section:settings.box &ndash;&gt;-->
						<!--<?php if($current["tips"] != ''): ?>-->
						<!--<div class="alert alert-block alert-success">-->
							<!--<button type="button" class="close" data-dismiss="alert">-->
								<!--<i class="ace-icon fa fa-times"></i>-->
							<!--</button>-->
							<!--&lt;!&ndash;i class="ace-icon fa fa-check green"></i&ndash;&gt;-->
							<!--<?php echo ($current["tips"]); ?>-->
						<!--</div>-->
						<!--<?php endif; ?>-->
						<!--<div class="ace-settings-container" id="ace-settings-container">-->
							<!--<div class="btn btn-app btn-xs btn-warning ace-settings-btn" id="ace-settings-btn">-->
								<!--<i class="ace-icon fa fa-cog bigger-130"></i>-->
							<!--</div>-->

							<!--<div class="ace-settings-box clearfix" id="ace-settings-box">-->
								<!--<div class="pull-left width-50">-->
									<!--&lt;!&ndash; #section:settings.skins &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<div class="pull-left">-->
											<!--<select id="skin-colorpicker" class="hide">-->
												<!--<option data-skin="no-skin" value="#438EB9">#438EB9</option>-->
												<!--<option data-skin="skin-1" value="#222A2D">#222A2D</option>-->
												<!--<option data-skin="skin-2" value="#C6487E">#C6487E</option>-->
												<!--<option data-skin="skin-3" value="#D0D0D0">#D0D0D0</option>-->
											<!--</select>-->
										<!--</div>-->
										<!--<span>&nbsp; 选择皮肤</span>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.skins &ndash;&gt;-->

									<!--&lt;!&ndash; #section:settings.navbar &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-navbar" />-->
										<!--<label class="lbl" for="ace-settings-navbar"> 固定导航条</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.navbar &ndash;&gt;-->

									<!--&lt;!&ndash; #section:settings.sidebar &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-sidebar" />-->
										<!--<label class="lbl" for="ace-settings-sidebar"> 固定侧边栏</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.sidebar &ndash;&gt;-->

									<!--&lt;!&ndash; #section:settings.breadcrumbs &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-breadcrumbs" />-->
										<!--<label class="lbl" for="ace-settings-breadcrumbs"> 固定面包屑</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.breadcrumbs &ndash;&gt;-->

									<!--&lt;!&ndash; #section:settings.rtl &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-rtl" />-->
										<!--<label class="lbl" for="ace-settings-rtl"> 切换至左边</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.rtl &ndash;&gt;-->

									<!--&lt;!&ndash; #section:settings.container &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-add-container" />-->
										<!--<label class="lbl" for="ace-settings-add-container">-->
											<!--切换宽窄度-->
										<!--</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:settings.container &ndash;&gt;-->
								<!--</div>&lt;!&ndash; /.pull-left &ndash;&gt;-->

								<!--<div class="pull-left width-50">-->
									<!--&lt;!&ndash; #section:basics/sidebar.options &ndash;&gt;-->
									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-hover" />-->
										<!--<label class="lbl" for="ace-settings-hover"> 子菜单收起</label>-->
									<!--</div>-->

									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-compact" />-->
										<!--<label class="lbl" for="ace-settings-compact"> 侧边栏紧凑</label>-->
									<!--</div>-->

									<!--<div class="ace-settings-item">-->
										<!--<input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-highlight" />-->
										<!--<label class="lbl" for="ace-settings-highlight"> 当前位置</label>-->
									<!--</div>-->

									<!--&lt;!&ndash; /section:basics/sidebar.options &ndash;&gt;-->
								<!--</div>&lt;!&ndash; /.pull-left &ndash;&gt;-->
							<!--</div>&lt;!&ndash; /.ace-settings-box &ndash;&gt;-->
						<!--</div>&lt;!&ndash; /.ace-settings-container &ndash;&gt;-->

						<!-- /section:settings.box -->
						<div class="row" style="margin-left:12px;margin-top:15px;">
							<div class="col-xs-12">
								<!-- PAGE CONTENT BEGINS -->
									<form class="form-horizontal" role="form" action="<?php echo U('update');?>" method="post">
									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-1"> 用户名：</label>
										<div class="col-sm-9">
											<input type="text" disabled="disabled" class="rcol-xs-10 col-sm-5" value="<?php echo ($member["user"]); ?>">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle" style="padding-top: 10px;display:block;">&nbsp;&nbsp;无法修改</span>
											</span>
										</div>
									</div>

									<div class="space-4"></div>

									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-2"> 用户密码：</label>
										<div class="col-sm-9">
											<input type="password" name="password" id="password" placeholder="用户密码" class="col-xs-10 col-sm-5" value="">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle" style="padding-top: 10px;display:block;">&nbsp;&nbsp;留空不修改</span>
											</span>
										</div>
									</div>
<!--
									<div class="space-4"></div>
									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-4"> 缩略图 </label>
										<div class="col-sm-9">
											<div class="col-xs-10 col-sm-5"><?php echo UpImage("head",100,100,$member['head']);?></div>
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle">仅支持jpg、gif、png、bmp、jpeg，且小于1MB。</span>
											</span>
										</div>
										
									</div>
									<div class="space-4"></div>

									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-4"> 性别 </label>
										<div class="col-sm-9">
											<select id="sex" name="sex" class="multiselect">
												<option value="0" <?php if($member['sex'] == 0): ?>selected="selected"<?php endif; ?>>保密</option>
												<option value="1" <?php if($member['sex'] == 1): ?>selected="selected"<?php endif; ?>>男</option>
												<option value="2" <?php if($member['sex'] == 2): ?>selected="selected"<?php endif; ?>>女</option>
											</select>
										</div>
									</div>
-->
									<div class="space-4"></div>

									<!--<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="id-date-picker-1">生日</label>
										<div class="col-xs-9 col-sm-9">
											<div class="input-group col-xs-5">
												<input class="form-control" id="birthday" name="birthday" value="<?php echo (date("Y-m-d",$member['birthday'])); ?>" type="text" data-date-format="dd-mm-yyyy" />
												<span class="input-group-addon">
													<i class="ace-icon fa fa-calendar bigger-110"></i>
												</span>
											</div>
										</div>
									</div>-->

									
									<div class="space-4"></div>

									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-6"> 电话号码：</label>
										<div class="col-sm-9">
											<input type="text" name="phone" id="phone" placeholder="电话号码" class="col-xs-10 col-sm-5" value="<?php echo ($member['phone']); ?>">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle"></span>
											</span>
										</div>
									</div>

									<div class="space-4"></div>

									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-7"> Q&nbsp;&nbsp;Q：</label>
										<div class="col-sm-9">
											<input type="text" name="qq" id="qq" placeholder="QQ" class="col-xs-10 col-sm-5" value="<?php echo ($member['qq']); ?>">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle"></span>
											</span>
										</div>
									</div>

									<div class="space-4"></div>

									<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-8"> E-mail：</label>
										<div class="col-sm-9">
											<input type="email" name="email" id="email" placeholder="E-mail" class="col-xs-10 col-sm-5" value="<?php echo ($member['email']); ?>">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle"></span>
											</span>
										</div>
									</div>
									<div class="space-4"></div>
									<!--<div class="form-group">
										<label class="col-sm-1 control-label no-padding-right" for="form-field-8"> 百度统计ID </label>
										<div class="col-sm-9">
											<input type="text" name="baidu_statistics_id" id="baidu_statistics_id" placeholder="百度统计ID" class="col-xs-10 col-sm-5" value="<?php echo ($member['baidu_statistics_id']); ?>">
											<span class="help-inline col-xs-12 col-sm-7">
											<span class="middle"></span>
										</span>
										</div>
									</div>

									<div class="space-4"></div>-->
									
									<div class="col-md-offset-2 col-md-9">
										<button class="btn btn-info" type="submit">
											<i class="icon-ok bigger-110"></i>
											提交信息
										</button>

										&nbsp; &nbsp; &nbsp;
										<button class="btn" type="reset">
											<i class="icon-undo bigger-110"></i>
											重置信息
										</button>
									</div>
								</form>
								<!-- PAGE CONTENT ENDS -->
							</div><!-- /.col -->
						</div><!-- /.row -->
					</div><!-- /.page-content -->
				</div>
			</div><!-- /.main-content -->
			
			
		</div><!-- /.main-container -->

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
		<!-- inline scripts related to this page -->
		<script src="/Public/Ln/js/date-time/bootstrap-datepicker.js"></script>
		<script type="text/javascript">
		jQuery(function($){
			$('#birthday').datepicker({
				  format: 'yyyy-mm-dd',
						weekStart: 1,
						autoclose: true,
						todayBtn: 'linked',
						language: 'cn'
				 });
			});
		
		</script>
	</body>
</html>