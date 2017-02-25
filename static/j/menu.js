/**
 *	Id menu.js
 */

var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];

JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':200,'menu':300,'dialog':400,'prompt':500};
JSMENU['float'] = '';

var EXTRAFUNC = [], EXTRASTR = '';
EXTRAFUNC['showmenu'] = [];

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = $id(ctrlid);
	var menuObj = $id(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var xoffset = isUndefined(v['xoffset']) ? 0 : parseInt(v['xoffset']);
	var yoffset = isUndefined(v['yoffset']) ? 0 : parseInt(v['yoffset']);
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 600 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && $id(drag) ? $id(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var coverclick = isUndefined(v['coverclick']) ? 1 : v['coverclick'];
	var beforefunc = isUndefined(v['beforefunc']) ? null : v['beforefunc'];
	var afterfunc = isUndefined(v['afterfunc']) ? null : v['afterfunc'];
	var opacity = isUndefined(v['opacity']) ? 0.4 : v['opacity'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	var ctrlclass = isUndefined(v['ctrlclass']) ? '' : v['ctrlclass'];
	var winhandlekey = isUndefined(v['win']) ? '' : v['win'];
	zindex = cover ? zindex + 500 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	for(i in EXTRAFUNC['showmenu']) {
		try {
			eval(EXTRAFUNC['showmenu'][i] + '()');
		} catch(e) {}
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype, beforefunc, afterfunc);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype, beforefunc, afterfunc);
	}

	if(ctrlObj) {
		if(!ctrlObj.getAttribute('initialized')) {
			ctrlObj.setAttribute('initialized', true);
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype, beforefunc, afterfunc);
					}, timeout);
				}
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	if(!menuObj.getAttribute('initialized')) {
		menuObj.setAttribute('initialized', true);
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			return doane(e, 0, 1);
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype, beforefunc, afterfunc);
					}, timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = document.body.scrollWidth + 'px';
			coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity='+opacity*100+')';
			coverObj.style.opacity = opacity;
			if(coverclick > 0){
				if(coverclick == 1){
					coverObj.onclick = function(){hideMenu(menuid, mtype, beforefunc, afterfunc)};
				}else{
					coverObj.ondblclick = function(){hideMenu(menuid, mtype, beforefunc, afterfunc)};
				}
			}
			$id('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
			_attachEvent(window, 'resize', function () {
				coverObj.style.width = document.body.scrollWidth + 'px';
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
			_attachEvent(window, 'scroll', function () {
				coverObj.style.width = document.body.scrollWidth + 'px';
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}

	if(cover) $id(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O > opacity) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 10;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 40);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	menuObj.style.display = '';
	if(ctrlObj && ctrlclass) {
		ctrlObj.className += ' ' + ctrlclass;
		menuObj.setAttribute('ctrlid', ctrlid);
		menuObj.setAttribute('ctrlclass', ctrlclass);
	}
	if(pos != '*') {
		var setPos = function(){setMenuPosition(showid, menuid, pos, xoffset, yoffset)};
		setPos();
		_attachEvent(window, 'resize', setPos);
	}
	if(BROWSER.ie && BROWSER.ie < 7 && winhandlekey && $id('fwin_' + winhandlekey)) {
		$id(menuid).style.left = (parseInt($id(menuid).style.left) - parseInt($id('fwin_' + winhandlekey).style.left)) + 'px';
		$id(menuid).style.top = (parseInt($id(menuid).style.top) - parseInt($id('fwin_' + winhandlekey).style.top)) + 'px';
	}
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\', \'' + beforefunc + '\' ,\'' + afterfunc + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
}

var delayShowST = null;
function delayShow(ctrlObj, call, time) {
	if(typeof ctrlObj == 'object') {
		var ctrlid = ctrlObj.id;
		call = call || function () { showMenu(ctrlid); };
	}
	var time = isUndefined(time) ? 500 : time;
	delayShowST = setTimeout(function () {
		if(typeof call == 'function') {
			call();
		} else {
			eval(call);
		}
	}, time);
	if(!ctrlObj.delayinit) {
		_attachEvent(ctrlObj, 'mouseout', function() {clearTimeout(delayShowST);});
		ctrlObj.delayinit = 1;
	}
}

var dragMenuDisabled = false;
function dragMenu(menuObj, e, op) {
	e = e ? e : window.event;
	if(op == 1) {
		if(dragMenuDisabled || in_array(e.target ? e.target.tagName : e.srcElement.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
			return;
		}
		JSMENU['drag'] = [e.clientX, e.clientY];
		JSMENU['drag'][2] = parseInt(menuObj.style.left);
		JSMENU['drag'][3] = parseInt(menuObj.style.top);
		document.onmousemove = function(e) {try{dragMenu(menuObj, e, 2);}catch(err){}};
		document.onmouseup = function(e) {try{dragMenu(menuObj, e, 3);}catch(err){}};
		doane(e);
	}else if(op == 2 && JSMENU['drag'][0]) {
		var menudragnow = [e.clientX, e.clientY];
		menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
		menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
		doane(e);
	}else if(op == 3) {
		JSMENU['drag'] = [];
		document.onmousemove = null;
		document.onmouseup = null;
	}
}

function setMenuPosition(showid, menuid, pos, xoffset, yoffset) {
	var showObj = $id(showid);
	var menuObj = menuid ? $id(menuid) : $id(showid + '_menu');
	if(isUndefined(pos) || !pos) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var important = pos.indexOf('!') != -1 ? 1 : 0;
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0;
	var ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0;
	var bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = fetchOffset(showObj);
		sx = sxy['left'];
		sy = sxy['top'];
		sw = showObj.offsetWidth;
		sh = showObj.offsetHeight;
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:	
			bpl = sx;
			bpt = sy;
			break;
		case 2:	
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:	
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:	
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:	
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			/* mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2 - document.documentElement.clientHeight/40; */
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			/* mt = mt <= 0 ? (mt + document.documentElement.clientHeight/40) : mt; */
			mt = mt <= 0 ? 0 : mt;
			break;
		case 1:	
			ml = bpl - mw;	
			mt = bpt - mh;
			break;
		case 2:	
			ml = bpl;	
			mt = bpt - mh;
			break;
		case 3:	
			ml = bpl;
			mt = bpt;
			break;
		case 4:	
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(!important) {
		if(in_array(direction, [1, 4]) && ml < 0) {
			ml = bpl;
			if(in_array(basePoint, [1, 4])) ml += sw;
		} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
			ml = bpl - mw;
			if(in_array(basePoint, [2, 3])) {
				ml -= sw;
			} else if(basePoint == 4) {
				ml += sw;
			}
		}
		if(in_array(direction, [1, 2]) && mt < 0) {
			mt = bpt;
			if(in_array(basePoint, [1, 2])) mt += sh;
		} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
			mt = bpt - mh;
			if(in_array(basePoint, [3, 4])) mt -= sh;
		}
	}
	if(pos.substr(0, 3) == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	
	/* offset trim */
	if(direction != 0) {
		ml += xoffset;
		mt += yoffset;
	}
	if(ml) menuObj.style.left = ml + 'px';
	menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function hideMenu(attr, mtype, beforefunc, afterfunc) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype, beforefunc, afterfunc);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype, beforefunc, afterfunc);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = $id(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		var ctrlObj = '', ctrlclass = '';
		if((ctrlObj = $id(menuObj.getAttribute('ctrlid'))) && (ctrlclass = menuObj.getAttribute('ctrlclass'))) {
			var reg = new RegExp(' ' + ctrlclass);
			ctrlObj.className = ctrlObj.className.replace(reg, '');
		}
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(beforefunc) {
				try{beforefunc()}catch(e){}
			}
			if(menuObj.cache) {
				if(menuObj.style.visibility != 'hidden') {
					menuObj.style.display = 'none';
					if(menuObj.cover) $id(attr + '_cover').style.display = 'none';
				}
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) $id(attr + '_cover').parentNode.removeChild($id(attr + '_cover'));
			}
			if(afterfunc) {
				try{afterfunc()}catch(e){}
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 20;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 40);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}

