<?php

/**	获取$_GET,$_POST,$_COOKIE变量
 *	@param $ket String/Integer 键名
 *	@param $type String 获取的类型 缺省'GP'
 *	@teturn String/NULL
 */
function getVar($key, $type='GP') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$key])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}
	return isset($var[$key]) ? $var[$key] : NULL;
}

/**	功能 调试变量(针对浏览器界面友好输出)
 *	@param $var Mixed 要调试的变量
 *	@param $vardump Boolean 是否使用vardump函数输出变量信息 [false:使用print_r输出,true:使用var_dump输出]
 *	@param $exit Boolean 输出完是否终止运行
 *	@teturn void
 */
function sdebug($var = null, $vardump = false, $exit = false) {
	echo '<pre>';
	if($vardump) {
		var_dump($var);
	} else {
		print_r($var);
	}
	echo '</pre>';
	if($exit)exit();
}

/**
 *	递归建立目录
 */ 
function mkdir_recursive($pathname, $mode=0755) {
	is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
	return is_dir($pathname) || @mkdir($pathname, $mode);
}

function utf2gb($utf8){
	return iconv("UTF-8","GB2312//IGNORE",$utf8);
}

function utf2gbk($utf8){
	return iconv("UTF-8","GBK//IGNORE",$utf8);
}

function gb2utf($utf8){
	return iconv("GB2312","UTF-8//IGNORE", $utf8);
}

function gbk2utf($utf8){
	return iconv("GBK","UTF-8//IGNORE", $utf8);
}

function utf2gb_recursive($string) {
	if(is_array($string)) {
		foreach($string as $key => $value) {
			$string[$key] = utf2gb_recursive($value);
		}
	} else {
		$string = utf2gb($string);
	}
	return $string;
}

function utf2gbk_recursive($string) {
	if(is_array($string)) {
		foreach($string as $key => $value) {
			$string[$key] = utf2gbk_recursive($value);
		}
	} else {
		$string = utf2gbk($string);
	}
	return $string;
}

function gb2utf_recursive($string){
	if(is_array($string)) {
		foreach($string as $key => $value) {
			$string[$key] = gb2utf_recursive($value);
		}
	} else {
		$string = gb2utf($string);
	}
	return $string;
}

function gbk2utf_recursive($string){
	if(is_array($string)) {
		foreach($string as $key => $value) {
			$string[$key] = gbk2utf_recursive($value);
		}
	} else {
		$string = gbk2utf($string);
	}
	return $string;
}

/**	随机获取数组中指定数量的元素,保留数组的索引关联
 *	@param $arr Array 要获取元素的原始数组
 *	@param $num Integer 随机获取的元素个数
 *	@teturn Array
 */
function sarray_rand($arr, $num = 1) {
	$r_values = array();
	if($arr && count($arr) > $num) {
		if($num > 1) {
			$r_keys = array_rand($arr, $num);
			foreach ($r_keys as $key) {
				$r_values[$key] = $arr[$key];
			}
		} else {
			$r_key = array_rand($arr, 1);
			$r_values[$r_key] = $arr[$r_key];
		}
	} else {
		$r_values = $arr;
	}
	return $r_values;
}

/**	由数组元素生成连接字符串
 *	@param $arr Array 数组
 *	@param $sep String 用于连接数组元素的连接符
 *	@param $wrap String 数组元素修饰符 可能的值[', ", `, ...]
 *	@teturn String/Integer(0)
 */
function simplode($arr, $sep = ',', $wrap = '\'') {
	$arr = is_array($arr) ? $arr : (array)$arr;
	if(!empty($arr)) {
		return $wrap.implode($arr, $wrap.$sep.$wrap).$wrap;
	} else {
		return 0;
	}
}

/**	功能 生成随机字符串
 *	@param $length Integer 生成的随机串的字符长度
 *	@param $numeric Boolean 是否纯数字串 缺省为false [true:是, false:否]
 *	@return String
 */
function random($length, $numeric = false) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

/**	功能 是否ajax请求
 *	@return Boolean
 */
function isajax() {
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])){
			return true;
		}
    }
	if(!empty($_POST['inajax']) || !empty($_GET['inajax']))
		// 判断Ajax方式提交
		return true;
	return false;
}

/**	功能 分页
 *	@param $count Integer 总数
 *	@param $perpage Integer 每页显示数
 *	@param $curpage Integer 当前页
 *	@param $mpurl String 页面url
 *	@param $showpagejump Boolean 是否显示跳页输入框,在非ajax分页时才生效
 *	@param $todiv String 页面锚记
 *	@param $ajaxdiv String ajax显示的目标元素id
 *	@param $pagevar String 页码参数名 缺省为'page'
 *	@return String
 */
