<?php

require 'config.php';

$_GET['view'] = getVar('view');
if(!in_array($_GET['view'], array('all','hits','me'), !0)) {
	$_GET['view'] = 'all';
}

$perpages = array(12, 15, 18, 21, 24);

$list = $loginuser = array();

if($_GET['view'] == 'me') {
	@include R_ROOT.'/include/inc.session.php';
	$loginuser = get_login_user($_G['uid']);
}

if($_GET['view'] == 'all' || ($_GET['view'] == 'me' && !empty($loginuser))) {//全部作品或我的作品
	
	$_GET['page'] = abs(intval(getVar('page')));
	$_GET['perpage'] = abs(intval(getVar('perpage')));
	
	$_GET['page'] = $_GET['page'] < 1 ? 1 : $_GET['page'];
	$_GET['perpage'] = in_array($_GET['perpage'], $perpages, !0) ? $_GET['perpage'] : $perpages[0];
	
	$start = ($_GET['page']-1)*$_GET['perpage'];
	$wheresql = '1';
	$ordersql = ($_GET['view'] == 'me' ? "" : "`digest` DESC,") . "`create_time` DESC, `id` DESC";
	$pageshow = '';
	$pageurl = 'gallery.php';
	
	if($_GET['view'] == 'me') {
		$wheresql .= " AND `uid`={$loginuser['uid']}";
	}
	
	if($_GET['view'] == 'all') {
		$wheresql .= " AND `checked`=1";
	}
	
	$sql = "SELECT COUNT(`id`) AS `count` FROM `skindesignjxh2017_upload` WHERE {$wheresql}";
	$count = $MDB->db_query($sql);
	if(0<($count = $count[0]['count'])) {
		$sql = "SELECT * FROM `skindesignjxh2017_upload` WHERE {$wheresql} ORDER BY {$ordersql} LIMIT {$start},{$_GET['perpage']}";
		$query = $MDB->Query($sql);
		while($tmp = $MDB->FetchArray($query)) {
			$tmp['piccover'] = empty($tmp['piccover'])?'':(substr($tmp['piccover'],0,4)=='http'?$tmp['piccover']:($tmp['status']==1?CLOUD_URL.basename($tmp['piccover']):WAPDL_URL.$tmp['piccover']));
			$tmp['pickey9'] = empty($tmp['pickey9'])?'':(substr($tmp['pickey9'],0,4)=='http'?$tmp['pickey9']:($tmp['status']==1?CLOUD_URL.basename($tmp['pickey9']):WAPDL_URL.$tmp['pickey9']));
			$tmp['pickey26'] = empty($tmp['pickey26'])?'':(substr($tmp['pickey26'],0,4)=='http'?$tmp['pickey26']:($tmp['status']==1?CLOUD_URL.basename($tmp['pickey26']):WAPDL_URL.$tmp['pickey26']));
			$list[] = $tmp;
		}
		
		$_GET['showpage'] = 9;//分页条长度
		$pageshow = pageshow($count, $_GET['perpage'], $_GET['page'], $pageurl);
	}
	
} elseif($_GET['view'] == 'hits') {//获奖作品
	//查询获奖的作品并group by
	$sql = "SELECT * FROM `skindesignjxh2017_upload` WHERE `checked`=1 AND `hit`=1 AND `grade`>0 ORDER BY `grade` DESC, `listorder` DESC LIMIT 100";
	$query = $MDB->Query($sql);
	while($tmp = $MDB->FetchArray($query)) {
		$tmp['piccover'] = empty($tmp['piccover'])?'':(substr($tmp['piccover'],0,4)=='http'?$tmp['piccover']:($tmp['status']==1?CLOUD_URL.basename($tmp['piccover']):WAPDL_URL.$tmp['piccover']));
		$tmp['pickey9'] = empty($tmp['pickey9'])?'':(substr($tmp['pickey9'],0,4)=='http'?$tmp['pickey9']:($tmp['status']==1?CLOUD_URL.basename($tmp['pickey9']):WAPDL_URL.$tmp['pickey9']));
		$tmp['pickey26'] = empty($tmp['pickey26'])?'':(substr($tmp['pickey26'],0,4)=='http'?$tmp['pickey26']:($tmp['status']==1?CLOUD_URL.basename($tmp['pickey26']):WAPDL_URL.$tmp['pickey26']));
		$list[$tmp['grade']][] = $tmp;
	}
	$navigator = array('gallery' => ' class="curr"');
	include template('gallery_hits');exit;
}

$navigator = array('gallery' => ' class="curr"');

include template('gallery_'.$_GET['view']);
?>