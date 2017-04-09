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

                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="row">
                            <form class="form-inline" action="<?php echo U('index');?>" method="get" id="search-form">
                                <a class="btn-line btn btn-info " href="<?php echo U('add');?>" value="">新增</a>
                                <label class="btn-line inline">用户搜索</label>
                                <input type="text" name="keyword" class="btn-line form-control">
                                <!--<label class="btn-line inline">域名分组</label>
                                <select name="packet_id">
                                    <option value="0">选择域名分组</option>
                                    <?php if(is_array($packet_list)): $i = 0; $__LIST__ = $packet_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option <?php if(I('get.packet_id')==$v['id']): ?>selected<?php endif; ?> value="<?php echo ($v['id']); ?>"><?php echo ($v["alias"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    <option <?php if(I('get.packet_id')=='none'): ?>selected<?php endif; ?> value="none">未分组</option>
                                </select>-->
                                <input type="text" name="suid" value="<?= I('get.suid','')?>" class="btn-line form-control" placeholder="推广商ID搜索">
                                <button type="submit" class="marL-0 marT-15 btn-line btn btn-purple btn-sm">
                                    <span class="ace-icon fa fa-search icon-on-right bigger-110"></span>
                                    搜索
                                </button>
                                <?php if($u['gid'] < 3): ?><!--<a class="marT-15 btn-line btn btn-info" href="<?php echo U('do_ex',array('uid'=>$u['uid']));?>" value="" style="margin-left: 5%;">Excel导出</a>--><?php endif; ?>
                                <?php if(in_array($user['uid'],C('FINANCE_IDS'))): ?><!--<a class="btn btn-info" href="<?php echo U('do_ex',array('ref1_ids'=>C('EXCEL_REF1_ID')));?>" style="margin-left: 5%;">指定一级Excel导出</a>--><?php endif; ?>
                            </form>

                            <?php if(in_array($user['uid'],C('FINANCE_IDS'))): ?><form class="form-inline" action="<?php echo U('do_ex',array('ref1_ids'=>C('EXCEL_REF1_ID')));?>" method="get" id="search-form2">
                                <label class="inline">&nbsp;&nbsp;开始日期：</label>
                                <div class="input-group ">
                                    <input class="form-control" id="startime" name="startime" value="<?php echo ($date["start"]); ?>" type="text" data-date-format="dd-mm-yyyy"/>
                                    <span class="input-group-addon btn-mar">
                                        <i class="ace-icon fa fa-calendar bigger-110"></i>
                                    </span>
                                </div>
                                <label class="inline clear-both margin-top1" >&nbsp;&nbsp;结束日期:</label>
                                <div class="input-group marginT-15">
                                    <input class="form-control" id="entime" name="endtime" value="<?php echo ($date["end"]); ?>" type="text"  data-date-format="dd-mm-yyyy"/>
                                    <span class="input-group-addon btn-mar">
                                        <i class="ace-icon fa fa-calendar bigger-110"></i>
                                    </span>
                                </div>
                                <button type="submit" class="btn btn-purple btn-sm clear-both marginT-15">
                                    <span class="ace-icon fa fa-search icon-on-right bigger-110"></span>
                                    指定一级Excel导出
                                </button>
                            </form>
                                <form class="form-inline" action="<?php echo U('do_ex',array('uid'=>$u['uid']));?>" method="get" id="search-form3">
                                    <button type="submit" class="btn btn-purple btn-sm clear-both marginT-15">
                                        <span class="ace-icon fa fa-search icon-on-right bigger-110"></span>
                                        导出
                                    </button>
                                </form><?php endif; ?>

                        </div>

                        <div class="space-4"></div>
                        <div class="row">
                            <form id="form" method="post" action="<?php echo U('del');?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="center one"> ID
                                            <!--<input class="check-all" type="checkbox" value="">-->
                                        </th>
                                        <th >用户名</th>
                                        <!--<th class="two">备注</th>-->
                                        <th class="there">用户组</th>
                                        <th class="fou">电话</th>
                                        <th class="fif">微信账号</th>
                                        <th class="six">支付宝账号</th>
                                        <!--<th class="sev">银行名称</th>
                                        <th class="eight">银行账号</th>-->
                                        <?php if(in_array($user['uid'],C('FINANCE_IDS'))): ?><th class="nine">分成比例(%)</th><?php endif; ?>
                                        <!--<th class="ten">上级经销商</th>-->
                                        <!--<?php if($u['gid'] == 1): ?><th class="ele">所在域名组</th><?php endif; ?>-->
                                        <!--<th class="thth">推广方式</th>-->
                                        <th>注册时间</th>
                                      	<th>用户锁</th>
                                        <th class="center">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <style>
                                        .grouptd{position:relative;}
                                        .group{display:inline-block;width:100%;}
                                        .groupselect{position:absolute;top:0;left:0;width:100%;height:100%;border:0;}
                                    </style>
                                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i; if($val['has_child']): ?><tr class="has_child c_hide" data-id="<?php echo ($val['uid']); ?>">
                                        <?php else: ?>
                                            <tr><?php endif; ?>
                                        <td class="one"><i></i><?php echo ($val['uid']); ?>
                                            <input class="uids" type="hidden" name="uids[]" value="<?php echo ($val['uid']); ?>">
                                        </td>
                                            <!--<td class="center">
                                                <?php if($val['uid'] != 1): ?><input class="uids" type="checkbox" name="uids[]" value="<?php echo ($val['uid']); ?>"><?php else: ?><span title="系统管理员，禁止删除">&#45;&#45;</span><?php endif; ?>
                                            </td>-->
                                        <?php if($val['gid'] == 3): ?><td class="two">&nbsp;&nbsp;---&nbsp;<?php echo ($val['user']); ?></td>
                                            <?php else: ?>
                                            <td class="there"><?php echo ($val['user']); ?></td><?php endif; ?>
                                        <!--<td class="fou"><?php echo ($val["remark"]); ?></td>-->
                                        <td class="grouptd fif">
                                            <span class="group" val="<?php echo ($val['uid']); ?>"><?php echo ($val['title']); ?></span>
                                        </td>
                                        <td class="six"><?php echo ($val['phone']); ?></td>
                                        <td class="se"><?php echo ($val['wei_number']); ?></td>
                                        <td class="eight"><?php echo ($val['alipay_number']); ?></td>
                                        <!--<td class="nine"><?php echo ($val['bank_name']); ?></td>
                                        <td class="ten"><?php echo ($val['bank_number']); ?></td>-->
                                        <?php if(in_array($user['uid'],C('FINANCE_IDS'))): ?><td class="ele"><?php echo ($val['scale']); ?></td><?php endif; ?>
										<!--<td><?php echo ($val['puser']); ?></td>
                                        <?php if($u['gid'] == 1): ?><td class="twl">
                                                <?php if(is_array($group)): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i; if(($val['gid'] == $v['id']) and $v['id'] == 2): echo ($val['packet']); endif; endforeach; endif; else: echo "" ;endif; ?>
                                        </td><?php endif; ?>
                                        <td class="thth"><?php echo ($cpstype[$val['cps_type']]); ?></td>-->
                                        <td class="ten"><?php echo ($val['date']); ?></td>
                                      	<td class="ten"><?php if($val['login_lock'] != 1): ?><span style="color:red">锁定</span><?php endif; if($val['login_lock'] == 1): ?>正常<?php endif; ?></td>
										<td class="center"><a href="<?php echo U('edit');?>&uid=<?php echo ($val['uid']); ?>">修改</a>&nbsp;
											<?php if($u['gid'] == 1): ?><span style="cursor: pointer;color: #337ab7;" onclick="up_url(this,'a_<?php echo ($val['uid']); ?>')" url_val="<?php echo U('do_ex');?>&uid=<?php echo ($val['uid']); ?>">导出下级</span>
												<a id="a_<?php echo ($val['uid']); ?>" href="" ><span></span></a>&nbsp;<?php endif; ?>
											<?php if($val['uid'] != 1): ?><!--<a class="del" href="javascript:;" val="<?php echo U('del');?>&uids=<?php echo ($val['uid']); ?>" title="删除">删除</a>--><?php endif; ?>
										</td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php echo ($page); ?>
                        </div>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
    
</div><!-- /.main-container -->
<?php if($u['gid'] == 2): ?><!--modal-->
    <div class="modal fade" id="wechat_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">选择跳转域名</h4>
                    <input type="hidden" value="" id="select_member">
                </div>
                <div class="modal-body row" id="wechats">
                    <?php if(is_array($wechats)): $i = 0; $__LIST__ = $wechats;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><label class="col-xs-4" style="width:180px;">
                            <input type="checkbox" class="ace ace-checkbox-2 children" name="wechat_ids" value="<?php echo ($val["id"]); ?>">
                            <span class="lbl"><?php echo ($val["url"]); ?></span>
                        </label><?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <a href="javascript:;" class="btn btn-primary" onclick="saveWechatSelect()">确定</a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div><?php endif; ?>
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
	/**
	* 导出下级时，url地址进行修改
	**/
	function up_url(obj,a_id){
		var url_val=$(obj).attr('url_val');
		if($("input[name='startime']").val()+''!='undefined'){
			url_val+='&startime=' + $("input[name='startime']").val();
		}
		if($("input[name='endtime']").val()+''!='undefined'){
			url_val+='&endtime=' + $("input[name='endtime']").val();
		}
		$('#'+a_id).attr('href',url_val).find('span').click();
	}
    function change_p_user(obj){
        var uid = $(obj).closest('tr').find(".uids").val();
        if(typeof (uid) != 'undefined'){
            var pId = $(obj).find("option:selected").val();
           $.get("<?php echo U('update');?>&ajax=change_puser&uid="+uid+"&pId="+pId,function(data){
                if($.trim(data) == 1){
                    alert('修改成功！');
                    window.location.reload();
                }
            })
        }
    }
    $(function(){
        $(".packet").on('change', function(){
            var uid = $(this).closest('tr').find(".uids").val();
            if(typeof (uid) != 'undefined'){
                var packet_id = $(this).find("option:selected").val();
                $.get("<?php echo U('update');?>&ajax=packet&uid="+uid+"&packet_id="+packet_id,function(data){
                    if($.trim(data) == 1){
                        alert('修改成功！');
                    }
                })
            }
        });

        $(".group").click(function(){

            $(this).addClass('hide');

            $(this).parent().find(".groupselect").removeClass('hide');

        });

        $(".has_child").find('i').click(function(){
            var t = $(this).closest("tr");
            var id = t.attr('data-id');
            var child = $(".child_" + id);
            var startime = $("#startime").val();
            var endtime = $("#entime").val();
            if(t.hasClass('c_hide')) {
                if (child.length > 0) {
                    child.show()
                    t.removeClass('c_hide').addClass('c_open');
                } else{
                    $.get("<?php echo U('Ln/Member/Index');?>&ajax=1&uid="+id+"&gid="+<?php echo ($gid); ?>,
                            function(data){
                                var ret = JSON.parse(data);
                                t.after(ret.html);
                                $("." + id + "_child").show();
                                t.removeClass('c_hide').addClass('c_open');
                            });
                }
            } else{
                if (child.length > 0) {
                    child.hide();
                    t.removeClass('c_open').addClass('c_hide');
                }
            }
        });

        $(".groupselect").on("change",function(){

            var ob = $(this);

            var gid=ob.val();

            var uid = ob.parent().find('.group').attr('val');

            $.get("<?php echo U('update');?>&ajax=yes&uid="+uid+"&gid="+gid,function(data){

                var text = ob.find("option:selected").text();

                ob.parent().find(".group").removeClass('hide').html(text);

                ob.addClass('hide');
                if(gid != 2){
                    ob.closest('tr').find(".packet").addClass('hide');
                }else{
                    ob.closest('tr').find(".packet").removeClass('hide');
                }

            });

        });

        $(".check-all").click(function(){

            $(".uids").prop("checked", this.checked);

        });

        $(".uids").click(function(){

            var option = $(".ids");

            option.each(function(i){

                if(!this.checked){

                    $(".check-all").prop("checked", false);

                    return false;

                }else{

                    $(".check-all").prop("checked", true);

                }

            });

        });

        $("#submit").click(function(){

            bootbox.confirm({

                title: "系统提示",

                message: "是否要删除所选用户？",

                callback:function(result){

                    if(result){

                        $("#form").submit();

                    }

                },

                buttons: {

                    "cancel" : {"label" : "取消"},

                    "confirm" : {

                        "label" : "确定",

                        "className" : "btn-danger"

                    }

                }

            });

        });

        $(".del").click(function(){

            var url = $(this).attr('val');

            bootbox.confirm({

                title: "系统提示",

                message: "是否要该用户?",

                callback:function(result){

                    if(result){

                        window.location.href = url;

                    }

                },

                buttons: {

                    "cancel" : {"label" : "取消"},

                    "confirm" : {

                        "label" : "确定",

                        "className" : "btn-danger"

                    }

                }

            });

        });

        /*查询*/
        $("#search-form").on('submit',function(e){
            e.preventDefault();
            var getURl = $("#search-form").attr("action")
					+'&packet_id='+$("select[name='packet_id']").val()
                    +'&keyword='+ $("input[name='keyword']").val()
                    +'&order='+ $("#order").find("option:selected").val();
            var suid = $("input[name='suid']").val();
            if(suid){
                getURl += '&suid='+suid;
            }
            window.location.href=getURl;
        });

        $('#startime').datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1,
            autoclose: true,
            todayBtn: 'linked',
            language: 'cn'
        });

        $('#entime').datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1,
            autoclose: true,
            todayBtn: 'linked',
            language: 'cn'
        });

        $("#search-form2").on('submit', function (e) {

            e.preventDefault();

            var getURl = $("#search-form2").attr("action") + '&startime=' + $("input[name='startime']").val() + '&endtime=' + $("input[name='endtime']").val() ;

            $("#search-form2").attr("action", getURl);

            window.location.href = getURl;

        });

        $("#search-form3").on('submit', function (e) {

            e.preventDefault();

            var getURl = $("#search-form3").attr("action") + '&startime=' + $("input[name='startime']").val() + '&endtime=' + $("input[name='endtime']").val() ;

            $("#search-form3").attr("action", getURl);

            window.location.href = getURl;

        });

    });
    <?php if($u['gid'] == 2): ?>function showWechatSelect(id)
        {
            $('#select_member').val(id);
            var w_ids = $("#member_"+id).val().split(',');
            $('input[name="wechat_ids"]').each(function(index, value){
                if($.inArray($(this).val(),w_ids) != -1){
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
            $("#wechat_modal").modal();
        }

        function saveWechatSelect()
        {
            var str = '';
            $('input[name="wechat_ids"]').each(function(){
                if($(this).prop('checked')) {
                    str += ',' + $(this).val();
                }
            });

            if(str != '') {
                str = str.substr(1);
                var id = $('#select_member').val();

                $.ajax({
                    type: 'GET',
                    url: "<?php echo U('Member/saveWechatSelect');?>",
                    data: {'id': id, 'ids': str, 'ajax': 1},
                    dataType: 'text',
                    success: function (data) {
                        $("#member_"+id).val(str);
                    }
                });
            }

            $("#wechat_modal").modal('hide');
        }<?php endif; ?>
</script>
</body>
</html>