<?php
$pageIsPublic = true;
include("include.php");
$redirect = false;
if (url_action("logout")) {
	error_debug("<b>index.php</b> Logging Out");
	cookie("last_login");
	$_SESSION["user_id"] = false;
	$redirect = "/";
} elseif (login(@$_COOKIE["last_login"], "", true)) { //log in with last login
	error_debug("<b>index.php</b> Cookie Found (good)");
	$redirect = (empty($_GET["goto"])) ? $_SESSION["homepage"] : $_GET["goto"];
} elseif ($posting) { //logging in
	error_debug("<b>index.php</b> Posting");
	if (login($_POST["email"], $_POST["password"])) {
		error_debug("<b>index.php</b> Login successful");
		$redirect = (empty($_POST["goto"])) ? $_SESSION["homepage"] : $_POST["goto"];
   	} else {
		error_debug("<b>index.php</b> Login unsuccessful");
		$redirect = "/";
    }
}
if ($redirect) url_change($redirect);
?>
<html>
	<head>
		<title>Intranet</title>
		<style type="text/css">
			<!--
			body { background-color:#d3d3d3; margin:0px; padding:0px; width:100%; height:100%; font-family:verdana; font-size:11px; color:#444; line-height:19px; }
			#container { width:708px; height:334px; position:absolute; top:50%; left:50%; margin-left:-257px; margin-top:-167px; }
    		
			#white	{ background-color:#ffffff; width:355px; height:296px; padding:18px 20px 20px 20px;  position:relative; float:left; }
			#grey	{ background-color:#ededed; width:159px; height:314px; padding:10px;  position:relative;  margin-left:8px; float:left; }

			.top-left { top:0px; left:0px; position:absolute; }
			.top-right { top:0px; right:0px; float:right; position:absolute; }
			.bottom-left { bottom:0px; left:0px; position:absolute; }
			.bottom-right { bottom:0px; right:0px; float:right; position:absolute; }
			
			a { color:#6666cc; text-decoration:none; }
			a:hover { color:#9999ff; text-decoration:underline; }
			
			#login { position:absolute; top:138px; right:50px; display:block; width:230px; }
			dl, dt, dd { padding:0px; margin:0px; }
			dt, dd { height:30px; }
			dl dt { width:70px; padding-top:1px; text-align:right; white-space:nowrap; float:left; clear:left; position:relative; }
			dl dd { width:150px; float:right; }
			input { background-color:#eee; border:1px solid #ccc; font-family:verdana; font-size:11px; padding:2px; color:#444; }
			input.text { width:150px; }
			
			#grey ul { margin:10px 0px 0px 0px; padding:0px; list-style:circle; }
			#grey li { margin:2px 0px 0px 20px; padding:0px 0px 0px 5px; }
			
			//-->
		</style>
		<!--[if ie]>
		<style type="text/css">
			#white	{ width:395px; height:334px; }
			#grey	{ width:179px; height:334px; }
			#logos	{ height:334px; }
			.top-right { right:-1px; }
			.bottom-right { right:-1px; }
		</style>
		<![endif]--> 
		<script language="javascript">
			<!--
	    	function validate(form) {
	        	if (!form.email.value.length) {
	            	form.email.focus();
	                return false;
	            }
	            return true;
	        }
			//-->
		</script>
	</head>
	<body>
		<div id="container">
			<div id="white">
				<div class="top-left"><img src="<?=$locale?>images/corners-white/top-left.png" width="10" height"10" border="0"></div>
				<div class="top-right"><img src="<?=$locale?>images/corners-white/top-right.png" width="10" height"10" border="0"></div>
				<div class="bottom-left"><img src="<?=$locale?>images/corners-white/bottom-left.png" width="10" height"10" border="0"></div>
				<div class="bottom-right"><img src="<?=$locale?>images/corners-white/bottom-right.png" width="10" height"10" border="0"></div>
				<?=draw_img($locale . "images/logos.png");?>
				<div id="login">
					<form method="post" action="/" name="login">
					<input type="hidden" name="goto" value="<?=@$_GET["goto"]?>">
					<dl>
					<dt>Email</dt>
					<dd><input type="text" name="email" class="text" value="<?=@$_COOKIE["last_email"]?>"></dd>
					<dt>Password</dt>
					<dd><input type="password" name="password" class="text"></dd>
					<dt>&nbsp;</dt>
					<dd><input type="submit" value="go" class="submit"></dd>
					</dl>
					</form>
				</div>
			</div>
			<div id="grey">
				<div class="top-left"><img src="<?=$locale?>images/corners-grey/top-left.png" width="10" height"10" border="0"></div>
				<div class="top-right"><img src="<?=$locale?>images/corners-grey/top-right.png" width="10" height"10" border="0"></div>
				<div class="bottom-left"><img src="<?=$locale?>images/corners-grey/bottom-left.png" width="10" height"10" border="0"></div>
				<div class="bottom-right"><img src="<?=$locale?>images/corners-grey/bottom-right.png" width="10" height"10" border="0"></div>
				<?=getString("login")?>
				<ul>
				<li><a href="/login/password_reset.php">I Forgot My Password</a></li>
				<li><a href="/login/account_request.php">I Need a New Account</a></li>
				<li><a href="mailto:josh@joshreisner.com">I Would Like To Ask a Question</a></li>
				</ul>
			</div>
		</div>
	</body>
	<script language="javascript">
		document.login.<? if(@$_COOKIE["last_email"]) {?>password<? }else{?>email<? }?>.focus();
	</script>
</html>