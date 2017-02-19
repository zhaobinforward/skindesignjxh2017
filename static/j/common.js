/**
 *	Id commom.js
 */

var CHARSET = typeof CHARSET == 'undefined' ? '' : CHARSET;
var SITEURL = typeof SITEURL == 'undefined' ? '' : SITEURL;
var IMGDIR = typeof IMGDIR == 'undefined' ? 'static/i' : IMGDIR;
var JSPATH = typeof JSPATH == 'undefined' ? 'static/j/' : JSPATH;
var CSSPATH = typeof CSSPATH == 'undefined' ? 'static/c/' : CSSPATH;
var VERHASH = typeof VERHASH == 'undefined' ? '1.0' : VERHASH;
var USERAGENT = navigator.userAgent.toLowerCase();

var CSSLOADED = [];/* css动态载入标识数组 */
var JSLOADED = [];/* javascript动态载入标识数组 */
var evalscripts = [];/* js相关 */
var CLIPBOARDSWFDATA = '';/* 剪切板相关 */
var BROWSER = {};

browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser'});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
	HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}

if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
		var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

function $id(id) {
	return document.getElementById(id) ? document.getElementById(id) : null;
}

function $C(classname, ele, tag) {
	var returns = [];
	var ele = isUndefined(ele) ? '' : ele;
	ele = typeof ele == 'object' ? ele : (ele !== '' ? ($id(ele) ? $id(ele) : null) : document);
	if(!ele)
		return returns;
	tag = tag || '*';
	if(ele.getElementsByClassName) {
		var eles = ele.getElementsByClassName(classname);
		if(tag != '*') {
			for (var i = 0, L = eles.length; i < L; i++) {
				if (eles[i].tagName.toLowerCase() == tag.toLowerCase()) {
					returns.push(eles[i]);
				}
			}
		} else {
			returns = eles;
		}
	} else {
		eles = ele.getElementsByTagName(tag);
		var pattern = new RegExp("(^|\\s)"+classname+"(\\s|$)");
		for (i = 0, L = eles.length; i < L; i++) {
			if (pattern.test(eles[i].className)) {
				returns.push(eles[i]);
			}
		}
	}
	return returns;
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function _detachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.removeEventListener) {
		obj.removeEventListener(evt, func, false);
	} else if(eventobj.detachEvent) {
		obj.detachEvent('on' + evt, func);
	}
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function isUndefined(val) {
	return typeof val == 'undefined' ? true : false;
}

/**	功能 获取url参数值
 *	@param arg String 要获取的参数名
 *	@param url String url地址 可选,缺省为当前页面的地址
 *	@return String 要获取的参数值 参数不存在则为""
 */
function getUrlArg(arg, url){
	var arg = isUndefined(arg) ? '' : arg;
	var url = isUndefined(url) || url === '' ? document.location.href : url;
	if(url.indexOf('?') == -1 || arg == '')
		return '';
	url = url.substr(url.indexOf('?')+1);
	var expr = new RegExp('(\\w+)=(\\w+)','ig');
	var args = [];
	while((tmp = expr.exec(url)) != null){
		args[tmp[1]] = tmp[2];
	}
	return isUndefined(args[arg]) ? '' : args[arg];
}

function in_array(needle, haystack){
	if(typeof haystack == 'undefined')return false;
	if(typeof needle == 'string' || typeof needle == 'number'){
		for(var i in haystack){
			if(haystack[i] == needle){
				return true;
			}
		}
	}
	return false;
}

function trim(str) {
	return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function mb_strlen(str) {
	var len = 0;
	var charset = document.charset ? document.charset : (document.characterSet ? document.characterSet : CHARSET);
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = !dot ? '...' : '';
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}

function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function htmlspecialchars(str) {
	return preg_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], str);
}

function display(id) {
	var obj = $id(id);
	if(obj.style.visibility) {
		obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
	} else {
		obj.style.display = obj.style.display == '' ? 'none' : '';
	}
}

function setHomepage(sURL) {
	try{
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(sURL);
	}catch(e){
		alert("\u975e%20ie%20\u6d4f\u89c8\u5668\u8bf7\u624b\u52a8\u5c06\u672c\u7ad9\u8bbe\u4e3a\u4e3b\u9875");return false;
	}
}