function pageshow($count, $perpage, $curpage, $mpurl, $showpagejump = false, $todiv = '', $ajaxdiv = '', $pagevar = 'page'){
	$inajax = isajax();//是否ajax请求
	$prevname = '上一页';
	$nextname = '下一页';
	if(empty($ajaxdiv) && $inajax) {
		$ajaxdiv = $_GET['ajaxdiv'];
	}

	$page = 5;//规定的分页条的显示长度
	if($_GET['showpage']) $page = abs(intval($_GET['showpage']));
	if($page<3) $page=3; 

	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&' : '?';
	$realpages = 1;//计算出的实际页数
	if($count > $perpage) {
		$offset = (int)($page/2);
		$realpages = @ceil($count / $perpage);//计算出的实际页数
		if($curpage > $realpages) $curpage = $realpages;
		$pages = $_GET['maxpage'] && $_GET['maxpage'] < $realpages ? $_GET['maxpage'] : $realpages;//计算出的实际页数,受 maxpage(最大页数) 参数限制,不能翻页到无限大的页码
		if($page > $pages) {//规定的显示长度 > 计算出的总页数
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = '';
		$urlplus = $todiv ? "#$todiv" : '';
		if($curpage > 1) {
			$multipage .= "<a ";
			if($inajax) {
				$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}{$pagevar}=".($curpage-1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
			} else {
				$multipage .= "href=\"{$mpurl}{$pagevar}=".($curpage-1)."$urlplus\"";
			}
			$multipage .= " class=\"prev\">{$prevname}</a>";
		} else {
			$multipage .= "<a href=\"javascript:;\" class=\"prev disabled\">{$prevname}</a>";
		}
		if($curpage - $offset > 1 && $pages > $page) {
			$multipage .= "<a ";
			if($inajax) {
				$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}{$pagevar}=1&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
			} else {
				$multipage .= "href=\"{$mpurl}{$pagevar}=1{$urlplus}\"";
			}
			$multipage .= " class=\"first\">1</a><span class=\"etc\">...</span>";
		}
		for($i = $from; $i <= $to; $i++) {
			if($i == $curpage) {
				$multipage .= '<a class="active">'.$i.'</a>';
			} else {
				$multipage .= "<a ";
				if($inajax) {
					$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}{$pagevar}=$i&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
				} else {
					$multipage .= "href=\"{$mpurl}{$pagevar}=$i{$urlplus}\"";
				}
				$multipage .= ">$i</a>";
			}
		}
		if($to < $pages) {
			$multipage .= "<span class=\"etc\">...</span><a ";
			if($inajax) {
				$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}{$pagevar}=$pages&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
			} else {
				$multipage .= "href=\"{$mpurl}{$pagevar}=$pages{$urlplus}\"";
			}
			$multipage .= " class=\"last\">$realpages</a>";
		}
		if($curpage < $pages) {
			$multipage .= "<a ";
			if($inajax) {
				$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}{$pagevar}=".($curpage+1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
			} else {
				$multipage .= "href=\"{$mpurl}{$pagevar}=".($curpage+1)."{$urlplus}\"";
			}
			$multipage .= " class=\"next\">{$nextname}</a>";
		} else {
			$multipage .= "<a href=\"javascript:;\" class=\"next disabled\">{$nextname}</a>";
		}
		if($multipage && !$inajax && $showpagejump) {
			$multipage .= '<label>';
			$multipage .= '<input type="text" name="custompage" class="px" size="2" title="输入页码,按回车快速跳转" value="" onkeydown="if(event.keyCode==13) {window.location=\'' . $mpurl . $pagevar . '=\'+this.value;doane(event);}"/>';
			$multipage .= '<span title="共 '. $realpages .' 页"> / ' . $realpages . ' 页</span>';
			$multipage .= '</label>';
		}
	}
	$multipage = empty($multipage) ? '' : "<div class=\"pg\">{$multipage}</div>";
	return $multipage;
}

/**	功能 获取client ip
 *	@param $only_clientip Boolean 缺省false
	该参数的作用 用户使用代理访问时,取到的可能是多个ip: ip1,ip2,... 当 only_clientip=ture时仅仅返回ip1(clientip), 否则返回全部ip
 *	@return String
 */
function get_onlineip($only_clientip = false) {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	if($onlineip && $only_clientip && strpos($onlineip, ',') !== false) {
		$onlineip = substr($onlineip, 0, strpos($onlineip, ','));
	}
	return $onlineip;
}

