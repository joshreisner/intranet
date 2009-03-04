<?
$pageIsPublic = true;
include("../include.php");?>
<html>
	<head>
		<title>Request an Account</title>
		<link rel="stylesheet" type="text/css" href="<?=$locale?>style.css" />
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
echo drawServerMessage("<h1>Account Already Exists</h1>  The email you entered already belongs to an active account on the system.  Would you
like to <a href='password_reset.php'>reset your password</a>?");
?>
				</td>
			</tr>
		</table>
	</body>
</html>