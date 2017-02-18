<?php

require 'config.php';

$navigator = array('pc' => 'class="active"');

$sql = "select count(*) as num from skin_info where name like binary '%阿狸%' and sign like '%,30825,%' and checked=1 and killed=0 ";
//$sql = "select count(*) as num from skin_info where name like binary '%阿狸%' and sign like '%,42,%' and checked=1 and killed=0 ";
$sql = iconv("UTF-8","GB2312",$sql);
$count = $PC_MDB->db_query($sql);
$count = $count[0]['num'];
$page = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1; 
$pagesize = 15;
$url = "pcshow.php";

$offset = ($page - 1) * $pagesize;
$sql = "select skin_id,picThumb,name,author from skin_info where name like binary '%阿狸%' and sign like '%,30825,%' and checked=1 and killed=0 order by skin_score desc ,date desc limit {$offset},{$pagesize}";
$sql = iconv("UTF-8","GB2312",$sql);
$content = $PC_MDB->db_query($sql);
foreach($content as $k=>&$v){
	$v['picThumb'] = PC_PIC.$v['picThumb'];
	$v['dl'] = PC_DL.$v['skin_id'];
	if(mb_strlen($v['author'],'GB2312') > 7){
		$v['author'] = mb_substr($v['author'],0,5,'GB2312')."..";
	}
}
 
$pageinfo = pageshow($count,$pagesize,$page,$url);

include template('pcshow');
?>