function getCurrentStyle(obj, cssproperty, csspropertyNS) {
	if(obj.style[cssproperty]){
		return obj.style[cssproperty];
	}
	if (obj.currentStyle) {
		return obj.currentStyle[cssproperty];
	} else if (document.defaultView.getComputedStyle(obj, null)) {
		var currentStyle = document.defaultView.getComputedStyle(obj, null);
		var value = currentStyle.getPropertyValue(csspropertyNS);
		if(!value){
			value = currentStyle[cssproperty];
		}
		return value;
	} else if (window.getComputedStyle) {
		var currentStyle = window.getComputedStyle(obj, "");
		return currentStyle.getPropertyValue(csspropertyNS);
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			position = getCurrentStyle(obj, 'position', 'position');
			if(position == 'relative') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}

function hideWindow(k, all, clear) {
	all = isUndefined(all) ? 0 : all;
	clear = isUndefined(clear) ? 1 : clear;
	hideMenu('fwin_' + k, 'win');
	if(clear && $id('fwin_' + k)) {
		$id('append_parent').removeChild($id('fwin_' + k));
	}
	if(all) {
		hideMenu();
	}
}

/* added */
function window_open(iframeid, url, param) {
	var iframeid = isUndefined(iframeid) ? '' : iframeid;
	var url = isUndefined(url) ? '' : url;
	var param = isUndefined(param) ? {} : param;
	var drag = null;
	var menuid = 'fwin_' + iframeid;
	var menuObj = $id(menuid);
	var hidedom = '';

	var initMenu = function() {
		var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', iframeid);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				drag = objs[i].id;
				fctrlidinit = true;
			}
		}
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['win'],'drag':(drag == null ? '' : drag),'cache':0,'cover':1,'coverclick':2};
		for(k in param) {
			v[k] = param[k];
		}
		showMenu(v);
	};
	if(!param['cache'] && menuObj){
		hideMenu(menuObj.id);
	}
	if(!menuObj) {
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = 'fwinmask';
		menuObj.style.display = 'none';
		$id('append_parent').appendChild(menuObj);
		evt = ' style="cursor:move" onmousedown="dragMenu($id(\'' + menuid + '\'), event, 1)" ondblclick="hideWindow(\'' + iframeid + '\')"';
		if(!BROWSER.ie) {
			hidedom = '<style type="text/css">object{visibility:hidden;}</style>';
		}
		var flb = '<h3 class="flb" id="fctrl_' + iframeid + '"></h3>';
		menuObj.innerHTML = '<div class="menu_wrap">' + hidedom + '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"' + evt + '></td><td class="t_r"></td></tr><tr><td class="m_l"' + evt + ')">&nbsp;&nbsp;</td><td class="m_c" id="fwin_content_' + iframeid + '" style="position:relative;">'
			+ '</td><td class="m_r"' + evt + '"></td></tr><tr><td class="b_l"></td><td class="b_c"' + evt + '></td><td class="b_r"></td></tr></table><a href="javascript:;" class="menu_close" title="关闭" onclick="hideWindow(\'' + iframeid + '\')"></a></div>';
		if(!$id(iframeid)) {
			var iframe = '<iframe id="' + iframeid + '" src="' + url + '" scrolling="no" frameborder="0"';
			if(param['width']) iframe += ' width = "' + param['width'] + '"';
			if(param['height']) iframe += ' height="' + param['height'] + '"';
			iframe += '/>';
			$id('fwin_content_' + iframeid).innerHTML = flb + iframe;
		} else {
			if($id(iframeid).src != url) {
				$id(iframeid).src = url;
				if(param['width']) {
					$id(iframeid).width = param['width'] + 'px';
				}
				if(param['height']) {
					$id(iframeid).height = param['height'] + 'px';
				}
			}
			$id('fwin_content_' + iframeid).innerHTML = flb;
			$id('fwin_content_' + iframeid).appendChild($id(iframeid));
		}
		initMenu();
		show();
	}
	doane();
}

