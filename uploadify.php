<?php

require 'config.php';
require R_ROOT.'/include/func.upload.php';

if(submitcheck('dosubmit')) {
	if($_POST['formhash'] != formhash()) {
		showmessage('formhash_error', 0);
	}
	if($_POST['uid'] != 3029) {
		if((R_DATE_START > 0 && TIMESTAMP < R_DATE_START)) {
			showmessage('waiting_start', 0);//活动未开始
		}
		if((R_DATE_END > 0 && TIMESTAMP > R_DATE_END)) {
			showmessage('has_end', 0);//活动已结束
		}
	}
	
	$_GET['type'] = getVar('type');
	if(!in_array($_GET['type'], array('pic', 'rar', 'ssf'), !0)) {
		showmessage('unknown_file_type', 0);//未知的文件类型
	}
	if($_GET['type'] == 'pic') {
		$result = upload($_FILES['Filedata'], array('png', 'jpg', 'jpeg', 'gif'), MAX_PIC_SIZE, DATA_ROOT, 'upload');
	} elseif($_GET['type'] == 'rar') {
		$result = upload($_FILES['Filedata'], array('rar', 'zip'), MAX_RAR_SIZE, DATA_ROOT, 'upload');
	} else {
		$result = upload($_FILES['Filedata'], array('ssf'), MAX_SSF_SIZE, DATA_ROOT, 'upload');
	}
	if($result['status'] != 1) {//uploaded fail
		$message = '';
		switch($result['status']) {
			case -22:
				$message = '文件过大';break;
			case -21:
				$message = '不允许的文件类型';break;
			case -20:
				$message = '获取文件类型失败';break;
			case -5:
				$message = '不能上传空文件';break;
			default :
				$message = "文件上传失败({$result['status']})";
		}
		showmessage($message, 0);
	}
	
	$filedir = $result['data']['filedir'];
	$filename = $result['data']['filename'];
	
	if($_GET['type'] == 'rar' || $_GET['type'] == 'ssf') {//存储至kv
		$flag = uploadKV(md5($filename), DATA_ROOT."/{$filedir}/{$filename}");
		if($flag !== true) {
			@unlink(DATA_ROOT."/{$filedir}/{$filename}");
			showmessage('存储'.($_GET['type']=='rar'?'音效文件':'皮肤包文件').'失败', 0);
		}
		@file_put_contents(DATA_ROOT.'/rarfilename.txt', "{$filedir}/{$filename}\r\n", FILE_APPEND);
	} else {
		//检查图片的尺寸
		if(false === ($imagesize = @getimagesize(DATA_ROOT."/{$filedir}/{$filename}"))) {
			@unlink(DATA_ROOT."/{$filedir}/{$filename}");
			showmessage("上传的不是有效的图片文件", 0);
		}
		if($imagesize[0] != 1080 || $imagesize[1] != 887) {
			@unlink(DATA_ROOT."/{$filedir}/{$filename}");
			showmessage("上传的图片的尺寸不正确", 0);
		}
		
		//转移至云存储
		$flag = upload_cloud(DATA_ROOT."/{$filedir}/{$filename}", $filename);
		if(!is_array($flag) || !isset($flag[0]['status']) || $flag[0]['status'] != 0) {
			@unlink(DATA_ROOT."/{$filedir}/{$filename}");
			showmessage("存储图片失败({$flag[0]['status']})", 0);
		}
		@file_put_contents(DATA_ROOT.'/picfilename.txt', "{$filedir}/{$filename}\r\n", FILE_APPEND);
	}
	
	//unlink
	@unlink(DATA_ROOT."/{$filedir}/{$filename}");
	
	showmessage('upload_success', 1, '', 2, "{$filedir}/{$filename}");
}
?>