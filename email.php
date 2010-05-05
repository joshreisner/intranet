<?php
include('include.php');

//error_handle('test');

array_send(array('subject'=>'testing', 'message'=>'really testing', 'url'=>$_josh['request']['url'], 'sanswww'=>$_josh['request']['sanswww']), 'http://errors.joshreisner.com/log.php');
?>