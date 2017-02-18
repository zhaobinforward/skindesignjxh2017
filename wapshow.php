<?php
//ÊÖ»úÆ¤·ô
require 'config.php';

$navigator = array('wap' => 'class="active"');

$sql = "select count(*) as num from skindesignali_upload where checked=1";
$count = $MDB->db_query($sql);
$count = $count[0]['num'];
$page = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
$pagesize  = 15;
$url = "wapshow.php";

$offset = ($page - 1)*$pagesize; 
$sql = "select pickey9,skin_name,author_name from skindesignali_upload where checked=1 order by listorder asc ,create_time desc limit {$offset},{$pagesize}";
$content = $MDB->db_query($sql);
foreach($content as $k=>&$v){
	$v['pickey9'] = CLOUD_URL.end(explode("/",$v['pickey9']));
	if(mb_strlen($v['author_name'],'utf-8') > 7){
		$v['author_name'] = mb_substr($v['author_name'],0,5,'utf-8')."..";
	}
}

$pageinfo = pageshow($count,$pagesize,$page,$url);

include template('wapshow');
?>