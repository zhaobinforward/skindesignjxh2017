<?php

require 'config.php';
require R_ROOT.'/data/pc_prize.php';

if(DISPLAY){
	//pc
	$skin_ids = '';
	foreach($prize as $k=>$v){
		$skin_ids .= ",".implode(',',$v);
	}
	$skin_ids = trim($skin_ids,',');
	$sql = "select skin_id,picThumb,author,name from skin_info where skin_id in (".$skin_ids.")";
	$pc_prize = $PC_MDB->db_query($sql);
	foreach($pc_prize as $k=>&$val){
		$val['author'] = iconv("GB2312","UTF-8",$val['author']);
		if(mb_strlen($val['author'],'UTF-8') > 7){
			$val['author'] = mb_substr($val['author'],0,5,'UTF8')."..";
		}
		$val['name'] = iconv("GB2312","UTF-8",$val['name']);
		$val['picThumb'] = PC_PIC.$val['picThumb'];
	}
	//wap
	$sql = "select pickey9,skin_name,author_name,grade from skindesignali_upload where checked=1 and hit=1 and grade >0 order by grade desc,listorder desc limit 100";
	$tmp = $MDB->db_query($sql);
	$wap_prize = array();
	foreach($tmp as $k=>&$v){
		if(mb_strlen($v['author_name'],'utf-8') > 7){
			$v['author_name'] = mb_substr($v['author_name'],0,5,'utf-8')."..";
		}
		$v['pickey9'] = CLOUD_URL.basename($v['pickey9']);
		$wap_prize[$v['grade']][] = $v;
	}

	include template('prize');

}else{
	include template('prize_null');
}

?>