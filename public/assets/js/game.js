var ajax_nav = 0;
var timezone = 0;
var timestamp = 0;

var XNova =
{
	path: '/',
	isMobile: /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent),
	lastUpdate: 0,
	setAjaxNavigation: function ()
	{
		if (!$('#gamediv').length)
			return;

		$.ajaxSetup({data: {isAjax: true}});

		$("body").on('click', 'a[data-link!=Y]', function(e)
		{
			var el = $(this);

			if (el.hasClass('window'))
				return false;

			if (!el.attr('href'))
				return false;

			if (el.attr('href').indexOf('#') === 0)
				return false;

			if (el.attr('href').indexOf('javascript') === 0 || el.attr('href').indexOf('mailto') === 0 || el.attr('href').indexOf('#') >= 0 || el.attr('target') === '_blank')
				return true;
			else
			{
				e.preventDefault();

				load(el.attr('href'));
			}

			return false;
		});

		$('#gamediv form[class!=noajax]').ajaxForm(
		{
			delegation: true,
			dataType: 'json',
			beforeSerialize: function(form)
			{
				$(form).append('<input type="hidden" name="ajax" value="1">');

				showLoading();

				ClearTimers();
				start_time = new Date();
				Djs = start_time.getTime() - start_time.getTimezoneOffset()*60000;
			},
			success: function (data)
			{
				$('#tooltip').hide();
				hideLoading();

				if (data.data.redirect !== undefined)
					window.location.href = data.data.redirect;
				else
					$('#gamediv').html(data.html);

				TextParser.parseAll();
			},
			error: function()
			{
				$('#tooltip').hide();
				hideLoading();

				alert('Что-то пошло не так!? Попробуйте еще раз');
			}
		});

		$('#windowDialog').on('submit', 'form', function(e)
		{
			e.preventDefault();

			showLoading();

			$.ajax({
				url: $(this).attr('action'),
				type: 'post',
				data: $(this).serialize(),
				dataType: 'json',
				beforeSend: function(jqXHR, settings)
				{
					settings.data += (settings.data !== '' ? '&' : '')+'popup=Y&ep=dontsavestate';
	    			return true;
				},
				success: function (data)
				{
					hideLoading();

					if (data.message !== '')
					{
						$.toast({
							text: data.message,
							icon: statusMessages[data.status]
						});
					}
					else if (data.html !== '')
					{
						$('#windowDialog').html(data.html);

						TextParser.parseAll();
					}
				},
				error: function()
				{
					hideLoading();

					alert('Что-то пошло не так!? Попробуйте еще раз');
				}
			})
		});

		$('#tooltip').hide();
	}
};

var statusMessages = {0: 'error', 1: 'success', 2: 'info', 3: 'warning'};

function ShowHiddenBlock (id)
{
    $('#'+id).toggle();
}

var timeouts	= [];
var start_time 	= new Date();

function print_date(timestamp, view)
{
	timestamp = (timestamp + timezone * 1800) * 1000;

    var X = new Date(timestamp);

	if (view == 1) {
		return ((X.getHours()<10?'0':'')+X.getHours()+':'+((m=X.getMinutes())<10?'0':'')+m);
	} else {
		document.write(((d=X.getDate())<10?'0':'')+d+'-'+((mn=X.getMonth()+1)<10?'0':'')+mn+' '+(X.getHours()<10?'0':'')+X.getHours()+':'+((m=X.getMinutes())<10?'0':'')+m+':'+((s=X.getSeconds())<10?'0':'')+s);
		return '';
	}
}