/**	功能 生成表单hash串,用于表单安全验证
 *	@param $specialadd String 额外加密串 可选 缺省''
 *	@return String
 */
function formhash($specialadd = '') {
	return substr(md5(substr(TIMESTAMP, 0, -7).SITE_AUTHKEY.$specialadd), 8, 8);
}

/**	功能 表单安全验证
 *	@param $var String 表单标识变量
 *	@return Boolean/Void
 */
function submitcheck($var) {
	if(!$_POST[$var]) {
		return false;
	} else {
		if($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) ||
		preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
			return true;
		} else {
			echo "111";
			exit;
			showmessage('submit_invalid');
		}
	}
}

/**	功能 检查$email是否为邮箱地址
 *	@param $email String 要检查的邮箱名
 *	@return Boolean
 */
function isemail($email) {
	return strlen($email) > 6 && strlen($email) <= 32 && preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $email);
}

function isip($ip) {
	return preg_match('/\d{1,3}(\.\d{1,3}){3}/', $ip) ? true : false;
}

function is_weixin() {
	if(stripos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return true;
	}	
	return false;
}

/**	功能 格式化字节大小
 *	@param $size Integer 文件字节数
 *	@return String 如:10.1KB, 0.99MB, ...
 */
function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

/**	获取指定模板文件的模板全名
 *	@param $filename String 不带后缀的模板文件名
 *	@param $fileext String 模板文件后缀名
 *	@teturn String
 */
function template($filename, $fileext = TPL_FILEEXT) {
	return R_ROOT."/templates/{$filename}.{$fileext}";
}

/**
 * @param $message String	提示消息
 * @param $status Integer	状态码
 * @param $data	String/Array 传递数据
 * @return void
 */
function apimessage($message, $status = 0, $data = '') {
	$status = intval($status);
	$message = array('message'=>$message,'status'=>$status,'data'=>$data);
	$message = json_encode($message);
	@header("Content-type: text/html");
	@ob_clean();
	echo $message;
	exit();
}

/**
 * @param $message String	提示消息
 * @param $status Integer	状态	0/1/2 : 失败/成功/提示
 * @param $jumpurl String	跳转url
 * @param $waiting Integer	等待时间
 * @param $data	String/Array 传递数据
 * @param $tplname String  使用特定的showmessage模板 如: skinupload
 * @param $values Array 语言替换
 */
function showmessage($message, $status = 0, $jumpurl = '', $waiting = 2, $data = '', $tplname = '', $values = array()) {
	$_GET['inshowmessage'] = true;
	$message = lang($message, 'message', $values);
	$status = intval($status);
	$waiting = intval($waiting);
	if($waiting<0) $waiting = 0;
	
	if(isajax()) {
		$_GET['retstruct'] = isset($_GET['retstruct']) ? strtolower($_GET['retstruct']) : '';
		$_GET['retstruct'] = $_GET['retstruct'] !== 'json' && $_GET['retstruct'] !== 'text' ? 'json' : $_GET['retstruct'];
		$_GET['rettype'] = isset($_GET['rettype']) ? strtolower($_GET['rettype']) : '';
		$_GET['rettype'] = $_GET['rettype'] !== 'xml' && $_GET['rettype'] !== 'html' && $_GET['rettype'] !== 'text' ? 'text' : $_GET['rettype'];
		if($_GET['retstruct'] == 'json'){//json结构
			$message = array('message'=>$message,'status'=>$status,'url'=>$jumpurl,'waiting'=>$waiting,'data'=>$data);
			$message = json_encode($message);
		} else {//text结构
			if($data === '' || (is_array($data) && empty($data)))
				$data = '';
			elseif(is_array($data))
				$data = json_encode($data);
			$message .= '<status value="'.$status.'"></status>';
			$message .= '<url value="'.$jumpurl.'"></url>';
			$message .= '<waiting value="'.$waiting.'"></waiting>';
			$message .= '<data value="'.$data.'"></data>';
		}
		if($_GET['rettype'] == 'xml') {//xml类型
			xml_out($message);
		} elseif($_GET['rettype'] == 'html') {//html类型
			html_out($message);
		} else {//text类型
			@header("Content-type: text/html");
			@ob_clean();
			echo $message;
			exit();
		}
	} else {
		if($waiting > 0){//调用模板显示
			include template('showmessage');
		}else{//header转向
			if($jumpurl){
				@header("HTTP/1.1 301 Moved Permanently");
				@header('Location:'.$jumpurl);
			}else{
				@header('Location:./');
			}
		}
		exit();
	}
}

