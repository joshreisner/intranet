<?php
$pageIsPublic = true;
include("include.php");
$redirect = false;
if (url_action("logout")) {
	error_debug("<b>index.php</b> Logging Out", __file__, __line__);
	cookie("last_login");
	$_SESSION["user_id"] = false;
	$redirect = "/";
} elseif (login(@$_COOKIE["last_login"], "", true)) { //log in with last login
	error_debug("<b>index.php</b> Cookie Found (good)", __file__, __line__);
	$redirect = (empty($_GET["goto"])) ? $_SESSION["homepage"] : $_GET["goto"];
} elseif ($posting) { //logging in
	error_debug("<b>index.php</b> Posting", __file__, __line__);
	if (login($_POST["email"], $_POST["password"])) {
		error_debug("<b>index.php</b> Login successful", __file__, __line__);
		$redirect = (empty($_POST["goto"])) ? $_SESSION["homepage"] : $_POST["goto"];
   	} else {
		error_debug("<b>index.php</b> Login unsuccessful", __file__, __line__);
		$redirect = "/";
    }
}
if ($redirect) url_change($redirect);
url_header_utf8();
?>
<html>
	<head>
		<?=draw_meta_utf8()?>
		<title><?=getString("app_name")?></title>
		<style type="text/css">
			<!--
			body { background-color:#d3d3d3; margin:0px; padding:0px; width:100%; height:100%; font-family:verdana; font-size:11px; color:#444; line-height:19px; }
			#container { width:708px; height:334px; position:absolute; top:50%; left:50%; margin-left:-257px; margin-top:-167px; }
    		
			#white	{ background-color:#ffffff; width:355px; height:296px; padding:18px 20px 20px 20px;  position:relative; float:left; }
			#grey	{ background-color:#ededed; width:159px; height:314px; padding:10px;  position:relative;  margin-left:8px; float:left; }

			#language { clear:both; width:514px; text-align:center; padding-top:20px; }
			input, a { outline:none; }
			
			.top-left { top:0px; left:0px; position:absolute; }
			.top-right { top:0px; right:0px; float:right; position:absolute; }
			.bottom-left { bottom:0px; left:0px; position:absolute; }
			.bottom-right { bottom:0px; right:0px; float:right; position:absolute; }
			
			a { color:#6666cc; text-decoration:none; }
			a:hover { color:#9999ff; text-decoration:underline; }
			
			form.login { position:absolute; top:138px; left:0px; display:block; width:230px; }
			form.login fieldset { border:none; }
			form.login fieldset legend { display:none; }
			form.login div.field { position:relative; height:30px; }
			form.login div.field label { width:170px; display:block; position:absolute; left:0px; text-align:right; overflow-x:visible; white-space:nowrap; }
			form.login input { position:absolute; left:180px; }
			input { background-color:#eee; border:1px solid #ccc; font-family:verdana; font-size:11px; padding:2px; color:#444; margin:0px; }
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
		<?=draw_javascript_lib();?>
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
				<div class="top-left"><img src="/images/corners-white/top-left.png" width="10" height"10" border="0"></div>
				<div class="top-right"><img src="/images/corners-white/top-right.png" width="10" height"10" border="0"></div>
				<div class="bottom-left"><img src="/images/corners-white/bottom-left.png" width="10" height"10" border="0"></div>
				<div class="bottom-right"><img src="/images/corners-white/bottom-right.png" width="10" height"10" border="0"></div>
				<?php
				echo draw_img($_josh["write_folder"] . "/login.png");
				
				$f = new form('login', false, getString('submit'));
				$f->set_field(array('name'=>'email', 'type'=>'text', 'label'=>getString('email'), 'value'=>@$_COOKIE["last_email"]));
				$f->set_field(array('name'=>'password', 'type'=>'password', 'label'=>getString('password')));
				$f->set_field(array('name'=>'goto', 'type'=>'hidden', 'value'=>@$_GET["goto"]));
				$f->set_focus((@$_COOKIE["last_email"] ? 'password' : 'email'));
				echo $f->draw();
				?>
			</div>
			<div id="grey">
				<div class="top-left"><img src="/images/corners-grey/top-left.png" width="10" height"10" border="0"></div>
				<div class="top-right"><img src="/images/corners-grey/top-right.png" width="10" height"10" border="0"></div>
				<div class="bottom-left"><img src="/images/corners-grey/bottom-left.png" width="10" height"10" border="0"></div>
				<div class="bottom-right"><img src="/images/corners-grey/bottom-right.png" width="10" height"10" border="0"></div>
				<?=getString("app_welcome")?>
				<ul>
				<li><a href="/login/password_reset.php"><?=getString("login_forgot_password")?></a></li>
				<li><a href="/login/account_request.php"><?=getString("login_need_account")?></a></li>
				<li><a href="mailto:josh@joshreisner.com"><?=getString("login_ask_question")?></a></li>
				</ul>
			</div>
			<? if (getOption('languages')) {
				echo draw_div('language', draw_form_select('language_id', 'SELECT id, title FROM languages ORDER BY title', $_SESSION['language_id'], true, 'grey', 'url_query_set(\'language_id\', this.value)'));
			}?>
		</div>
	</body>
</html>