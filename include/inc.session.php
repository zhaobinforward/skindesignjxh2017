<?php
/**
 *	处理会登录状态
 */

$passportid = !empty($_SERVER["HTTP_X_SOHUPASSPORT_USERID"]) ? $_SERVER["HTTP_X_SOHUPASSPORT_USERID"]: '';

if($_SESSION['uid'] && $passportid) {
	$_G['uid'] = $_SESSION['uid'];
	$_G['nickname'] = $_SESSION['nickname'];
	if(empty($_COOKIE['uid'])) {
		@ssetcookie('uid', $_G['uid']);
		@ssetcookie('nickname', $_G['nickname']);
	}
} else {
	if($passportid) {
		$user = get_user($passportid, 'passportid');//get local user info by passport
	} else {
		$user = array();
	}
	if($user) {
		$_SESSION['uid'] = intval($user['uid']);
		$_SESSION['nickname'] = $user['nickname'] === '' ? '搜狐网友' . sprintf("%09d", $user['uid']) : $user['nickname'];
		$_G['uid'] = $_SESSION['uid'];
		$_G['nickname'] = $_SESSION['nickname'];
		$_G['user'] = $user;
		if(empty($_COOKIE['uid'])) {
			@ssetcookie('uid', $_G['uid']);
			@ssetcookie('nickname', $_G['nickname']);
		}
	} else {
		clear_cookies();
	}
}

?>