function raport_to_bb(raport)
{
	raport = $('#'+raport);

	var txt = raport.html();

	txt = txt.replace(/<tbody>/gi, "");
	txt = txt.replace(/<\/tbody>/gi, "");
	txt = txt.replace(/<tr>/gi, "[tr]");
	txt = txt.replace(/<\/tr>/gi, "[\/tr]");
	txt = txt.replace(/<td>/gi, "[td]");
	txt = txt.replace(/<\/td>/gi, "[\/td]");
	txt = txt.replace(/<\/table>/gi, "[\/table]");
	txt = txt.replace(/<th>/gi, "[th]");
	txt = txt.replace(/<th width="40%">/gi, "[th(w=40)]");
	txt = txt.replace(/<th width="10%">/gi, "[th(w=10)]");
	txt = txt.replace(/<\/th>/gi, "[\/th]");
	txt = txt.replace(/<td class="c" colspan="4">/gi, "[td(cl=c)(cs=4)]");
	txt = txt.replace(/<td colspan="4" class="c">/gi, "[td(cl=c)(cs=4)]");
	txt = txt.replace(/<table width="100%">/gi, "[table(w=100)]");
	txt = txt.replace(/<table width="100%" cellspacing="1">/gi, "[table(w=100)]");
	txt = txt.replace(/<table cellspacing="1" width="100%">/gi, "[table(w=100)]");
	txt = txt.replace(/<th width="220" align="right">/gi, "[th(w=33)]");
	txt = txt.replace(/<th align="right" width="220">/gi, "[th(w=33)]");
	txt = txt.replace(/<th width="220">/gi, "[th]");
	txt = txt.replace(/<br>/gi, " ");
	txt = txt.replace(/<\/a>/gi, "[\/url]");
	txt = txt.replace(/<a href="(.*?)">/gi, "[url=http://uni5.xnova.su$1]");

	raport.html(txt);
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec)
		{
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};

	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

	if (s[0].length > 3)
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);

	if ((s[1] || '').length < prec)
	{
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}

	return s.join(dec);
}

var Format = {
	number: function(value)
	{
		if (value > 1000000000)
			return number_format(Math.floor(value / 1000000), 0, ',', '.')+'kk';

		return number_format(value, 0, ',', '.');
	}
};

var flotenTimers = [];
var flotenTime = [];

function FlotenTime (obj, time)
{
	if (flotenTimers['fleet'+obj] === undefined)
		flotenTimers['fleet'+obj] = (new Date).getTime();
	if (flotenTime['fleet'+obj] === undefined)
		flotenTime['fleet'+obj] = time;

	if (time === undefined)
	{
		time = flotenTime['fleet'+obj] - Math.floor((((new Date).getTime() - flotenTimers['fleet'+obj]) / 1000));
	}

	var divs    = $('#'+obj);
	var ttime   = time;
	var mfs1    = 0;
	var hfs1    = 0;

	if (ttime < 1)
		divs.html("-");
	else
    {
		if (ttime > 59)
		{
			mfs1 = Math.floor(ttime / 60);
			ttime = ttime - mfs1 * 60;
		}

		if (mfs1 > 59)
		{
			hfs1 = Math.floor(mfs1 / 60);
			mfs1 = mfs1 - hfs1 * 60;
		}

		if (ttime < 10)
			ttime = "0" + ttime;

		if (mfs1 < 10)
			mfs1 = "0" + mfs1;

		divs.html(hfs1 + ":" + mfs1 + ":" + ttime);
	}

	timeouts['fleet'+obj] = setTimeout(function(){FlotenTime(obj)}, 1000);
}

var Djs = start_time.getTime() - start_time.getTimezoneOffset()*60000;

function hms(layr, X)
{
      var d,mn,m,s;

      $("#" + layr).html(((d=X.getDate())<10?'0':'')+d+'.'+((mn=X.getMonth()+1)<10?'0':'')+mn+'.'+X.getFullYear()+' '+X.getHours()+':'+((m=X.getMinutes())<10?'0':'')+m+':'+((s=X.getSeconds())<10?'0':'')+s);
}

function UpdateClock()
{
    //hms('clock', new Date((new Date).getTime() + serverTime));

	//timeouts['clock'] = setTimeout(UpdateClock, 1000);
}

function setMaximum(type, number)
{
	var obj = document.getElementsByName('fmenge['+type+']')[0];

    if (parseInt(obj.value) === 0)
		obj.value = number;
	else
		obj.value = 0;
}

function UpdateGameInfo (mes, ally)
{
	$('.ico_mail + b').html(''+mes+'');
	$('.ico_alliance + b').html(''+ally+'');
}

function QuickFleet (mission, galaxy, system, planet, type, count)
{
	$.ajax({
		type: "GET",
		url: XNova.path+"fleet/quick/",
		data: "mode="+mission+"&g="+galaxy+"&s="+system+"&p="+planet+"&t="+type+"&count="+count,
		dataType: 'json',
		success: function(data)
		{
			$.toast({
			  	text : data.message,
				position : 'bottom-center',
				icon: statusMessages[data.status]
			});
		}
	});
}

