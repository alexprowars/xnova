
var XNova =
{
	path: '/',
	gameSpeed: 1,
	fleetSpeed: 1,
	resSpeed: 1,
	isMobile: /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent),
	format: function (zahl)
	{
		var zahl_tmp1;
		var zahl_tmp2;
		var zahl_tmp3;
		var html = "";

		if (zahl >= 1000000)
		{
			zahl_tmp1 = Math.floor(zahl / 1000000);
			html += "" + zahl_tmp1 + ".";
			zahl_tmp2 = Math.floor((zahl - (zahl_tmp1 * 1000000)) / 1000) + "";

			if (zahl_tmp2.length == 1)
				html += "00" + zahl_tmp2 + ".";
			else if (zahl_tmp2.length == 2)
				html += "0" + zahl_tmp2 + ".";
			else
				html += "" + zahl_tmp2 + ".";

			zahl_tmp3 = Math.floor(zahl - (zahl_tmp1 * 1000000) - (zahl_tmp2 * 1000)) + "";

			if (zahl_tmp3.length == 1)
				html += "00" + zahl_tmp3 + "";
			else if (zahl_tmp3.length == 2)
				html += "0" + zahl_tmp3 + "";
			else
				html += "" + zahl_tmp3 + "";
		}
		else if (zahl >= 1000)
		{
			zahl_tmp1 = Math.floor(zahl / 1000);
			html += "" + zahl_tmp1 + ".";
			zahl_tmp2 = Math.floor(zahl - (zahl_tmp1 * 1000)) + "";

			if(zahl_tmp2.length == 1)
				html += "00" + zahl_tmp2 + "";
			else if(zahl_tmp2.length == 2)
				html += "0" + zahl_tmp2 + "";
			else
				html += "" + zahl_tmp2 + "";
		}
		else
			html = zahl;

		return html;
	},
	updateResources: function ()
	{
		var metall, crystall, deuterium;
		var bold1_met = '<font color=#3abc55>';
		var bold2_met = '</font>';
		var bold1_cry = '<font color=#3abc55>';
		var bold2_cry = '</font>';
		var bold1_deu = '<font color=#3abc55>';
		var bold2_deu = '</font>';
		var faktor_met = 1;
		var faktor_cry = 1;
		var faktor_deu = 1;
		var ges_met = production[0];
		var ges_cry = production[1];
		var ges_deu = production[2];

		var rohstoffe = $('#ress')[0];

		if (rohstoffe === undefined)
			return;

		if(rohstoffe.metall.value >= max[0] - ress[0] || rohstoffe.bmetall.value == 1 || ress[0] >= max[0]) {
			bold1_met = '<div class="full">';
			bold2_met = '</div>';
			rohstoffe.bmetall.value = 1;
			faktor_met = 0;

		}
		metall = Math.floor(rohstoffe.metall.value) + Math.floor(ress[0]);
		rohstoffe.metall.value = (Math.floor(rohstoffe.metall.value * 10000)/10000) + (ges_met * faktor_met);

		if(rohstoffe.crystall.value >= max[1] - ress[1] || rohstoffe.bcrystall.value == 1 || ress[1] >= max[1]) {
			bold1_cry = '<div class="full">';
			bold2_cry = '</div>';
			rohstoffe.bcrystall.value = 1;
			faktor_cry = 0;
		}

		crystall = Math.floor(rohstoffe.crystall.value) + Math.floor(ress[1]);
		rohstoffe.crystall.value = (Math.floor(rohstoffe.crystall.value * 10000)/10000) + (ges_cry * faktor_cry);

		if(rohstoffe.deuterium.value >= max[2] - ress[2] || rohstoffe.bdeuterium.value == 1 || ress[2] >= max[2]) {
			bold1_deu = '<div class="full">';
			bold2_deu = '</div>';
			rohstoffe.bdeuterium.value = 1;
			faktor_deu = 0;
		}
		deuterium = Math.floor(rohstoffe.deuterium.value) + Math.floor(ress[2]);
		rohstoffe.deuterium.value = (Math.floor(rohstoffe.deuterium.value * 10000)/10000) + (ges_deu * faktor_deu);

	    $('#met').html(bold1_met+number_format(metall, 0, ',', '.')+bold2_met);
	    $('#cry').html(bold1_cry+number_format(crystall, 0, ',', '.')+bold2_cry);
	    $('#deu').html(bold1_deu+number_format(deuterium, 0, ',', '.')+bold2_deu);
	},
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

			if (el.attr('href').indexOf('#') == 0)
				return false;

			if (el.attr('href').indexOf('javascript') == 0 || el.attr('href').indexOf('mailto') == 0 || el.attr('href').indexOf('#') >= 0 || el.attr('target') == '_blank')
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
			},
			error: function()
			{
				$('#tooltip').hide();
				hideLoading();

				alert('Что-то пошло не так!? Попробуйте еще раз');
			}
		});

		$(document).on('submit', '#windowDialog form', function(e)
		{
			e.preventDefault();

			showLoading();

			$.ajax({
				url: $(this).attr('target'),
				type: 'post',
				data: $(this).serializeObject(),
				dataType: 'json',
				success: function (data)
				{
					hideLoading();

					if (data.message != '')
					{
						$.toast({
							text: data.message,
							icon: statusMessages[data.status]
						});
					}
					else if (data.html != '')
					{
						ClearTimers();

						$('#gamediv').html(data.html);
						dialog.dialog("close");
					}

					if (data.status == 0)
					{
						dialog.dialog("close");
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


function doc(id)
{
	return document.getElementById(id);
}

var mark = 1;

var isMobile = /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);

function SelectAll()
{
	for (var i = 0; i < document.forms['mes_form'].elements.length; i++)
	{
        var item = document.forms['mes_form'].elements[i];
		if (item.name.indexOf('delmes') >= 0)
        {
		    item.checked = mark;
		}
	}
	if (mark == 0)
		mark = 1;
	else
		mark = 0;
}

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
	txt = txt.replace(/<a href="(.*?)">/gi, "[url=http://uni3.xnova.su$1]");

	raport.html(txt);
}

function number_format(number, decimals, dec_point, thousands_sep)
{
	number = (number + '')
			.replace(/[^0-9+\-Ee.]/g, '');
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
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
			.split('.');
	if (s[0].length > 3)
	{
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '')
			.length < prec)
	{
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1)
				.join('0');
	}
	return s.join(dec);
}

function FlotenTime (obj, time)
{
	var v       = new Date();
	var divs    = $('#'+obj);
	var ttime   = time;
	var mfs1    = 0;
	var hfs1     = 0;

	if (ttime < 1)
		divs.html("-");
	else
    {
		if (ttime > 59) {
			mfs1 = Math.floor(ttime / 60);
			ttime = ttime - mfs1 * 60;
		}
		if (mfs1 > 59) {
			hfs1 = Math.floor(mfs1 / 60);
			mfs1 = mfs1 - hfs1 * 60;
		}
		if (ttime < 10) {
			ttime = "0" + ttime;
		}
		if (mfs1 < 10) {
			mfs1 = "0" + mfs1;
		}
		divs.html(hfs1 + ":" + mfs1 + ":" + ttime);
	}

	time--;

	timeouts['fleet'+obj] = window.setTimeout(function(){FlotenTime(obj,time)}, 999);
}

var Djs = start_time.getTime() - start_time.getTimezoneOffset()*60000;

function hms(layr, X)
{
      var d,mn,m,s;

      $("#" + layr).html(((d=X.getDate())<10?'0':'')+d+'.'+((mn=X.getMonth()+1)<10?'0':'')+mn+'.'+X.getFullYear()+' '+X.getHours()+':'+((m=X.getMinutes())<10?'0':'')+m+':'+((s=X.getSeconds())<10?'0':'')+s);
}

function UpdateClock()
{
   	var D0 = new Date;
    hms('clock', new Date(D0.getTime() + serverTime));

	timeouts['clock'] = setTimeout(UpdateClock, 999);
}

function setMaximum(type, number)
{
    if(document.getElementsByName('fmenge['+type+']')[0].value == 0)
   		document.getElementsByName('fmenge['+type+']')[0].value = number;
	else
		document.getElementsByName('fmenge['+type+']')[0].value = 0;
}

function UpdateGameInfo (mes, ally)
{
	$('#new_messages').html(''+mes+'');
	$('#ally_messages').html(''+ally+'');
}

function setCookie (name, value, expires, path, domain, secure)
{
      document.cookie = name + "=" + escape(value) + ((expires) ? "; expires=" + expires : "") + ((path) ? "; path=" + path : "") + ((domain) ? "; domain=" + domain : "") + ((secure) ? "; secure" : "");
}

function getCookie(name)
{
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;

	if (cookie.length > 0)
    {
		offset = cookie.indexOf(search);
		if (offset != -1)
        {
			offset += search.length;
			end = cookie.indexOf(";", offset);
			if (end == -1)
            {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

function QuickFleet (mission, galaxy, system, planet, type, count)
{
	$.ajax({
		type: "GET",
		url: "?set=fleet&page=quick",
		data: "ajax=1&mode="+mission+"&g="+galaxy+"&s="+system+"&p="+planet+"&t="+type+"&count="+count+"",
		success: function(msg)
		{
			if ($('#galaxyMessage').length > 0)
			{
				$('#galaxyMessage').html(msg).show();

				setTimeout(function ()
				{
					$('#galaxyMessage').hide();
				}, 3000);
			}
			else
				alert(msg);
		}
	});
}

function fenster(target_url, win_name, w, h)
{
	if (!w)
		w=640;
	if (!h)
		h=480;

	var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width='+w+',height='+h+',top=0,left=0');
	new_win.focus();
}

function ClearTimers ()
{
	for(var i in timeouts)
	{
		clearInterval(timeouts[i]);
		clearTimeout(timeouts[i]);
	}

	timeouts.length = 0;
}

function load (url)
{
	if (!blockTimer)
		return false;

    ClearTimers();

	blockTimer = false;

    var loc = url.substring(1).split("&");

    var set = loc[0].split("=");
	
	var mod;
	
	if (loc[1] !== undefined)
		mod = loc[1].split("=");

    if (set[1] != 'buildings')
        currentState = set[1];
    else
        currentState = set[1] + ((loc[1] != undefined && loc[1] != 'ajax=2' && loc[1] != 'ajax=1') ? '&'+loc[1] : '');

	url = url+(addToUrl != '' ? '&'+addToUrl : '');

	showLoading();

	$.ajax(
	{
		url: url+'&ajax=1&random=' + Math.random()*99999,
		cache: false,
		dataType: 'json',
		success: function(html)
		{
			$('#tooltip').hide();
			hideLoading();
			ClearTimers();

			$('body > .contentBox').attr('class', 'contentBox set_'+set[1]+(mod !== undefined && set[1] == 'buildings' && mod[0] !== undefined ? mod[1] : ''));
			$('body.window .content').css('width', '');
			$('#box, .game_content > .content').css('display', '');

			$('#gamediv').html(data.html);

			if (data.message != '')
			{
				$.toast({
					text: data.message,
					icon: statusMessages[data.status]
				});
			}

			if (data.data.redirect !== undefined)
				window.location.href = data.data.redirect;

			dialog.dialog("close");

			if (data.data.tutorial !== undefined && data.data.tutorial.popup != '')
			{
				$.confirm({
				    title: 'Обучение',
				    content: data.data.tutorial.popup,
					confirmButton: 'Продолжить',
					cancelButton: false,
					backgroundDismiss: false,
					confirm: function ()
					{
						if (data.data.tutorial.url != '')
						{
							load(data.data.tutorial.url);
						}
					}
				});
			}

			if (data.data.tutorial.toast != '')
			{
				$.toast({
					text: data.data.tutorial.toast,
					icon: 'info',
					stack : 1
				});
			}
		},
		timeout: 10000,
		error: function()
		{
			$('#tooltip').hide();
			document.location = url;
		}
	});

	start_time      = new Date();
    Djs             = start_time.getTime() - start_time.getTimezoneOffset()*60000;

	return true;
}

function setMenuItem (location)
{
	$('.game_content > ul li > a').removeClass('check');

	if (location != undefined && location != '')
    	$('#link_'+location).addClass('check');
}



function addHistoryState (url)
{
	var supportsHistoryAPI = !!(window.history && history.pushState);

	if (supportsHistoryAPI)
	{
		window.history.pushState({save: 1}, null, url);
	}
	else
	{
		//window.location.hash = currentState;
	}
}

var tooltipTimer;

var currentState = window.location.hash.slice(1);

var dialog;

$(document).ready(function()
{
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

    if (ajax_nav == 1)
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
						load(currentState+'&ep=dontsavestate');

						e.preventDefault();
					}
				});
			}, 1000);
		}
    }

	$('body').on('mouseenter', '.tooltip', function()
	{
		var tip = $('#tooltip');

		if ($(this).data('tooltipWidth'))
			tip.css({width: $(this).data('tooltipWidth')});
		else
			tip.css({width: ''});

		tip.html($(this).data('tooltipContent')).show();
	})
	.on('mouseleave', '.tooltip', function()
	{
		$('#tooltip').hide();
	})
	.on('mousemove', '.tooltip', function(e)
	{
		var tip = $('#tooltip');

		if (tip.is(':visible'))
		{
			var mousex = e.pageX + 20;
			var mousey = e.pageY + 20;

			var tipWidth = tip.width();
			var tipHeight = tip.height();

			var tipVisX = $(window).width() - (mousex + tipWidth);
			var tipVisY = $(window).height() - (mousey + tipHeight);

			if (tipVisX < 20)
				mousex = e.pageX - tipWidth - 20;

			if (tipVisY < 20)
				mousey = e.pageY - tipHeight - 20;

			tip.css({
				top: mousey,
				left: mousex
			});
		}
	})
	.on('mouseenter', ".tooltip_sticky", function (e)
	{
   		var tip = $('#tooltip');
		var obj = $(this);

		tip.css({width: ''});

		tooltipTimer = setTimeout(function()
		{
			tip.html(obj.data('tooltipContent')).addClass('tooltip_sticky_div');

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
	});

	$('#windowDialog form').ajaxForm(
	{
		delegation: true,
		target: '#windowDialog',
		beforeSerialize: function(form)
		{
			$(form).append('<input type="hidden" name="ajax" value="1">');
			$(form).append('<input type="hidden" name="ep" value="dontsavestate">');

			showLoading();
		},
		success: function ()
		{
			hideLoading();
		},
		error: function()
		{
			hideLoading();

			alert('Что-то пошло не так!? Попробуйте еще раз');
		}
	});
});

function showWindow (title, url, width, height)
{
	if (XNova.isMobile == false)
	{
		$('#windowDialog').html('');

		showLoading();

		if (height === undefined)
			height = 'auto';

		$.ajax(
				{
					url: url,
					cache: false,
					data: {ajax: 'Y'},
					dataType: 'json',
					success: function (json)
					{
						var obj = $('#windowDialog');

						obj.dialog("option", "title", title);

						if (width != undefined)
						{
							obj.dialog("option", "minWidth", width);
							obj.dialog("option", "width", width);
						}

						if (height != undefined)
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

var breakArrow = 0;

function showArrow (top, left)
{
	var id = parseInt(top) + parseInt(left);

	$('#arr'+id).remove();

	if ($('#gamediv').length > 0)
		$('#gamediv').append('<div class="arrow" id="arr'+id+'"></div>');
	else
		$('#box').append('<div class="arrow" id="arr'+id+'"></div>');

	$('#arr'+id).css('top', top).css('left', left);

	breakArrow = 0;

	function run()
	{
		if (!breakArrow && $('#arr'+id).length > 0)
	    	$('#arr'+id).animate({"opacity": 0.1},1000).animate({"opacity": 1},500, run);
	}
	run();
}

function hideArrow()
{
	breakArrow = 1;
	$('#box .arrow').remove();
}

var loadingTimer;
var blockTimer = true;

function showLoading ()
{
	$('#preloadOverlay').show();

	setTimeout(function()
	{
		blockTimer = true;
	}, 500);

	/**
	clearTimeout(loadingTimer);
	loadingTimer = setTimeout(function()
	{
		$('#preloadOverlay').hide();
		$('#loadingOverlay').show();
	}, 1000);
	**/
}

function hideLoading ()
{
	//blockTimer = true;
	//clearTimeout(loadingTimer);
	//$('#loadingOverlay').hide();
	$('#preloadOverlay').hide();
}

function f(target_url, win_name)
{
	var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0');
	new_win.focus();
}

function html5_storage()
{
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch(e) {
        return false;
    }
}