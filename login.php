<?php
/**
 */

require 'config.php';
require R_ROOT.'/include/inc.session.php';

$preg_refer = get_siteurl();
if(!isset($_GET['rurl'])) {
	$_GET['rurl'] = get_siteurl();
} else {
	if(!preg_match("@^{$preg_refer}@", $_GET['rurl'])){
		$_GET['rurl'] = '';
	} else {
		$_GET['rurl'] = urldecode($_GET['rurl']);
	}
}
$_jumpurl = $_GET['rurl'] ? $_GET['rurl'] : '';

if($_G['uid'] > 0){
	if(isajax()) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.location.href="login.php?rurl="+encodeURIComponent(window.location.href)</script>';exit;
	}
	showmessage('login_success', 1, empty($_jumpurl)?'./':$_jumpurl, 0);
}

$passportid = !empty($_SERVER["HTTP_X_SOHUPASSPORT_USERID"]) ? $_SERVER["HTTP_X_SOHUPASSPORT_USERID"]: '';
if($passportid) {
	$user = get_user($passportid, 'passportid');//本地用户
	$from_register = !1;//是否来自注册用户
	if(empty($user)) {
		$from_register = !0;
		$userinfo = get_userinfo($passportid);//获取通行证信息
		if(is_array($userinfo) && $userinfo['status'] == '0') {
			$userinfo = $userinfo['data'];
		} else {//取不到通行证信息(有可能是status=10002,不支持的账号类型)
			$userinfo = array();
		}
		
		if($userinfo) {
			$user = array(
				'passportid' => $userinfo['userid'],
				'nickname' => trim($userinfo['result']['nick']),
				'gender' => $userinfo['result']['sex'],
				'headimgurl' => trim($userinfo['result']['headurl']),
			);
		} else {//没获取到通行证信息
			$user = array(
				'passportid' => $passportid,
				'nickname' => substr($passportid, 0, strpos($passportid, '@')),
				'gender' => 0,//未知
				'headimgurl' => '',
			);
		}
		$user['regip'] = $_G['onlineip'];
		$user['ips'] = $_G['ips'];
		$user['salt'] = random(8);
		$user['create_time'] = TIMESTAMP;
		
		$insert_id = $MDB->insert_table('skindesignali_user', $user, true);
		if($insert_id < 1) {
			clear_cookies();
			showmessage('save_user_fail', 0);
		}
		$user['uid'] = $insert_id;
	}
	$_SESSION['uid'] = intval($user['uid']);
	$_SESSION['nickname'] = $user['nickname'];
	@ssetcookie('uid', intval($user['uid']));
	@ssetcookie('nickname', $user['nickname']);
	
	if($from_register && isajax()) {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.location.href="login.php?rurl="+encodeURIComponent(window.location.href)</script>';exit;
	}
	
	showmessage('login_success', 1, empty($_jumpurl)?'./':$_jumpurl, 0);
}

$siteurl = get_siteurl();
$rurl = $siteurl.'login.php?rurl='.urlencode($_jumpurl);


include template('login');
?>