function fenster(target_url, win_name, w, h)
{
	if (!w)
		w=850;
	if (!h)
		h=500;

	var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width='+w+',height='+h+',top=0,left=0');
	new_win.focus();
}

function ClearTimers ()
{
	for (var i in timeouts)
	{
		if (timeouts.hasOwnProperty(i))
		{
			clearInterval(timeouts[i]);
			clearTimeout(timeouts[i]);
		}
	}

	flotenTimers = [];
	flotenTime = [];

	timeouts.length = 0;
}

function load (url, disableUrlState)
{
	if (!blockTimer)
		return false;

    ClearTimers();

	blockTimer = false;

    var loc = url.substring(1).split("/");
	var set = loc[0];
	var mod;

	if (typeof disableUrlState === 'undefined')
		disableUrlState = false;
	
	if (loc[1] !== undefined)
		mod = loc[1];

    if (set !== 'buildings')
        currentState = set;
    else
        currentState = set + ((loc[1] !== undefined && loc[1] !== 'ajax=2' && loc[1] !== 'ajax=1') ? '&'+loc[1] : '');

	showLoading();

	$('[role="tooltip"]').remove()

	$.ajax(
	{
		url: url,
		cache: false,
		dataType: 'json',
		success: function(data)
		{
			$('#tooltip').hide();
			hideLoading();
			ClearTimers();

			$('body > .contentBox').attr('class', 'contentBox set_'+set+(mod !== undefined && set === 'buildings' && mod !== undefined ? mod : ''));
			$('body.window .game_content').css('width', '');
			$('.ui-helper-hidden-accessible').html('');

			$('#gamediv').html(data.html);

			if (data.message !== '')
			{
				$.toast({
					text: data.message,
					icon: statusMessages[data.status]
				});
			}

			if (typeof data.data.title_full !== 'undefined')
				document.title = data.data.title_full;

			if (data.data.redirect !== undefined)
				window.location.href = data.data.redirect;

			if (disableUrlState === false && typeof data.data.url !== 'undefined')
				addHistoryState(data.data.url);

			dialog.dialog("close");

			for (var key in data.data)
			{
				if (data.data.hasOwnProperty(key))
					Vue.set(options, key, data.data[key])
			}

			if (data.data.tutorial !== undefined && data.data.tutorial.popup !== '')
			{
				$.confirm({
				    title: 'Обучение',
				    content: data.data.tutorial.popup,
					confirmButton: 'Продолжить',
					cancelButton: false,
					backgroundDismiss: false,
					confirm: function ()
					{
						if (data.data.tutorial.url !== '')
						{
							load(data.data.tutorial.url);
						}
					}
				});
			}

			if (data.data.tutorial !== undefined && data.data.tutorial.toast !== '')
			{
				$.toast({
					text: data.data.tutorial.toast,
					icon: 'info',
					stack : 1
				});
			}

			TextParser.parseAll();
		},
		timeout: 10000,
		error: function()
		{
			$('#tooltip').hide();
			document.location = url;

			console.log('error in '+url);
		}
	});

	start_time      = new Date();
    Djs             = start_time.getTime() - start_time.getTimezoneOffset()*60000;

	return true;
}

function addHistoryState (url)
{
	var supportsHistoryAPI = !!(window.history && history.pushState);

	if (supportsHistoryAPI)
		window.history.pushState({save: 1}, null, url);
}

var tooltipTimer, dialog;
var currentState = window.location.hash.slice(1);

