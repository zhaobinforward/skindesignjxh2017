<?php
/**
 *	UI�й���Ʒ(Ƥ��)ɾ���ӿ�
 */
require 'config.php';

$_POST['proid'] = intval(getVar('proid', 'p'));
$_POST['sign'] = getVar('sign', 'p');

//��֤�ӿ�ǩ��
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