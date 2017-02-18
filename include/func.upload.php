<?php
/**
 *	Upload functions
 */

/**
 *	功能 获取单个上传的文件
 *	@param $file Arary 文件$_FILES一个的子元素 必须
 *	@param $filetypes String/Array 允许的文件类型 可选 缺省array() 如 'sql' 或 array('sql') 或 array('sql','txt',...), ... 若传空数组则使用函数内部预定义类型
 *	@param $maxfilesize Integer 允许的文件字节大小(单位KB) 可选 缺省2048
 *	@param $prefix_dir String 前置目录 可选 缺省'' 缺省将使用DATA_ROOT作为前置目录
 *	@param $custom_dir String 自定义目录 可选 缺省'' 会拼接在$prefix_dir后
 *	@return Array array('status'=>Integer,'data'=>array('filedir'=>String,'filename'=>String,'name'=>String,'type'=>String,'size'=>Integer,'error'=>Integer))
 *		status含义：
 *		-99: 未定义错误
 *		-24: 移动临时文件失败
 *		-23: 文件保存的目录不存在或创建失败
 *		-22: 文件大小超过$filesize的限制
 *		-21: 不允许的文件类型
 *		-20: 获取文件名后缀失败
 *		-5: 上传文件大小为0
 *		-4: 没有文件被上传
 *		-3: 文件只有部分被上传
 *		-2: 文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
 *		-1: 文件超过了 php.ini 中 upload_max_filesize 选项限制的值
 *		0: file参数错误
 *		1: 成功
 */
function upload($file, $filetypes = array(), $maxfilesize = 2048, $prefix_dir = '', $custom_dir = '') {
	$file = is_array($file) ? $file : (array)$file;
	$filetypes = is_array($filetypes) ? $filetypes : (array)$filetypes;
	$prefix_dir = trim($prefix_dir);
	$savepath = '';
	
	$result = array(
		'status'=>0,
		'data'=>array(
			'filedir'=>'',//文件目录(相对DATA_ROOT的路径)
			'filename'=>'',//文件名(带后缀)
			'name'=>$file['name'],
			'type'=>$file['type'],
			'size'=>$file['size'],
			'ext'=>'',
			'error'=>$file['error']
		)
	);
	
	if(!isset($file['tmp_name']) || !isset($file['name']) || !isset($file['size']) || !isset($file['type']) || !isset($file['error'])) {
		$result['status'] = 0;
		return $result;
	}
	if($file['error'] != 0) {
		if($file['error'] == 1) {//上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
			$result['status'] = -1;
			return $result;
		} elseif($file['error'] == 2) {//上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值
			$result['status'] = -2;
			return $result;
		} elseif($file['error'] == 3) {//文件只有部分被上传
			$result['status'] = -3;
			return $result;
		} elseif($file['error'] == 4) {//没有文件被上传
			$result['status'] = -4;
			return $result;
		} elseif($file['error'] == 5) {//上传文件大小为0
			$result['status'] = -5;
			return $result;
		} elseif($file['error'] == 6) {//找不到临时文件夹
			$result['status'] = -6;
			return $result;
		} elseif($file['error'] == 7) {//写入文件失败
			$result['status'] = -7;
			return $result;
		} else {//未知错误
			$result['status'] = -99;
			return $result;
		}
	}
	$fileext = fileext($file['name']);//文件名后缀
	if($fileext == ''){
		$result['status'] = -20;
		return $result;
	}
	if(empty($filetypes)) {
		$filetypes = array('sql','txt','csv','jpg','jpeg','png','gif','bmp');
	}
	if(!in_array($fileext,$filetypes)) {
		$result['status'] = -21;
		return $result;
	}
	
	if($file['size'] > $maxfilesize*1024) {
		$result['status'] = -22;
		return $result;
	}
	
	$savepath = get_target_dir($fileext, $prefix_dir, $custom_dir);

	if(!is_dir($savepath) && !smkdir($savepath, 0775, false)) {
		$result['status'] = -23;
		return $result;
	}
	$filename = get_target_filename($fileext, true, 8);//带扩展的文件名
	$filepath = $savepath.'/'.$filename;
	//移动临时文件
	if(function_exists("move_uploaded_file") && move_uploaded_file($file['tmp_name'], $filepath)) {
	} elseif(rename($file['tmp_name'], $filepath)) {
	} elseif(copy($file['tmp_name'],$filepath)) {
		@unlink($file['tmp_name']);
	}else{
		$result['status'] = -24;
		return $result;
	}
	$result = array(
		'status'=>1,
		'data'=>array(
			'filedir'=>str_replace(($prefix_dir === '' ? DATA_ROOT : $prefix_dir).'/', '', $savepath),
			'filename'=>$filename,
			'name'=>$file['name'],
			'type'=>$file['type'],
			'size'=>$file['size'],
			'ext'=>$fileext,
			'error'=>$file['error']
		)
	);
	return $result;
}

/**	功能 根据文件后缀获取文件保存目录
 *	@param $fileext String 文件后缀名
 *	@param $prefix_dir String 前置目录 可选 缺省'' 缺省将使用DATA_ROOT作为前置目录
 *	@param $custom_dir Strign 自定义目录 可选 缺省'' 会拼接在$prefix_dir后
 *	@return String 文件保存目录(绝对路径) 如: /search/.../sweb/data/upload/uploadImage/2014/05
 */
function get_target_dir($fileext, $prefix_dir = '', $custom_dir = '') {
	$file_dir = trim($prefix_dir) === '' ? DATA_ROOT : $prefix_dir;
	$file_dir .= (substr($file_dir, -1) == '/' ? '' : '/') . $custom_dir;
	$file_dir .= (substr($file_dir, -1) == '/' ? '' : '/') . date('Ym') . '/' . date('d') ;
	return $file_dir;
}

/**	功能 根据文件后缀获取文件的保存名
 *	@param $fileext String 文件后缀名
 *	@param $numeric Boolean 是否纯数字串 缺省为false [true:是, false:否]
 *	@param $rand_length Integer 附加随机串长度 缺省8
 *	@return String 文件名(带后缀) 如: 140004sdfesadfsasd1e43.png
 */
function get_target_filename($fileext, $numeric = false, $rand_length = 8) {
	return date('YmdHis').strtolower(random($rand_length,$numeric)).'.'.$fileext;
}
?>