$(document).ready(function()
{
	if ($.isFunction($.reject))
	{
		$.reject({
			reject: {
				msie: 9,
				chrome: 20,
				firefox: 27,
				opera: 11,
				safari: 6,
				unknown: false,
				webkit: 537.1
			},
			display: ['firefox', 'chrome', 'opera'],
			imagePath: ''+XNova.path+'assets/images/',
			header: 'Привет из каменного века!',
			paragraph1: 'Вы вкурсе, что ваш браузер безнадёжно устарел и не поддерживает корректное отображение в данной игре?',
			paragraph2: 'Обновите текущий браузер или установите новый:',
			closeLink: 'Закрыть окно',
			closeMessage: 'Администрация не несет ответственности за отображение игры на несовместимом браузере',
			closeCookie: true
		});
	}

	if ($.isFunction($(document).dialog))
	{
		dialog = $('#windowDialog').dialog({
			autoOpen: false,
			minWidth: 500,
			minHeight: 300,
			maxHeight: 600,
			resizable: false,
			title: 'Сообщение',
			modal: true,
			position: { my: "center", at: "center", of: window },
			close: function()
			{
				$('#windowDialog').html('');
			}
		});
	}

    if (ajax_nav === 1)
    {
		XNova.setAjaxNavigation();

		var supportsHistoryAPI = !!(window.history && history.pushState);

		if (supportsHistoryAPI)
		{
			addHistoryState(location.search);

			window.setTimeout( function()
			{
				$(window).on("popstate", function(e)
				{
					var data = e.originalEvent.state;

					if (data !== null)
					{
						currentState = location.search;
						load(currentState, true);

						e.preventDefault();
					}
				});
			}, 1000);
		}
    }

	if ($.isFunction($(document).tooltip))
	{
		$(document).tooltip({
			items: ".tooltip",
			track: !XNova.isMobile,
			show: false,
			hide: false,
			position: {my: "left+25 top+15", at: "left bottom", collision: "flipfit"},
			content: function ()
			{
				if ($(this).hasClass('script'))
					return eval($(this).data('content'));
				else if (typeof $(this).data('content') === "undefined")
					return $(this).find('.tooltip-content').clone();
				else
					return $(this).data('content');
			},
			open: function(ev, obj)
			{
				if ($(ev.toElement).data('tooltipWidth') !== undefined)
					obj.tooltip.css({width: $(ev.toElement).data('tooltipWidth')});
			},
			close: function()
			{
				$('.ui-helper-hidden-accessible').html('');
			}
		});
	}

	$('body').on('mouseenter', ".tooltip_sticky", function (e)
	{
   		var tip = $('#tooltip');
		var obj = $(this);

		tip.css({width: ''});

		tooltipTimer = setTimeout(function()
		{
			tip.html(obj.data('content')).addClass('tooltip_sticky_div');

			tip.css({
				top : e.pageY - tip.outerHeight() / 2,
				left : e.pageX - tip.outerWidth() / 2
			});

			tip.show();
		}, 400);
   	})
	.on('mouseleave', ".tooltip_sticky", function ()
	{
		clearTimeout(tooltipTimer);
	})
	.on('mouseleave', ".tooltip_sticky_div", function ()
	{
   		var tip = $('#tooltip');

   		tip.removeClass('tooltip_sticky_div').hide();
   	})
	.on('click', '.ui-widget-overlay', function()
	{
		closeWindow();
	})
	.on('click', '.fancybox', function(e)
	{
		if ($.isFunction($(document).fancybox))
		{
			e.preventDefault();

			$.fancybox({
				href: $(this).attr('href'),
				padding: 0,
				openSpeed: 100,
				closeSpeed: 100
			});
		}
	})
	.on('change', 'input.checkAll', function()
	{
		var checked = $(this).is(':checked');

		$(this).closest('form').find('input[type=checkbox]').each(function()
		{
			$(this).prop('checked', checked);
		});
	});
});

function showWindow (title, url, width, height)
{
	if (!XNova.isMobile)
	{
		$('#windowDialog').html('');

		showLoading();

		if (height === undefined)
			height = 'auto';

		$.ajax(
		{
			url: url,
			cache: false,
			data: {ajax: 'Y', 'popup': 'Y'},
			dataType: 'json',
			success: function (json)
			{
				var obj = $('#windowDialog');

				if (title.length > 0)
					obj.dialog("option", "title", title);
				else
					obj.dialog("option", "title", json.data.title);

				if (width !== undefined)
				{
					obj.dialog("option", "minWidth", width);
					obj.dialog("option", "width", width);
				}

				if (height !== undefined)
					obj.dialog("option", "height", height);

				hideLoading();

				obj.html(json.html);
				obj.dialog("option", "position", {my: "center", at: "center", of: window});
				obj.dialog("open");
			}
		});
	}
	else
	{
		window.location.href = url.split('ajax').join('').split('popup').join('');
	}
}

function closeWindow()
{
	$('#windowDialog').dialog('close');
}

