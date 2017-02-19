<?php

header('content-type:text/html;charset=utf-8');
//通用常量
define('R_ROOT', dirname(__FILE__));
define('R_TMP', R_ROOT.'/tmp');
define('DATA_ROOT', R_ROOT . '/data');
define('CACHE_ROOT', DATA_ROOT . '/cache');
define('TPL_FILEEXT', 'html');//tpl suffix
define('PRODUCT_MODEL', product_model()?1:0);//产品模式[1:产品模式,0:开发模式]
define('TABLE_PREFIX', 'skindesignjxh2017_');//table prefix
define('SITE_AUTHKEY', 'sogoushouji~!@');//站点加密串
define('COOKIE_DOMAIN', '.shouji.sogou.com');
define('TIMESTAMP', time());//入口时间戳
define('CLOUD_URL', 'http://img.sogoucdn.com/app/a/100540022/');//云图url,一定要以/结尾
define('PC_PIC','http://dl.pinyin.sogou.com/cache/skins/');//pc 皮肤图片cdn地址前缀
define('PC_DL','http://download.pinyin.sogou.com/skins/download.php?skin_id=');//pc皮肤下载地址
define('WAPDL_URL', 'http://img.shouji.sogou.com/wapdl/');//wapdl url,一定要以/结尾
define('KV_APPID', '110166');//KV的appid
define('KV_NAMESPACE', 'shoujisapp');//KV的namespace
define('KV_SERVER', 'kv.sogou');
define('IN_APP', !0);//
define('INTERFACE_KEY', 'UICHINA2016!@#');
define('DISPLAY',true);//显示获奖作品开关
define('PHP7',substr(PHP_VERSION,0,1)==7 ? TRUE : FALSE );

date_default_timezone_set('PRC');
if(PHP_VERSION < '5.3.0'){
	set_magic_quotes_runtime(0);//later 5.3.0, discard
}

if(PRODUCT_MODEL){
	error_reporting(0);
	ini_set('display_errors', 'Off');
} else {
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR);
	ini_set('display_errors', 'On');
}
ob_start();
@session_start();

define('R_DATE_START', strtotime('2017-02-18 10:00:00'));//活动开始时间
define('R_DATE_END', strtotime('2017-05-10 23:59:59'));//活动结束时间
define('R_TODY_START', strtotime(date('Y-m-d')));//今日开始时间戳
define('R_TODY_OFFSET', TIMESTAMP-R_TODY_START);//当前时间戳与今日开始时间戳的差量(即时间偏移量)

require R_ROOT.'/include/inc.constants.php';
require R_ROOT.'/include/func.common.php';
require R_ROOT.'/include/func.passport.php';
if(PHP7){
	include R_ROOT.'/include/class.mysql7.php';
}else{
	include R_ROOT.'/include/class.mysql.php';

}

global $_G;
$_G['uid'] = 0;
$_G['nickname'] = '';
$_G['user'] = array();
$_G['ips'] = get_onlineip();
$_G['onlineip'] =  empty($_G['ips']) || strpos($_G['ips'], ',') === false ? $_G['ips'] : substr($_G['ips'], 0, strpos($_G['ips'], ','));
$_G['cookies'] = $_COOKIE;

//DB
$MDB = new nMysql('sogou_shoujiwap', array('charset'=>'utf8'));
$SDB = &$MDB;

/**	是否产品模式
 *	@param void
 *	@return Boolean
 */
function product_model() {
	if($_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '10.129.157.65' || $_SERVER['HTTP_HOST'] == '10.129.157.24') {
		return false;
	}
	return true;
}
?>
