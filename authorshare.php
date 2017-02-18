<?php

require 'config.php';

$_GET['id'] = intval(getVar('id'));
if($_GET['id'] < 1) {
	$_GET['id'] = 0;
}

$navigator = array('authorshare' => ' class="curr"');

include template($_GET['id']>0&&@file_exists(template("authorshare_{$_GET['id']}"))?"authorshare_{$_GET['id']}":"authorshare");
?>