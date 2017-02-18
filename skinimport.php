<?php
/**
 *	UI中国作品(皮肤)导入接口
 */
require 'config.php';

$_POST['skin'] = getVar('skin', 'p');
$_POST['sign'] = getVar('sign', 'p');

$ssfdat = $_POST['skin'];

apimessage('cutoff', 999);

//验证接口签名
$sign = md5('skinimport'.INTERFACE_KEY);
if($_POST['sign'] != $sign) {
	apimessage('sign_error', 1);
}

if($ssfdat === '') {
	apimessage('skin_data_empty', 2);
}

@file_put_contents('text.txt', "{$ssfdat}\r\n\r\n", FILE_APPEND);
if(!is_array($ssfdat = @json_decode($ssfdat, true))) {
	apimessage('skin_data_error', 3);
}

$ssfdat['uid'] = intval($ssfdat['uid']);
if($ssfdat['uid'] < 1) {
	apimessage('uid_error', 4);
}

$ssfdat['projectid'] = intval($ssfdat['projectid']);
if($ssfdat['projectid'] < 1) {
	apimessage('projectid_error', 5);
}

//检查用户是否存在
$user = get_user($ssfdat['uid'], 'ucuid');
if(empty($user)) {
	$user = array(
		'ucuid' => $ssfdat['uid'],
		'nickname' => addslashes($ssfdat['username']),
		'regip' => $_G['onlineip'],
		'ips' => $_G['ips'],
		'salt' => random(8),
		'create_time' => TIMESTAMP,
	);
	if(1<($insertid = $MDB->insert_table('skindesign2016_user', $user, true))) {
		$user['uid'] = $insertid;
	} else {
		apimessage('register_fail', 6);
	}
} else {
	if($user['nickname'] != $ssfdat['username']) {
		$newarr = array('nickname'=>addslashes($ssfdat['username']),'update_time'=>TIMESTAMP);
		$MDB->update_table('skindesign2016_user', $newarr, "`uid`={$user['uid']}", true);
	}
}

//检查是否报名
$sql = "SELECT * FROM `skindesign2016_reginfo` WHERE `uid`={$user['uid']} LIMIT 1";
$query = $MDB->Query($sql);
if(!$reginfo = $MDB->FetchArray($query)) {
	$reginfo = array(
		'uid' => $user['uid'],
		'ip' => $_G['onlineip'],
		'nickname' => addslashes($ssfdat['username']),
		'qq' => $ssfdat['qq'],
		'email' => $ssfdat['email'],
		'telnumber' => $ssfdat['phone'],
		'from' => 1,
		'create_time' => TIMESTAMP,
	);
	if(1<($insertid = $MDB->insert_table('skindesign2016_reginfo', $reginfo, true))) {
		$reginfo['id'] = $insertid;
	} else {
		apimessage('register_fail', 7);
	}
} else {
	if($reginfo['nickname'] != $ssfdat['username'] 
		|| $reginfo['qq'] != $ssfdat['qq'] 
		|| $reginfo['email'] != $ssfdat['email'] 
		|| $reginfo['telnumber'] != $ssfdat['phone']) {
		$newarr = array(
			'nickname' => addslashes($ssfdat['username']),
			'qq' => $ssfdat['qq'],
			'email' => $ssfdat['email'],
			'telnumber' => $ssfdat['phone'],
			'update_time' => TIMESTAMP,
		);
		$MDB->update_table('skindesign2016_reginfo', $newarr, "`id`={$reginfo['id']}", true);
	}
}

//检查作品是否存在
$sql = "SELECT * FROM `skindesign2016_upload` WHERE `projectid`={$ssfdat['projectid']} LIMIT 1";
$query = $MDB->Query($sql);
if(!$skin = $MDB->FetchArray($query)) {
	$skin = array(
		'regid' => $reginfo['id'],
		'uid' => $user['uid'],
		'ucuid' => $user['ucuid'],
		'projectid' => $ssfdat['projectid'],
		'skin_name' => addslashes($ssfdat['project_title']),
		'short_name' => addslashes(shtmlspecialchars($ssfdat['shortname'])),
		'author_name' => addslashes($ssfdat['username']),
		'piccover' => addslashes($ssfdat['font_cover']),
		'pickey9' => addslashes($ssfdat['piclist'][0]),
		'pickey26' => addslashes($ssfdat['piclist'][1]),
		'rarfile' => addslashes($ssfdat['filedownload']),
		'ssffile' => '',
		'intro' => addslashes(shtmlspecialchars($ssfdat['introduction'])),
		'checked' => 0,
		'usekv' => 0,
		'status' => 0,
		'from' => 1,
		'create_time' => $ssfdat['createtime'],
		'update_time' => $ssfdat['lastupdate'],
	);
	if(1>$MDB->insert_table('skindesign2016_upload', $skin, true)) {
		apimessage('import_fail', 8);
	}
} else {
	$newarr = array(
		'skin_name' => addslashes($ssfdat['project_title']),
		'short_name' => addslashes(shtmlspecialchars($ssfdat['shortname'])),
		'author_name' => addslashes($ssfdat['username']),
		'piccover' => addslashes($ssfdat['font_cover']),
		'pickey9' => addslashes($ssfdat['piclist'][0]),
		'pickey26' => addslashes($ssfdat['piclist'][1]),
		'rarfile' => addslashes($ssfdat['filedownload']),
		'ssffile' => '',
		'intro' => addslashes(shtmlspecialchars($ssfdat['introduction'])),
		//'checked' => 0,
		'usekv' => 0,
		'status' => 0,
		'create_time' => $ssfdat['createtime'],
		'update_time' => $ssfdat['lastupdate'],
	);
	if(1>$MDB->update_table('skindesign2016_upload', $newarr, "`id`={$skin['id']}", true)) {
		apimessage('update_fail', 9);
	}
}

apimessage('success', 0);
?>