function setWindowTitle (title)
{
	$('#windowDialog').dialog( "option", "title", title );
}

var blockTimer = true;

function showLoading ()
{
	$.fancybox.showLoading();

	setTimeout(function()
	{
		blockTimer = true;
	}, 500);
}

function hideLoading ()
{
	$.fancybox.hideLoading();
}

function f(target_url, win_name)
{
	var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0');
	new_win.focus();
}

function date ( format, timestamp )
{
	var a, jsdate = new Date(timestamp ? timestamp * 1000 : null);
	var pad = function(n, c){
		if( (n = n + "").length < c ) {
			return new Array(++c - n.length).join("0") + n;
		} else {
			return n;
		}
	};
	var txt_weekdays = ["Sunday","Monday","Tuesday","Wednesday",
		"Thursday","Friday","Saturday"];
	var txt_ordin = {1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};
	var txt_months =  ["", "January", "February", "March", "April",
		"May", "June", "July", "August", "September", "October", "November",
		"December"];

	var f = {
			d: function(){
				return pad(f.j(), 2);
			},
			D: function(){
				t = f.l(); return t.substr(0,3);
			},
			j: function(){
				return jsdate.getDate();
			},
			l: function(){
				return txt_weekdays[f.w()];
			},
			N: function(){
				return f.w() + 1;
			},
			S: function(){
				return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th';
			},
			w: function(){
				return jsdate.getDay();
			},
			z: function(){
				return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0;
			},
			W: function(){
				var a = f.z(), b = 364 + f.L() - a;
				var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;

				if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){
					return 1;
				} else{

					if(a <= 2 && nd >= 4 && a >= (6 - nd)){
						nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
						return date("W", Math.round(nd2.getTime()/1000));
					} else{
						return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
					}
				}
			},
			F: function(){
				return txt_months[f.n()];
			},
			m: function(){
				return pad(f.n(), 2);
			},
			M: function(){
				t = f.F(); return t.substr(0,3);
			},
			n: function(){
				return jsdate.getMonth() + 1;
			},
			t: function(){
				var n;
				if( (n = jsdate.getMonth() + 1) == 2 ){
					return 28 + f.L();
				} else{
					if( n & 1 && n < 8 || !(n & 1) && n > 7 ){
						return 31;
					} else{
						return 30;
					}
				}
			},
			L: function(){
				var y = f.Y();
				return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0;
			},
			//o not supported yet
			Y: function(){
				return jsdate.getFullYear();
			},
			y: function(){
				return (jsdate.getFullYear() + "").slice(2);
			},
			a: function(){
				return jsdate.getHours() > 11 ? "pm" : "am";
			},
			A: function(){
				return f.a().toUpperCase();
			},
			B: function(){
				// peter paul koch:
				var off = (jsdate.getTimezoneOffset() + 60)*60;
				var theSeconds = (jsdate.getHours() * 3600) +
								 (jsdate.getMinutes() * 60) +
								  jsdate.getSeconds() + off;
				var beat = Math.floor(theSeconds/86.4);
				if (beat > 1000) beat -= 1000;
				if (beat < 0) beat += 1000;
				if ((String(beat)).length == 1) beat = "00"+beat;
				if ((String(beat)).length == 2) beat = "0"+beat;
				return beat;
			},
			g: function(){
				return jsdate.getHours() % 12 || 12;
			},
			G: function(){
				return jsdate.getHours();
			},
			h: function(){
				return pad(f.g(), 2);
			},
			H: function(){
				return pad(jsdate.getHours(), 2);
			},
			i: function(){
				return pad(jsdate.getMinutes(), 2);
			},
			s: function(){
				return pad(jsdate.getSeconds(), 2);
			},

			O: function(){
			   var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4);
			   if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t;
			   return t;
			},
			P: function(){
				var O = f.O();
				return (O.substr(0, 3) + ":" + O.substr(3, 2));
			},
			c: function(){
				return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P();
			},
			U: function(){
				return Math.round(jsdate.getTime()/1000);
			}
	};

	return format.replace(/[\\]?([a-zA-Z])/g, function(t, s){
		if( t!=s ){
			// escaped
			ret = s;
		} else if( f[s] ){
			// a date function exists
			ret = f[s]();
		} else{
			// nothing special
			ret = s;
		}

		return ret;
	});
}
