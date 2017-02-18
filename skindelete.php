<?php
/**
 *	UI中国作品(皮肤)删除接口
 */
require 'config.php';

$_POST['proid'] = intval(getVar('proid', 'p'));
$_POST['sign'] = getVar('sign', 'p');

//验证接口签名
$sign = md5('skindelete'.INTERFACE_KEY);
if($_POST['sign'] != $sign) {
	apimessage('sign_error', 1);
}

if($_POST['proid'] < 1) {
	apimessage('proid_error', 2);
}

$sql = "DELETE FROM `skindesign2016_upload` WHERE `projectid`={$_POST['proid']}";
if($MDB->Query($sql)) {
	apimessage('success', 0);
} else {
	apimessage('delete_fail', 3);
}
?>