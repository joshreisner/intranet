<?
//this one is not public
include('../include.php');

if ($posting) {
	db_query('UPDATE users SET password = PWDENCRYPT("' . $_POST['password1'] . '") WHERE id = ' . $_SESSION['user_id']);
	$_SESSION['password'] = false;
	url_change($_SESSION['homepage']);
}

echo drawTopSimple(getString('password_update'));

$f = new form('password_update', false, getString('submit'));
$f->set_field(array('type'=>'password', 'name'=>'password1', 'label'=>getString('password')));
$f->set_field(array('type'=>'password', 'name'=>'password2', 'label'=>getString('confirm')));
echo $f->draw();

echo drawBottomSimple();
?>