var showNoticeST = null;
function showNotice(msg, closetime) {
	var msg = isUndefined(msg) ? '' : msg;
	var closetime = isUndefined(closetime) ? 0 : parseFloat(closetime);
	closetime = isNaN(closetime) ? 0 : closetime;
	var menuid = 'win_notice';
	if($id(menuid)) {
		if(showNoticeST) {
			try{clearTimeout(showNoticeST);}catch(e){}
		}
		hideMenu(menuid, 'dialog');
	}
	var menuObj = document.createElement('div');
	menuObj.className = 'dialog_notice';
	menuObj.id = menuid;
	menuObj.innerHTML = '<div class="ncont"><p>' + msg + '</p></div>';
	if(!$id('append_parent'))
		return;
	$id('append_parent').appendChild(menuObj);
	showMenu({'mtype':'dialog','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'cache':0,'cover':1});
	if(closetime > 0) {
		if(showNoticeST) 
			try{clearTimeout(showNoticeST);}catch(e){}
		showNoticeST = setTimeout(function(){
			hideMenu(menuid, 'dialog')}, closetime*1000);
	}
}

/* prompt */
function showPrompt(title, item, okFunc, canFunc) {
	var title = isUndefined(title) ? '\u8bf7\u8f93\u5165' : title;
	var item = isUndefined(item) ? '\u6761\u76ee\u503c' : item;
	var okFunc = isUndefined(okFunc) ? '' : okFunc;
	var canFunc = isUndefined(canFunc) ? '' : canFunc;
	
	var menuid = 'win_prompt';
	var mtype = 'prompt';
	
	if(!$id('append_parent') || $id(menuid))
		return;
	
	var prompt = document.createElement('div');
	prompt.id = menuid;
	prompt.className = 'dialog dialog_prompt';
	var innerHtml = '';
	innerHtml += '<div class="dialog_top">';
	innerHtml += '<span class="dialog_title">' + title + '</span>';
	innerHtml += '<a class="dialog_close" title="\u5173\u95ed"><span>x</span></a>';
	innerHtml += '</div>';
	innerHtml += '<div class="dialog_cont"><span class="dialog_item">' + item + '\uff1a</span><input type="text" class="dialog_txt" name="dialog_txt" value=""/></div>';
	innerHtml += '<div class="dialog_bot cl"><div class="dialog_btn">';
	innerHtml += '<button type="button" class="btn_ok"><span>\u786e\u5b9a</span></button>';
	innerHtml += '<button type="button" class="btn_cancel"><span>\u53d6\u6d88</span></button>';
	innerHtml += '</div></div>';
	prompt.innerHTML = innerHtml;
	
	$id('append_parent').appendChild(prompt);
	var dialog_txt = $C('dialog_txt', prompt, 'input')[0];
	
	/* bind func */
	var dialog_close = $C('dialog_close', prompt, 'a')[0];
	if(dialog_close) {
		_attachEvent(dialog_close, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof canFunc == 'function') {
				canFunc();
			} else {
				try{eval(canFunc)}
				catch(e){}
			}
		});
	}
	var btn_ok = $C('btn_ok', prompt, 'button')[0];
	if(btn_ok) {
		_attachEvent(btn_ok, 'click', function(){
			var okFunc_ret = false;
			if(typeof okFunc == 'function') {
				okFunc_ret = okFunc();
			} else {
				try{okFunc_ret = eval(okFunc)}
				catch(e){okFunc_ret = true}
			}
			if(okFunc_ret)
				hideMenu(menuid, mtype);
		})
	}
	var btn_cancel = $C('btn_cancel', prompt, 'button')[0];
	if(btn_cancel) {
		_attachEvent(btn_cancel, 'click', function(){
			if(typeof canFunc == 'function') {
				canFunc();
			} else {
				try{eval(canFunc)}
				catch(e){}
			}
			hideMenu(menuid, mtype);
		})
	}
	
	showMenu({'menuid':menuid,'mtype':mtype,'pos':'00','duration':3,'cover':1,'coverclick':0,'cache':0});
	if(dialog_txt) {
		dialog_txt.focus();
	}
}

