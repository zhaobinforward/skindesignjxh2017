<?php

require 'config.php';
require R_ROOT.'/include/inc.session.php';

$reginfo = array();
if($_G['uid'] > 0) {
	$sql = "SELECT * FROM `skindesignjxh2017_reginfo` WHERE `uid`={$_G['uid']} LIMIT 1";
	$reginfo = $MDB->db_query($sql);
	$reginfo = $reginfo[0];
}

//处理提交
if(submitcheck('dosubmit')) {
	if($_G['uid'] != 3029) {
		if((R_DATE_START > 0 && TIMESTAMP < R_DATE_START)) {
			showmessage('waiting_start', 0);//活动未开始
		}
		if((R_DATE_END > 0 && TIMESTAMP > R_DATE_END)) {
			showmessage('has_end', 0);//活动已结束
		}
	}
	
	if($_G['uid'] < 1) {
		showmessage('need_login', 2, 'login.php');
	}
	//获取参数
	$_POST['nickname'] = getVar('nickname');
	$_POST['qq'] = getVar('qq');
	$_POST['telnumber'] = getVar('telnumber');
	$_POST['email'] = getVar('email');
	
	$_POST['skin_name'] = getVar('skin_name');
	$_POST['short_name'] = getVar('short_name');
	$_POST['intro'] = getVar('intro');
	
	$_POST['picurl'] = getVar('picurl');
	$_POST['rarfile'] = getVar('rarfile');
	$_POST['ssffile'] = getVar('ssffile');
	
	//处理参数
	$_POST['nickname'] = getstr($_POST['nickname'], 48);
	$_POST['qq'] = getstr($_POST['qq'], 32);
	$_POST['telnumber'] = getstr($_POST['telnumber'], 32);
	$_POST['email'] = getstr($_POST['email'], 48);
	
	$_POST['skin_name'] = getstr($_POST['skin_name'], 60);
	$_POST['short_name'] = getstr($_POST['short_name'], 32);
	$_POST['intro'] = getstr($_POST['intro'], 900);
	
	$_POST['picurl'] = is_array($_POST['picurl']) ? $_POST['picurl'] : array();
//	foreach($_POST['picurl'] as $picurl) {
//		if(empty($picurl) || !urlcheck(CLOUD_URL.basename($picurl))) {
//			unset($_POST['picurl']);
//		}
//	}
	if(empty($_POST['picurl']) || count($_POST['picurl']) < 2) {
		showmessage('没有上传皮肤效果图或效果图少于2张', 0);
	}
	$_POST['picurl'] = array_merge($_POST['picurl']);

	if(empty($reginfo)) {//先报名
		if(empty($_POST['nickname'])) {
			showmessage('没有填写昵称', 0);
		}
		if(empty($_POST['qq'])) {
			showmessage('没有填写邮箱', 0);
		}
		if(empty($_POST['telnumber'])) {
			showmessage('没有填写手机号', 0);
		}
		if(!isemail($_POST['email'])) {
			showmessage('邮箱格式不正确', 0);
		}
		$newarr = array(
			'uid' => $_G['uid'],
			'ip' => $_G['onlineip'],
			'nickname' => addslashes($_POST['nickname']),
			'qq' => addslashes($_POST['qq']),
			'telnumber' => addslashes($_POST['telnumber']),
			'email' => addslashes($_POST['email']),
			'create_time' => TIMESTAMP
		);
		if(($regid = $MDB->insert_table('skindesignjxh2017_reginfo', $newarr, true)) < 1) {
			showmessage('报名失败', 0);
		}
	} else {//更新报名信息(如果需要的话)
		$regid = $reginfo['id'];
		$newarr = array();
		if($_POST['nickname']) {
			$newarr['nickname'] = addslashes($_POST['nickname']);
		}
		if($_POST['qq']) {
			$newarr['qq'] = addslashes($_POST['qq']);
		}
		if($_POST['telnumber']) {
			$newarr['telnumber'] = addslashes($_POST['telnumber']);
		}
		if($_POST['email']) {
			$newarr['email'] = addslashes($_POST['email']);
		}
		if($newarr) {
			$newarr['update_time'] = TIMESTAMP;
		}
		if($newarr && 1 > $MDB->update_table('skindesignjxh2017_reginfo', $newarr, "`id`={$regid}", true)) {
			showmessage('更新报名信息失败', 0);
		}
	}
	
	$newarr = array(
		'regid' => $regid,
		'uid' => $_G['uid'],
		'skin_name' => addslashes($_POST['skin_name']),
		'short_name' => addslashes($_POST['short_name']),
		'intro' => addslashes($_POST['intro']),
		'author_name' => addslashes($_POST['nickname']),
		'pickey9' => addslashes($_POST['picurl'][0]),//9键图
		'pickey26' => addslashes($_POST['picurl'][1]),//26键图
//		'rarfile' => addslashes($_POST['rarfile']),
//		'ssffile' => addslashes($_POST['ssffile']),
		'checked' => 0,
		'status' => 1,
//		'usekv' => 1,
//		'from' => 0,
		'create_time' => TIMESTAMP,
	);
	
	if($MDB->insert_table('skindesignjxh2017_upload', $newarr) < 1) {
		showmessage('提交失败', 0);
	}
	
	showmessage('提交成功', 1);
}

$navigator = array('upload' => ' class="curr"');

include template('upskin');
?>