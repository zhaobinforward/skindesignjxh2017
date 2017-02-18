<?php
/**
 */
require 'config.php';

$preg_refer = get_siteurl();
$_GET['refer'] = empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'];
if(!preg_match("@^{$preg_refer}@",$_GET['refer'])){
	$_GET['refer'] = '';
}
$_jumpurl = $_GET['refer'] ? $_GET['refer'] : './';

clear_cookies();
showmessage('logout_success', 1, $_jumpurl, 2);
?>