<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<html lang="zh-CN">

	<head>

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

		<meta charset="utf-8" />

		<title><?php echo (C("title")); ?></title>

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



	<body class="login-layout" style="background-position: center;">

		<div class="main-container login-main-container">

			<div class="main-content">

				<div class="row">

					<div class="col-sm-10 col-sm-offset-1">

						<div class="login-container">

							<div class="space-6"></div> 



							<div class="position-relative">

								<div id="login-box" class="login-box visible widget-box no-border" style="box-shadow: 0px 0px 10px #fff;border-radius:0;-webkit-border-radius:0;padding:0;border: 0;">

									<div class="widget-body">
										<h4 class="header blue lighter bigger" style="background-color: #36648B;line-height: 42px; margin: 0;font-size: 22px;">
											<b style="color: #fff;margin-left: 10px;">登录界面</b>
										</h4>
										<div class="widget-main">
											<div class="space-6"></div>
											<form action="<?php echo U('login/login');?>" method="post">

												<fieldset>

													<label class="block clearfix">

														<span class="block input-icon input-icon-right">

															<input type="text" class="form-control" name="user" placeholder="用户名" style="padding-left: 45px;padding-right: 5px;"/>

															<i class="ace-icon fa fa-user" style="left: 1px;width: 40px;background-color: #36648B;color: #fff;font-size: 24px;"></i>

														</span>

													</label>
													<div class="space"></div><div class="space"></div>


													<label class="block clearfix">

														<span class="block input-icon input-icon-right">

															<input type="password" class="form-control" name="password" placeholder="密码"  style="padding-left: 45px;padding-right: 5px;"/>

															<i class="ace-icon fa fa-lock" style="left: 1px;width: 40px;background-color: #36648B;color: #fff;font-size: 24px;"></i>

														</span>

													</label>



													<div class="space"></div>
													<div class="space"></div>
													<!--<label class="block clearfix">

														<span class="block input-icon ">

															<span class="inline"><input type="text" class="form-control" name="verify" placeholder="验证码" id="code" required /></span>

															<img style="cursor:pointer;" src="<?php echo U('login/verify');?>" width="100" height="30" title="看不清楚？点击刷新" onclick="this.src = '<?php echo U('login/verify');?>&'+new Date().getTime()">

														</span>

													</label>

													

													<div class="space"></div>-->

													

													<div class="clearfix">

														<button type="submit" class="width-35 pull-right btn btn-sm btn-primary" style="width: 100% !important;background-color: #36648B !important;border-color: #366488;width: 100%;background-color: #36648B;">

															<i class="ace-icon fa fa-key"></i>

															<span class="bigger-110">登录</span>

														</button>

													</div>



													<div class="space-4"></div>

												</fieldset>

											</form>

										</div><!-- /.widget-main -->

									</div><!-- /.widget-body -->

								</div><!-- /.login-box -->

							</div><!-- /.position-relative -->



							<div class="navbar-fixed-top align-right">


							</div>

						</div>

					</div><!-- /.col -->

				</div><!-- /.row -->

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



		<!-- inline scripts related to this page -->

		<script type="text/javascript">

			jQuery(function($) {

			 $(document).on('click', '.toolbar a[data-target]', function(e) {

				e.preventDefault();

				var target = $(this).data('target');

				$('.widget-box.visible').removeClass('visible');//hide others

				$(target).addClass('visible');//show target

			 });

			});

			

			

			

			//you don't need this, just used for changing background

			jQuery(function($) {

			 $('#btn-login-dark').on('click', function(e) {

				$('body').attr('class', 'login-layout');

				$('#id-text2').attr('class', 'white');

				$('#id-company-text').attr('class', 'blue');

				

				e.preventDefault();

			 });

			 $('#btn-login-light').on('click', function(e) {

				$('body').attr('class', 'login-layout light-login');

				$('#id-text2').attr('class', 'grey');

				$('#id-company-text').attr('class', 'blue');

				

				e.preventDefault();

			 });

			 $('#btn-login-blur').on('click', function(e) {

				$('body').attr('class', 'login-layout blur-login');

				$('#id-text2').attr('class', 'white');

				$('#id-company-text').attr('class', 'light-blue');

				

				e.preventDefault();

			 });

			 

			});

		</script>

	</body>

</html>