//xml输出
function xml_out($content){
	if(!$content) $content = "";
	$content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<root><![CDATA[$content]]></root>";
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml");
	@ob_clean();
	echo $content;
	exit();
}

//html输出
function html_out($content){
	if(!$content) $content = "";
	$content = "<html><head><title>ajax_html_output</title></head><body>$content</body></html>";
	@header("Content-type: text/html");
	@ob_clean();
	echo $content;
	exit();
}

//set全局变量
function setglobal($key , $value, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group.'/'.$key);
	$p = &$_G;
	foreach ($key as $k) {
		if(!isset($p[$k]) || !is_array($p[$k])) {
			$p[$k] = array();
		}
		$p = &$p[$k];
	}
	$p = $value;
	return true;
}

//get全局变量
function getglobal($key, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group.'/'.$key);
	$v = &$_G;
	foreach ($key as $k) {
		if (!isset($v[$k])) {
			return null;
		}
		$v = &$v[$k];
	}
	return $v;
}

//语言函数
function lang($langvar = null, $file, $vars = array(), $default = null) {
	global $_G;
	list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}
	
	$key = $path == '' ? $file : $path.'_'.$file;
	if(!isset($_G['lang'][$key])) {
		include R_ROOT.'/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
		$_G['lang'][$key] = isset($lang) ? $lang : array();
	}

	$returnvalue = &$_G['lang'];
	
	$return = $langvar !== null ? (isset($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : null) : $returnvalue[$key];
	$return = $return === null ? ($default !== null ? $default : $langvar) : $return;
	$searchs = $replaces = array();
	if($vars && is_array($vars)) {
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	if(is_string($return) && strpos($return, '{_G/') !== false) {
		preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
		foreach($gvar[0] as $k => $v) {
			$searchs[] = $v;
			$replaces[] = getglobal($gvar[1][$k]);
		}
	}
	$return = str_replace($searchs, $replaces, $return);
	return $return;
}

/**	功能 获取站点url
 *	@param void
 *	@return String
 */
function get_siteurl() {
	if(defined('SITE_URL')) {
		return SITE_URL;
	} else {
		$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
		return shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1));
	}
}

/**	功能 递归创建目录
 *	@param $dir String 要创建的目录名 必须
 *	@param $mode Integer 目录权限 可选 缺省0775
 *	@param $exit Boolean 创建失败是否exit 可选 缺省false
 *	@return Boolean [true:'创建成功',false:'创建失败']
 */
function smkdir($dir, $mode = 0775, $exit = false){
	$exit = $exit ? true : false;
	if(!is_dir($dir)) {
		smkdir(dirname($dir), $mode, $exit);
		$flag = @mkdir($dir,$mode);
		if(!$flag && $exit)
			exit("创建目录失败:{$dir}");
	} else {
		$flag = true;
	}
	return $flag ? true : false;
}

/**	功能 获取文件名后缀
 *	@param $filename String 带后缀的文件名 必须 如 'data.txt','data.sql'
 *	@return String ''或'sql','txt',...
 */
if(!function_exists('fileext')){
	function fileext($filename) {
		return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
	}
}

/**	功能 转义$string中的HTML代码
 *	@param $string String
 *	@return String
 */
function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

/**	功能 添加转义
 *	@param $string String/Array
 *	@return String/Array
 */
if(!function_exists('saddslashes')) {
function saddslashes($string) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}
}

/**	功能 去除转义
 *	@param $string String/Array 待处理的串
 *	@return String/Array 去除转义处理后的串
 */