/* confirm */
function showConfirm(msg, okFunc, canFunc, title) {
	var msg = isUndefined(msg) ? '' : msg;
	var okFunc = isUndefined(okFunc) ? '' : okFunc;
	var canFunc = isUndefined(canFunc) ? '' : canFunc;
	var title = isUndefined(title) ? '\u63d0\u793a\u6d88\u606f' : title;
	
	var menuid = 'win_confirm';
	var mtype = 'dialog';
	
	if(!$id('append_parent') || $id(menuid))
		return;
	
	var cfrm = document.createElement('div');
	cfrm.id = menuid;
	cfrm.className = 'dialog dialog_confirm';
	var innerHtml = '';
	innerHtml += '<div class="dialog_top">';
	innerHtml += '<span class="dialog_title">' + title + '</span>';
	innerHtml += '<a class="dialog_close" title="\u5173\u95ed"><span>x</span></a>';
	innerHtml += '</div>';
	innerHtml += '<div class="dialog_cont"><p>' + msg + '</p></div>';
	innerHtml += '<div class="dialog_bot cl"><div class="dialog_btn">';
	innerHtml += '<button type="button" class="btn_ok"><span>\u786e\u5b9a</span></button>';
	innerHtml += '<button type="button" class="btn_cancel"><span>\u53d6\u6d88</span></button>';
	innerHtml += '</div></div>';
	cfrm.innerHTML = innerHtml;
	
	$id('append_parent').appendChild(cfrm);
	
	/* bind func */
	var dialog_close = $C('dialog_close', cfrm, 'a')[0];
	if(dialog_close) {
		_attachEvent(dialog_close, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof canFunc == 'function') {
				canFunc();
			} else {
				try{eval(canFunc)}
				catch(e){}
			}
		});
	}
	var btn_ok = $C('btn_ok', cfrm, 'button')[0];
	if(btn_ok) {
		_attachEvent(btn_ok, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof okFunc == 'function') {
				okFunc();
			} else {
				try{eval(okFunc)}
				catch(e){}
			}
		})
	}
	var btn_cancel = $C('btn_cancel', cfrm, 'button')[0];
	if(btn_cancel) {
		_attachEvent(btn_cancel, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof canFunc == 'function') {
				canFunc();
			} else {
				try{eval(canFunc)}
				catch(e){}
			}
		})
	}
	
	showMenu({'menuid':menuid,'mtype':mtype,'pos':'00','duration':3,'cover':1,'coverclick':0,'cache':0});
}

