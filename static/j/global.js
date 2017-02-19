
var currentLocation = window.location.href;

function showLoginBox(rurl) {
	var rurl = typeof rurl == 'undefined' ? '' : rurl;
	if(rurl === '') {
		rurl = window.location.href;
	}
	var url = 'login.php?inajax=1&'+(rurl == '' ? '' : 'rurl='+encodeURIComponent(rurl))+'&_' + new Date().getTime();
	$.fancybox({'type':'ajax','ajax':{'url':url},'padding':'0', 'scrolling':'no','centerOnScroll':true,'overlayOpacity':0.4,'overlayColor':'#000'});
}

function logout() {
	$.ajax({
		type: 'GET',
		url: 'logout.php',
		dataType: 'json',
		data: 'inajax=1',
		success: function(resp) {
			if(resp.status > 0) {
				PassportSC.logoutHandle($id('uinfo'), logoutApp, logoutApp);
			} else {
				alert('\u6ce8\u9500\u5931\u8d25');//×¢ÏúÊ§°Ü
			}
		},
		error: function() {}
	});
}

function logoutApp() {
	top.window.location.href = currentLocation;
}

function _getUser() {
	var user = {uid:0,nickname:''};
	if($.cookie('pprdig') && $.cookie('uid') > 0) {
		user = {
			uid: parseInt($.cookie('uid'),10),
			nickname: $.cookie('nickname')
		};
	}
	return user;
}

jQuery.fn.DefaultValue = function(text){
    return this.each(function(){
		//Make sure we're dealing with text-based form fields
		if(this.type != 'text' && this.type != 'password' && this.type != 'textarea')
			return;
		
		//Store field reference
		var fld_current=this;
		
		//Set value initially if none are specified
        if(text !== undefined) {
			this.value=text;
		} else {
			if(this.defaultValue !== undefined){
				text = this.defaultValue;
			}else{
				text = '';
				return;
			}
			//Other value exists - ignore
		}
		
		//Remove values on focus
		$(this).focus(function() {
			if(this.value==text || this.value=='')
				this.value='';
		});
		
		//Place values back on blur
		$(this).blur(function() {
			if(this.value==text || this.value=='')
				this.value=text;
		});
		
		//Capture parent form submission
		//Remove field values that are still default
		$(this).parents("form").each(function() {
			//Bind parent form submit
			$(this).submit(function() {
				if(fld_current.value==text) {
					fld_current.value='';
				}
			});
		});
    });
};