function sstripslashes($string) {
	if(empty($string)) return $string;
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

/**	功能 检测字符串是否存在
 *	@param $haystack String 被查找的字符串
 *	@param $needle String 要查找的字符串
 *	@param $case Boolean 大小写敏感 缺省true(大小写敏感)
 *	@return Boolean 存在则返回true 不存在则返回false
 */
function strexists($haystack, $needle, $case = true){
	return $case ?  !(strpos($haystack, $needle) === false) : !(stripos($haystack, $needle)===false);
}

/**	功能 截取字符串
 *	@param $string String 要截取的字符串
 *	@param $length Integer 截取的长度
 *	@param $dot String 截取后追加的串(没有截取则不会追加)
 *	@param $rule Integer 截取规则 缺省0 [0:按照字节长度截取,utf8或非utf8,1个中文字符字节长度记为3或2,1个非中文字符字节长度记为1或1, 1或其他值:按照字长度截取,不论何种编码,不论是否中文字符,1个字符的字长均记为1]
 *	@param $charset String $string参数的字符编码类型 缺省utf-8
 *	@return String
 */
function cutstr($string, $length, $dot = '...', $rule = 0, $charset = 'utf-8') {
	if($string === '')
		return $string;
	$charset = $charset === '' ? 'utf-8' : strtolower($charset);
	$charset = $charset == 'utf-8' || $charset == 'utf8' ? 'utf-8' : $charset;
	
	$strlen = strlen($string);//字符串的字节长度
	if($rule == 0 && $strlen <= $length) {
		return $string;
	}
	
	$wl = $rule == 0 ? ($charset == 'utf-8' ? 3 : 2) : 1;//1个非英文字符用于计算的长度
	$strcut = '';
	if($charset == 'utf-8') {
		$n = $tn = $noc = 0;
		while($n < $strlen) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || $t == 13 || (32 <= $t && $t <= 126)) {//水平制表符,换行符,回车符,及空格,字母普通字符
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += $wl;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += $wl;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += $wl;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += $wl;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += $wl;
			} else {// [0,8] U {11,12} U [14,31] U [127,193] U {254,255} 均为控制符,不计入长度,但产生字节位移
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}
	} else {
		$n = $tn = $noc = 0;
		while($n < $strlen) {
			if(ord($string[$n]) <= 127) {
				$tn = 1; $n++; $noc++;
			} else {
				$tn = 2; $n += 2; $noc += $wl;
			}
			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
	}
	$strcut = substr($string, 0, $n);
	
	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return strlen($strcut) == $strlen ? $strcut : $strcut.$dot;
}

/**	功能 隐藏(替换为空或其他掩盖性字符)字符串
 *	@param $string String 要隐藏的字符串
 *	@param $hidlen Integer 隐藏的字的长度(中文或非中文字符,1个字符记为1字长)
 *	@param $replace String 隐藏的字符被该参数替换,该参数可以是空或其他掩盖性字符
 *	@param $maxlen Integer 字符串的最大字长(超过该值会进行截取) 缺省0(0为不限制)
 *	@param $charset String $string参数的字符编码类型 缺省utf-8
 *	@return String
 */
function hiddenstr($string, $hidlen, $replace = '*', $maxlen = 0, $charset = 'uft-8') {
	$hidlen = abs(intval($hidlen));
	$maxlen = abs(intval($maxlen));
	$charset = $charset === '' ? 'utf-8' : strtolower($charset);
	$charset = $charset == 'utf-8' || $charset == 'utf8' ? 'utf-8' : $charset;
	
	$strlen = strlen($string);//字符串的字节长度
	
	if($maxlen) {
		$string = cutstr($string, $maxlen, '', $charset, 1);
	}
	
	if($hidlen) {
		$wordlength = wordlength($string, 1, $charset);
		if($wordlength > $hidlen) {
			$string = cutstr($string, ($wordlength - $hidlen), '', $charset, 1);
			for($i = 0; $i < $hidlen; $i++) {
				$string .= $replace;
			}
		}
	}
	
	return $string;
}

/**	功能 计算字符串的字长(字符串的字节数或字符个数,视计算规则而定)
 *	@param $string String 要计算字长的字符串
 *	@param $rule Integer 计算规则 缺省0 [0:字长等于字节数,utf8/非utf8编码下,中文符1个字符的字长记为3/2,其他字符1个字符的字长记为1/1, 1:字长等于字符个数,即中文或非中文字符,1个字符记为1字长]
 *	@param $charset String $string参数的字符编码类型 缺省utf-8
 *	@return Integer
 */
function wordlength($string, $rule = 0, $charset = 'uft-8'){
	$charset = $charset === '' ? 'utf-8' : strtolower($charset);
	$charset = $charset == 'utf-8' || $charset == 'utf8' ? 'utf-8' : $charset;
	
	$wl = $rule == 0 ? ($charset == 'utf-8' ? 3 : 2) : 1;//1个非英文字符用于计算的长度
	$strlen = strlen($string);
	
	if($rule == 0)
		return $strlen;
	
	if($charset == 'utf-8') {
		$n = $noc = 0;
		while($n < $strlen) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || $t == 13 || (32 <= $t && $t <= 126)) {//水平制表符,换行符,回车符,及空格,字母普通字符
				$n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$n += 2; $noc += $wl;
			} elseif(224 <= $t && $t <= 239) {
				$n += 3; $noc += $wl;
			} elseif(240 <= $t && $t <= 247) {
				$n += 4; $noc += $wl;
			} elseif(248 <= $t && $t <= 251) {
				$n += 5; $noc += $wl;
			} elseif($t == 252 || $t == 253) {
				$n += 6; $noc += $wl;
			} else {// [0,8] U {11,12} U [14,31] U [127,193] U {254,255} 均为控制符,不计入长度,但产生字节位移
				$n++;
			}
		}
	} else {
		$n = $noc = 0;
		while($n < $strlen) {
			if(ord($string[$n]) <= 127) {
				$n++; $noc++;
			} else {
				$n += 2; $noc += $wl;
			}
		}
	}
	
	return $noc;
}