/* alert */
var showAlertST = null;
function showAlert(msg, timeout, callback, title) {
	var msg = isUndefined(msg) ? '' : msg;
	var timeout = isUndefined(timeout) ? 0 : parseFloat(timeout);
	timeout = isNaN(timeout) ? 0 : timeout;
	var title = isUndefined(title) ? '\u63d0\u793a\u6d88\u606f' : title;
	
	var menuid = 'win_alert';
	var mtype = 'dialog';
	
	if(!$id('append_parent') || $id(menuid))
		return;
	
	var alert = document.createElement('div');
	alert.id = menuid;
	alert.className = 'dialog dialog_alert';
	var innerHtml = '';
	innerHtml += '<div class="dialog_top">';
	innerHtml += '<span class="dialog_title">' + title + '</span>';
	innerHtml += '<a class="dialog_close" title="\u5173\u95ed"><span>x</span></a>';
	innerHtml += '</div>';
	innerHtml += '<div class="dialog_cont"><p>' + msg + '</p></div>';
	innerHtml += '<div class="dialog_bot cl"><div class="dialog_btn">';
	innerHtml += '<button type="button" class="btn_ok"><span>\u786e\u5b9a</span></button>';
	innerHtml += '</div></div>';
	alert.innerHTML = innerHtml;
	
	$id('append_parent').appendChild(alert);
	
	/* bind func */
	var dialog_close = $C('dialog_close', alert, 'a')[0];
	if(dialog_close) {
		_attachEvent(dialog_close, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof callback == 'function') {
				callback();
			} else {
				try{eval(callback)}
				catch(e){}
			}
			try{clearTimeout(showAlertST)}catch(e){}
		});
	}
	var btn_ok = $C('btn_ok', alert, 'button')[0];
	if(btn_ok) {
		_attachEvent(btn_ok, 'click', function(){
			hideMenu(menuid, mtype);
			if(typeof callback == 'function') {
				callback();
			} else {
				try{eval(callback)}
				catch(e){}
			}
			try{clearTimeout(showAlertST)}catch(e){}
		})
	}
	
	showMenu({'menuid':menuid,'mtype':mtype,'pos':'00','duration':3,'cover':1,'coverclick':0,'cache':0});
	
	if(timeout > 0) {
		showAlertST = setTimeout(function(){
			hideMenu(menuid, mtype);
			if(typeof callback == 'function') {
				callback();
			} else {
				try{eval(callback)}catch(e){}
			}
		}, timeout*1000);
	}
}