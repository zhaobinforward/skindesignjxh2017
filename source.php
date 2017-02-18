<?php
/*кь╡дрЁ*/
require 'config.php';
require R_ROOT.'/data/img_source.php';

$count = count($img_source);
$page = intval($_GET['page'])>0 ? intval($_GET['page']) : 1 ;
$pagesize = 28;
$url = 'source.php';
$offset = ($page-1)*$pagesize;

$pageinfo = pageshow($count,$pagesize,$page,$url);
$content = @array_slice($img_source,$offset,$pagesize);

include template('source');
?>