/**	功能 字符串过滤
 *	@param $String String 待处理的输入字符串
 *	@param $length Integer 要截取的长度 缺省为0(0表示不截取)
 *	@rule $rule 截取规则 参照 cutstr()函数的对应参数
 *	@param $in_slashes Integer 输入串是否已转义 缺省0 [0:待处理的字符串已经添加转义,~0:待处理的字符串未添加转义]
 *	@param $out_slashes Integer 输出串是否需要添加转义 缺省0 [0:输出字符串不需要添加转义,~0:输出字符串需要添加转义]
 *	@param $html Integer 是否进行html过滤处理 缺省0 [小于0:对字符串进行去html标签,等于0:对字符串进行转换html标签]
 *	@return String
 */
function getstr($string, $length = 0, $rule = 0, $in_slashes = 0, $out_slashes = 0, $html = 0) {
	$string = trim($string);
	$sppos = strpos($string, chr(0).chr(0).chr(0));
	if($sppos !== false) {
		$string = substr($string, 0, $sppos);
	}
	if($in_slashes) {
		$string = sstripslashes($string);
	}
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = shtmlspecialchars($string);
	}

	if($length > 0) {
		$string = cutstr($string, $length, '', $rule);
	}

	if($out_slashes) {
		$string = saddslashes($string);
	}
	return trim($string);
}

/**	功能 读取缓存文件
 *	@param $file String 缓存文件名(不带目录)
 *	@param $dir String 缓存文件所在目录 可选 缺省''(缺省将使用系统设定的CACHE_ROOT)
 *	@return Array
 */
function cache_read($file, $dir = '') {
	if(!$dir) $dir = CACHE_ROOT;
	$cachefile = $dir.'/'.$file;
	return @include $cachefile;
}

/**	功能 写缓存文件
 *	@param $file String 缓存文件名(不带目录)
 *	@param $array Array 缓存的数组变量
 *	@param $dir String 缓存文件存放的目录 可选 缺省''(缺省将使用系统设定的CACHE_ROOT)
 *	@return Integer/Boolean 失败返回false/成功返回写入文件的字节数
 */
function cache_write($file, $array, $dir = '') {
	if(!is_array($array)) return false;
	$array = "<?php\n//".date('Y-m-d H:i:s')."\nreturn ".arrayeval($array).";\n?>";
	$cachefile = ($dir ? $dir : CACHE_ROOT).'/'.$file;
	$strlen = file_put_contents($cachefile, $array);
	@chmod($cachefile, 0777);
	return $strlen;
}

/**	功能 生成更新sql语句
 *	@param $table String 表名
 *	@param $data Array 如array('field1'=>$value1,'field2'=>$value2, ...)
 *	@param $condition String 条件 如"`uid`=1"
 *	@param $limit 限制更新条数 缺省false[true:是, false:否]
 *	@return String
 */
function parse_update_sql($table, $data, $condition, $limit = false) {
	$sql = $comma = '';
	if(is_array($data) && $data) {
		$sql = "UPDATE `{$table}` SET ";
		foreach($data as $key => $value) {
			$sql .= "{$comma}`{$key}`='{$value}'";
			$comma = ',';
		}
		$sql .= " WHERE ".$condition;
		if($limit) {
			$sql .= " LIMIT 1";
		}
	}
	return $sql;
}

/**	功能 生成插入sql语句
 *	@param $table String 表名
 *	@param $data Array 如array('field1'=>$value1,'field2'=>$value2, ...)
 *	@return String
 */
function parse_insert_sql($table, $data) {
	$sql_head = $sql = $comma = '';
	if(is_array($data) && $data) {
		$sql_head .= "INSERT INTO `{$table}`(";
		$sql .= "VALUES(";
		foreach($data as $key => $value) {
			$sql_head .= "{$comma}`{$key}`";
			$sql .= "{$comma}'{$value}'";
			$comma = ',';
		}
		$sql_head .= ")";
		$sql .= ")";
		$sql = $sql_head . " " . $sql;
	}
	return $sql;
}

/**	功能 递归的方式将数组转换为字符串描述形式
 *	@param $array Array/String 数组或字符串 必须
 *	@param $level Integer 层级 可选,缺省0
 *	@return String
 */