function addFavorite(url,title){
	try{
		window.external.addFavorite(url,title);
	}catch(e){
		try{
			window.sidebar.addPanel(title,url,'');
		}catch(e){
			alert("\u8bf7\u6309%20ctrl+d%20\u952e\u6dfb\u52a0\u5230\u6536\u85cf\u5939");
		}
	}
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /^\w+\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	if(url.indexOf('/')==0){
		url = url.substr(1);
	}
	if(!url.match(/^https?:\/\//)) url = SITEURL + url;
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function isLoaded(callback) {
	var callback = typeof callback == 'undefined' ? '' : callback;
	if(window.document.readyState == 'complete') {
		try{eval('callback()')} catch(e) {}
		return true;
	}
	setTimeout('isLoaded('+callback+')', 700);
}

function doane(event, preventDefault, stopPropagation) {
	var preventDefault = isUndefined(preventDefault) ? 1 : preventDefault;
	var stopPropagation = isUndefined(stopPropagation) ? 1 : stopPropagation;
	e = event ? event : window.event;
	if(!e) {
		e = getEvent();
	}
	if(!e) {
		return null;
	}
	if(preventDefault) {
		if(e.preventDefault) {
			e.preventDefault();
		} else {
			e.returnValue = false;
		}
	}
	if(stopPropagation) {
		if(e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	return e;
}

if(BROWSER.ie) {
	document.documentElement.addBehavior("#default#userdata");
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], '', arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}


/**  add javascript
 *  @param src String
 *  @param text String
 *	@param callback function
 *  @param reload Int 0/1
 *  @param targetid String possible value{htmlhead,htmlbody,...}
 *  @param charset String
 *  @return void
 */
function appendscript(src, text, callback, reload, targetid, charset) {
	var src = isUndefined(src) ? '' : src;
	var text = isUndefined(text) ? '' : text;
	var callback = isUndefined(callback) ? '' : callback;
	var targetid = (isUndefined(targetid) || targetid == '' || targetid == null) ? 'htmlhead' : targetid;
	var reload = isUndefined(reload) ? 0 : (parseInt(reload) == 1 ? 1 : 0);
	var charset = isUndefined(charset) ? '' : charset;
	var id = hash(src + text);
	if(!src && !text) return;
	if(targetid != 'htmlhead' && targetid != 'htmlbody' && !$id(targetid)) return;
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $id(id)) {
		$id(id).parentNode.removeChild($id(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : '';
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
				if(callback)
					try{eval('callback()')} catch(e) {}
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
					if(callback)
						try{eval('callback()')} catch(e) {}
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		if(targetid == 'htmlhead') {
			document.getElementsByTagName('head')[0].appendChild(scriptNode);
		} else if(targetid == 'htmlbody') {
			document.getElementsByTagName('body')[0].appendChild(scriptNode);
		} else {
			$id(targetid).appendChild(scriptNode);
		}
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

/* 动态载入css */
/* 变量STYLEID */
function loadcss(cssname) {
	if(!CSSLOADED[cssname]) {
		if(!$id('css_' + cssname)) {
			css = document.createElement('link');
			css.id = 'css_' + cssname,
			css.type = 'text/css';
			css.rel = 'stylesheet';
			css.href = CSSPATH + cssname + '.css?' + VERHASH;
			var headNode = document.getElementsByTagName("head")[0];
			headNode.appendChild(css);
		} else {
			$id('css_' + cssname).href = CSSPATH + cssname + '.css?' + VERHASH;
		}
		CSSLOADED[cssname] = 1;
	}
}

var waitingtimer=[];
function showwaiting(showid, showmsg, wordflag, space, timeout, wordlength) {
	if(!$id(showid)) return false;
	showmsg = isUndefined(showmsg) ? '\u8bf7\u7a0d\u7b49' : showmsg;
	wordflag = isUndefined(wordflag) ? '.' : wordflag;
	space = isUndefined(space) ? '&nbsp;' : space;
	wordlength = isUndefined(wordlength) ? 3 : parseInt(wordlength);
	timeout = isUndefined(timeout) ? 500 : parseInt(timeout);
	var show = function(count){
		var count = isUndefined(count) ? 0 : parseInt(count);
		var flag = '';
		flag += new Array(count+1).join(wordflag) + new Array(wordlength-count+1).join(space);
		var showword = showmsg + flag;
		if($id(showid))
			$id(showid).innerHTML = showword;
		count++;
		if(count>wordlength) count = 0;
		waitingtimer[showid] = setTimeout(function(){try{clearwaiting(showid)}catch(e){}show(count)},timeout);
	}
	show();
}
function clearwaiting(showid) {
	if(isUndefined(showid)) {
		for(var i in waitingtimer){
			try{clearTimeout(waitingtimer[i]);}catch(e){}
		}
	} else {
		try{clearTimeout(waitingtimer[showid]);}catch(e){}
	}
	if($id(showid)) $id(showid).innerHTML = '';
}

function isWeiXin(){
	if(window.navigator.userAgent.toLowerCase().match(/MicroMessenger/i) == 'micromessenger') {
		return true;
	}
	return false;
}

function isQQ() {
	return /qq\s*\//i.test(window.navigator.userAgent);
}

function isIOS() {
	return (/(iphone|ipad|ios)/i).test(window.navigator.userAgent);
}

function isAndroid() {
	return /android[\/\s]+([\d\.]+)/i.test(window.navigator.userAgent)
}