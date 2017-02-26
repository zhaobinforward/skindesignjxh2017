<?php

require 'config.php';
require R_ROOT.'/data/awesome_content.php';
require R_ROOT.'/include/inc.session.php';

//手机作品展示*4
$sql = "select pickey9 from skindesignjxh2017_upload where checked=1 and status=1 order by create_time desc limit 4";
//$sql = "select pickey9 from skindesignjxh2017_upload where status=1 order by create_time desc limit 3";
$wtop3 = $MDB->db_query($sql);
foreach($wtop3 as $k=>&$v){
//	$v['pickey9'] = CLOUD_URL.end(explode("/",$v['pickey9']));
	$v['pickey9'] = LOCAL_URL.$v['pickey9'];
}
unset($v);

include template('index');


?>