function arrayeval($array, $level = 0) {
	if(!is_array($array)) {
		return "'".$array."'";
	}
	if(is_array($array) && function_exists("var_export")) {
		return var_export($array, true);
	}

	$comma = $space = '';
	for($i = 0; $i < $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	if(is_array($array)) {
		foreach($array as $key => $val) {
			$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
			$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			if(is_array($val)) {
				$evaluate .= "$comma\t$key => ".arrayeval($val, $level + 1);
			} else {
				$evaluate .= "$comma\t$key => $val";
			}
			$comma = ",\n$space";
		}
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

/**	功能 加密解密相关
 *	@param $string String 要加密或解密的串
 *	@param $operation 操作类型(加密/解密) 可选 缺省'DECODE' ['DECODE':解密, 'ENCODE':加密]
 *	@param $key 加密或解密的密钥(该值为''时,使用系统内置的密钥进行加密或解密) 可选 缺省''
 *	@param $expiry 明文密钥有效期 单位:秒 可选 缺省0(密钥永久有效) $expiry>0时,密钥的有效期为$expiry秒,超过$expiry秒后,解密函数返回空字符串(即解密不出结果)
 *	@return String
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : SITE_AUTHKEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

/**	功能 设置站点cookie
 *	@param $var String cookie变量名
 *	@param $value String cookie变量值 可选 缺省''
 *	@param $life Integer cookie生存时间 单位:秒 可选 缺省0
 *	@param $httponly Boolean 设定是否禁止客户端操作该cookie 可选 缺省false [true:是, false:否]
 *	@return void
 */
function ssetcookie($var, $value = '', $life = 0, $path = '/', $domain = COOKIE_DOMAIN, $httponly = false) {
	$_COOKIE[$var] = $value;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? TIMESTAMP + $life : ($life < 0 ? TIMESTAMP - 31536000 : 0);
	$path = empty($path) || $path == '/' ? ($httponly && PHP_VERSION < '5.2.0' ? '/; HttpOnly' : '/') : $path;

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		return setcookie($var, $value, $life, $path, $domain, $secure);
	} else {
		return setcookie($var, $value, $life, $path, $domain, $secure, $httponly);
	}
}

/**	功能 url检查
 *	@param $url String url
 */
function urlcheck($url) {
	$array = get_headers($url); 	
	if(preg_match('/http.*?(200|302)/i',$array[0])){ 
		return true;
	}else{ 
		return false;
	} 
}

/**	功能 curl http get请求
 *	@param $url String 请求的url地址
 *	@param $params String/Array http get请求的参数[String形式:a=b&c=d, Array形式:array('a'=>'b','c'=>'d')]
 *	@param &$result Mixed(Array/String/Integer) 请求的结果 或 curl失败的状态码
 *	@param $headers Array 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
 *	@return Integer
 *		1: 请求成功
 *		0: url为空
 *		-1: 初始化curl失败
 *		-2: curl_exec失败
 *		-3: 响应的状态码有误(不等于200)
 */
function curl_http_get($url, $params, &$result, $headers = array()) {
	if($url === '')
		return 0;
	$url .= stripos($url, '?') === false ? '?' : '';
	$param_str = '';
	if(is_array($params)) {
		foreach($params as $key => $val) {
			$param_str .= "&$key=$val";
		}
	} else {
		$param_str .= $params === '' ? '' : ('&' . $params);
	}
	$url .= $param_str === '' ? '' : $param_str;
	
	$curl = @curl_init();
	if($curl === false)
		return -1;
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	if(!empty($headers)) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}

	$result = curl_exec($curl);
	
	if(curl_errno($curl)) {
		$result = curl_errno($curl);
		@curl_close($curl);
		return -2;
	}
	
	$info = curl_getinfo($curl);
	if($info['http_code'] != '200') {
		$result = $info['http_code'];
		@curl_close($curl);
		return -3;
	}
	
	//释放curl句柄
	@curl_close($curl);

	return 1;
}

/**	功能 curl http post请求
 *	@param $url String 请求的url地址
 *	@param $params String/Array http get请求的参数[String形式:a=b&c=d, Array形式:array('a'=>'b','c'=>'d')]
 *	@param &$result Mixed(Array/String/Integer) 请求的结果 或 curl失败的状态码
 *	@param $headers Array 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
 *	@return Integer
 *		1: 请求成功
 *		0: url为空
 *		-1: 初始化curl失败
 *		-2: curl_exec失败
 *		-3: 响应的状态码有误(不等于200)
 */
function curl_http_post($url, $params, &$result, $headers = array()) {
	if($url === '')
		return 0;
	$curl = @curl_init();
	if($curl === false)
		return -1;
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
	if(!empty($headers)) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}

	$result = curl_exec($curl);
	
	if(curl_errno($curl)) {
		$result = curl_errno($curl);
		@curl_close($curl);
		return -2;
	}
	
	$info = curl_getinfo($curl);
	if($info['http_code'] != '200') {
		$result = $info['http_code'];
		@curl_close($curl);
		return -3;
	}
	
	//释放curl句柄
	@curl_close($curl);

	return 1;
}


