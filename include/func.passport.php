<?php
/**
 */

function microtime_passport() {
	list($usec, $sec) = explode(" ", microtime());
	return round(((float)$usec + (float)$sec)*10000);
}

function socket_begin($in, $address="127.0.0.1", $service_port=19999)
{
	/* Create a TCP/IP socket. */
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket < 0) {
		exit("socket_create error");
	}
	
	$result = socket_connect($socket, $address, $service_port);
	if ($result < 0) {
		exit("socket_connect() failed");
	}
	
	//stream_set_blocking ($socket, 0); // no blocking 
	socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0)); 

	$str = $out = '';
	socket_write($socket, $in, strlen($in));
	while ($out = socket_read($socket, 2048)) {
		$str .= $out;
	}
	
	socket_close($socket);
	return $str;
}


function php_post($host, $path, $post_str) 
{
	//$post_str = "user=".urlencode("看了")."&name=bsd";
	$in = "POST ".$path." HTTP/1.1\r\n";
	$in .= "Host: ".$host."\r\n";
	$in .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10 ( .NET CLR 3.5.30729)\r\n";
	$in .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
	$in .= "Accept-Language: zh-cn,zh;q=0.5\r\n";
	$in .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
	$in .= "Keep-Alive: 115\r\n";
	$in .= "Connection: keep-alive\r\n";
	$in .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$in .= "Content-Length: ".strlen($post_str)."\r\n";
	$in .= "\r\n";
	$in .= $post_str;
	$out = '';
	
	$service_port = getservbyname('www', 'tcp');
	$address = gethostbyname($host);

	$str = socket_begin($in, $address, $service_port);
	return $str;
}


/**	通过sgid获取passport_id
 *	@param $sgid String
 *	@return String 可能返回空字符串
 */
function get_passport_user($sgid)
{
	//$sgid = $_COOKIE["sgid"];
	$client_id = '1044';  // 2003 
	$key = '=#dW$h%q)6xZB#m#lu\'x]]wP=\FUO7';
	$ct = microtime_passport();
	$code=md5($sgid.$client_id.$key.$ct); 
	$ip   = $_SERVER['REMOTE_ADDR']; 
	
	$post_str = "sgid=".$sgid;
	$post_str .= "&client_id=".$client_id;
	$post_str .= "&user_ip=".$ip;
	$post_str .= "&ct=".$ct;
	$post_str .= "&code=".$code;
	
	$r = php_post("session.account.sogou", "/verify_sid", $post_str);
	if (preg_match("/[\"|\']passport_id[\"|\']\s*:\s*[\"|\'](.*?)[\"|\']/i", $r, $match)) return $match[1];
	else return '';
}

/**	功能 通过userid(passport_id)获取用户信息
 *	@param $userid String 用户的passport_id
 *	@return Integer/Array
		Array : 成功
		0 : curl post请求失败
		-1 : 响应数据为空
		-2 : 响应数据类型不为json格式
		
 */
function get_userinfo($userid, $fields = '') {
	$key = '=#dW$h%q)6xZB#m#lu\'x]]wP=\FUO7';
	$post_arr = array();
	$post_arr['userid'] = $userid;
	$post_arr['client_id'] = 1044;
	$post_arr['openid'] = $userid;
	$post_arr['ct'] = microtime_passport();
	$post_arr['code'] = md5($post_arr['userid'].$post_arr['client_id'].$key.$post_arr['ct']);
	
	$ret = curl_http_post('http://account.sogou/internal/connect/users/info', $post_arr, $data);
	if($ret < 1) {
		return 0;
	}
	$data = trim($data);
	if($data === '') {
		return -1;
	}
	$data = json_decode($data, true);
	if($data === false) {
		return -2;
	}
	return $data;
}

/**
 *	以下为本地用户相关函数
 */

/**	获取当前登录的用户
 *	@return Array
 */
function get_login_user() {
	global $_G;
	if($_G['user']) {
		return $_G['user'];
	}
	$_G['user'] = get_user($_G['uid'], 'uid');
	return $_G['user'];
}

/**	获取本地用户信息
 *	@param $uid Integer/String 用户的uid或openid[来自微信的用户会有openid参数]
 *	@param $type String 类型 缺省'uid' ['uid':通过uid获取,'passportid':通过passportid获取,'ucuid':通过ucuid获取]
 *	@return Array
 */
function get_user($uid, $type = 'uid') {
	global $MDB, $SDB;
	$user = array();
	switch($type) {
		case 'uid':
			$key = 'uid';break;
		case 'passportid':
			$key = 'passportid';break;
		case 'ucuid':
			$key = 'ucuid';break;
		default:
			$key = 'uid';
	}
	if(empty($uid)) {
		return $user;
	}
	$sql = "SELECT * FROM `skindesignali_user` WHERE `{$key}`='{$uid}' LIMIT 1";
	if(!$user = $SDB->FetchArray($SDB->Query($sql))) {
		$user = array();
	}
	return $user;
}

/**	清空登录相关参数
 *	@return Boolean(true)
 */
function clear_cookies() {
	@session_destroy();
	@ssetcookie('uid', '', -1);
	@ssetcookie('nickname', '', -1);
	@ssetcookie('nick', '', -1);
	return true;
}
?>