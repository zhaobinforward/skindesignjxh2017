<?php
//附件的形式下载图片并重命名
$file = $_GET['i'];
$name = $_GET['n'];
$type = $_GET['t'];
if(file_get_contents($file)){
	header("Content-type:octet/stream");
	header("Content-disposition:attachment;filename=".$name.".".$type.";");
	header("Content-Length:".filesize($file));
	readfile($file);
	exit;
}
?>