/**	功能 上传图片文件至云图
 *	@param $filename 图片文件名(全路径) 必须
 *	@param $sign 上传后的图片名 必须
 *	@param $url 上传请求的url地址 可选 缺省使用 http://innerupload01.picupload.djt.sogou-op.org/http_upload
	@return Mixed(Array/Integer)
		Array : 上传完成(不一定成功，需要查看array['status'])
		0 : 文件不存在
		-1 : fopen打开文件失败
		-2 : 初始化curl失败
		-3 : curl_exec失败
		-4 : 返回数据格式不正确(json解码失败)
 */
function upload_cloud($filename, $sign, $url = '') {
	if(empty($url)) {
		$url = 'http://innerupload01.picupload.djt.sogou-op.org/http_upload?appid=100540022';
	}
	if(!@file_exists($filename)){
		return 0;
	}
	if(!$fp = @fopen($filename, 'r')) {
		return -1;
	}
	$fields['f1'] = '@'.$filename;
	$fields['sign_f1'] = $sign;
	$size = @filesize($filename);
	if(!$ch = @curl_init()) {
		return -2;
	}
	curl_setopt($ch, CURLOPT_URL, $url) ;
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_INFILE, $fp);
	curl_setopt($ch, CURLOPT_INFILESIZE, $size);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	$result = curl_exec($ch);
	if(curl_errno($ch)) {
		curl_close($ch);
		fclose($fp);
		return -3;
	}
	curl_close($ch);
	fclose($fp);
	$result = @json_decode($result, true);
	if($result === false || $result === null) {
		return -4;
	}
	return $result;
}

/**	功能 上传文件至kv
 *	@param $k String key值
 *	@param $file String 文件地址(含文件名)
 *	@return Boolean
 */
function uploadKV($k, $file) {
	$remote_url = '/'.KV_APPID.'/'.KV_NAMESPACE.'/'.$k;
	$header = "POST $remote_url HTTP/1.0\r\n";
	$header .= "Host: ".KV_SERVER."\r\n";
	$header .= "Content-type: multipart/form-data\r\n";
	$file = is_file($file) ? file_get_contents($file) : $file;
	//$header .= "Content-type: application/octet-stream\r\n";
	$data = $file."\r\n";
	$header .= "Content-length: " . strlen($data) . "\r\n\r\n";
	//echo $header;
	$fp = fsockopen(KV_SERVER, 80);
	fputs($fp, $header.$data);
	$r = '';
	while (!feof($fp)) {
		$r .= fgets($fp, 128);
	}
	fclose($fp);
	$arr = explode("\r\n", $r);
	$arr = explode(' ', $arr[0]);
	//echo $r."\n";
	if($arr[1] == '200') return true;
	else return false;
}

/**	功能 从kv上取数据
 *	@param $k String key值
 *	@return Boolean(false)/文件内容
 */
function getFromKV($k) {
	$remote_url = '/'.KV_APPID.'/'.KV_NAMESPACE.'/'.$k;
	$fp = fsockopen(KV_SERVER, 80, $errno, $errstr, 30);
	$out = "GET ".$remote_url." HTTP/1.1\r\n";
	$out .= "Host: ".KV_SERVER."\r\n";
	$out .= "Connection: Close\r\n\r\n";
	fwrite($fp, $out);
	$r = '';
	while (!feof($fp)) {
		$r .= fgets($fp, 128);
	}
	fclose($fp);
	$arr = explode("\r\n\r\n", $r);
	$s_arr = explode(" ", $arr[0]);
	$status = $s_arr[1];
	if($status == '200') return substr($arr[1],0,strlen($arr[1])-2);
	else return false;
}

/**	功能 从kv上删数据
 *	@param $k String key值
 *	@return Boolean(false)/文件内容
 */
function delFromKV($k) {
	@exec('curl -X DELETE -v http://'.KV_SERVER.'/'.KV_APPID.'/'.KV_NAMESPACE.'/'.$k, $ret_arr, $ret_var);
	if($ret_var === 0) {
		return true;
	}
	return false;
}
?>