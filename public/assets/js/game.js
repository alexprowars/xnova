var XNova =
{
	isMobile: /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent),
	lastUpdate: 0
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

var Format = {
	number: function(value)
	{
		if (value > 1000000000)
			return number_format(Math.floor(value / 1000000), 0, ',', '.')+'kk';

		return number_format(value, 0, ',', '.');
	},
	time: function (value, separator)
	{
		if (typeof separator === 'undefined')
			separator = '';

		var dd = Math.floor(value / (24 * 3600));
		var hh = Math.floor(value / 3600 % 24);
		var mm = Math.floor(value / 60 % 60);
		var ss = Math.floor(value / 1 % 60);

		var time = '';

		if (dd !== 0)
			time += ((separator !== '' && dd < 10) ? '0' : '')+dd+((separator !== '') ? separator : ' д. ');

		if (hh > 0)
			time += ((separator !== '' && hh < 10) ? '0' : '')+hh+((separator !== '') ? separator : ' ч. ');

		if (mm > 0)
			time += ((separator !== '' && mm < 10) ? '0' : '')+mm+((separator !== '') ? separator : ' мин. ');

		if (ss !== 0)
			time += ((separator !== '' && ss < 10) ? '0' : '')+ss+((separator !== '') ? '' : ' с. ');

		if (!time.length)
			time = '-';

		return time;
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
		time = flotenTime['fleet'+obj] - Math.floor((((new Date).getTime() - flotenTimers['fleet'+obj]) / 1000));

	$('#'+obj).html(Format.time(time, ':'));

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

function QuickFleet (mission, galaxy, system, planet, type, count)
{
	$.ajax({
		type: "GET",
		url: options.path+"fleet/quick/",
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

	if (typeof disableUrlState === 'undefined')
		disableUrlState = false;

	showLoading();

	$('[role="tooltip"]').remove()

	$.ajax(
	{
		url: url,
		cache: false,
		dataType: 'json',
		success: function(result)
		{
			$('#tooltip').hide();
			hideLoading();
			ClearTimers();

			$('body.window .game_content').css('width', '');
			$('.ui-helper-hidden-accessible').html('');

			if (result.data.messages.length > 0)
			{
				result.data.messages.forEach(function(item)
				{
					$.toast({
						text: item.text,
						icon: item.type
					});
				})
			}

			if (typeof result.data['title_full'] !== 'undefined')
				document.title = result.data['title_full'];

			if (result.data.redirect !== undefined)
				window.location.href = result.data.redirect;

			if (disableUrlState === false && typeof result.data.url !== 'undefined')
			{
				if (!!(window.history && history.pushState))
					window.history.pushState({save: 1}, null, result.data.url);
			}

			closeWindow();

			application.applyData(result.data);
			application.$router.push(result.data.url);

			if (typeof result.data['tutorial'] !== 'undefined' && result.data['tutorial']['popup'] !== '')
			{
				$.confirm({
				    title: 'Обучение',
				    content: result.data.tutorial.popup,
					confirmButton: 'Продолжить',
					cancelButton: false,
					backgroundDismiss: false,
					confirm: function ()
					{
						if (result.data['tutorial']['url'] !== '')
							load(result.data['tutorial']['url']);
					}
				});
			}

			if (typeof result.data['tutorial'] !== 'undefined' && result.data['tutorial']['toast'] !== '')
			{
				$.toast({
					text: result.data['tutorial']['toast'],
					icon: 'info',
					stack : 1
				});
			}

			TextParser.parseAll();
		},
		timeout: 10000,
		error: function(jqXHR, exception)
		{
			console.log(jqXHR.responseText);
			console.log(exception);

			$('#tooltip').hide();
			document.location = url;
		}
	});

	start_time      = new Date();
    Djs             = start_time.getTime() - start_time.getTimezoneOffset()*60000;

	return true;
}

var tooltipTimer;

$(document).ready(function()
{
	if (!!(window.history && history.pushState))
	{
		window.history.pushState({save: 1}, null, location.search);

		window.setTimeout( function()
		{
			$(window).on("popstate", function(e)
			{
				var data = e.originalEvent.state;

				if (data !== null)
				{
					load(location.search, true);

					e.preventDefault();
				}
			});
		}, 1000);
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

	var body = $("body");

	body.on('click', 'a:not(.skip)', function(e)
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
	})
	.on('submit', '.content form[class!=noajax]', function(e)
	{
		e.preventDefault();

		var form = $(this);

		showLoading();

		ClearTimers();
		start_time = new Date();
		Djs = start_time.getTime() - start_time.getTimezoneOffset()*60000;

		var formData = new FormData(form[0]);

		$.ajax({
		    url: form.attr('action'),
		    data: formData,
		    type: 'post',
			dataType: 'json',
		    contentType: false,
		    processData: false,
			success: function (result)
			{
				$('#tooltip').hide();
				hideLoading();

				if (result.data.redirect !== undefined)
					window.location.href = result.data.redirect;

				for (var key in result.data)
				{
					if (result.data.hasOwnProperty(key))
						Vue.set(options, key, result.data[key])
				}

				setTimeout(function(){
					application.evalJs(result.data.html);
				}, 25);

				TextParser.parseAll();
			},
			error: function()
			{
				$('#tooltip').hide();
				hideLoading();

				alert('Что-то пошло не так!? Попробуйте еще раз');
			}
		});
	})
	.on('submit', '.jconfirm-dialog form', function(e)
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
				settings.data += (settings.data !== '' ? '&' : '')+'popup=Y';
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
					$('.jconfirm-content').html(data.html);

					TextParser.parseAll();
				}
			},
			error: function()
			{
				hideLoading();

				alert('Что-то пошло не так!? Попробуйте еще раз');
			}
		})
	})
	.on('mouseenter', ".tooltip_sticky", function (e)
	{
   		var tip;
		var obj = $(this);

		var hasHtml = obj.find('.tooltip-content').length > 0;

		if (hasHtml)
			tip = obj.find('.tooltip-content');
		else
			tip = $('#tooltip');

		tip.css({width: ''});

		tooltipTimer = setTimeout(function()
		{
			if (typeof obj.data('content') !== 'undefined')
				tip.html(obj.data('content'));
			else
				tip.show();

			tip.addClass('tooltip_sticky_div');

			if (hasHtml)
			{
				var parentOffset = $('.game_content > .content').offset();

				tip.css({
					top : obj.offset().top - parentOffset.top - tip.outerHeight() / 2,
					left : obj.offset().left - parentOffset.left - (tip.outerWidth() / 2)
				});
			}
			else
			{
				tip.css({
					top : e.pageY - tip.outerHeight() / 2,
					left : e.pageX - tip.outerWidth() / 2
				});
			}

			tip.show();
		}, 400);
   	})
	.on('mouseleave', ".tooltip_sticky", function ()
	{
		clearTimeout(tooltipTimer);
	})
	.on('mouseleave', ".tooltip_sticky_div", function (e)
	{
   		var tip = $('.tooltip_sticky_div');

   		tip.removeClass('tooltip_sticky_div').hide();
   	})
	.on('change', 'input.checkAll', function()
	{
		var checked = $(this).is(':checked');

		$(this).closest('form').find('input[type=checkbox]').each(function()
		{
			$(this).prop('checked', checked);
		});
	})
	.on('click', '.popup-user', function(e)
	{
		e.preventDefault();

		showWindow('', $(this).attr('href'))
	});

	if (typeof swipe !== 'undefined' && !navigator.userAgent.match(/(\(iPod|\(iPhone|\(iPad)/))
	{
		body.swipe(
		{
			swipeLeft: function()
			{
				if ($('.menu-sidebar').hasClass('active'))
					$('.menu-toggle').click();
				else
					$('.planet-toggle').click();
			},
			swipeRight: function()
			{
				if ($('.planet-sidebar').hasClass('active'))
					$('.planet-toggle').click();
				else
					$('.menu-toggle').click();
			},
			threshold: 100,
			excludedElements: ".table-responsive",
			fallbackToMouseEvents: false,
			allowPageScroll: "auto"
		});
	}
});

function showWindow (title, url, width)
{
	if (!XNova.isMobile)
	{
		if (width === undefined)
			width = 600;

		$.dialog({
			title: '',
			theme: 'dialog',
			useBootstrap: false,
			boxWidth: width,
			backgroundDismiss: true,
			animation: 'opacity',
			closeAnimation: 'opacity',
			animateFromElement: false,
			draggable: false,
			content: function ()
			{
				var self = this;

				return $.ajax({
					url: url,
					type: 'get',
					data: {'popup': 'Y'},
					success: function (result)
					{
						self.setTitle(result.data.title);
						self.setContent(result.data.html);
					}
				});
			}
		});
	}
	else
		window.location.href = url.split('ajax').join('').split('popup').join('');
}

function closeWindow()
{
	jconfirm.instances.forEach(function(item)
	{
		item.close();
	});
}

var blockTimer = true;

function showLoading ()
{
	$('#ajaxLoader').show();

	setTimeout(function()
	{
		blockTimer = true;
	}, 500);
}

function hideLoading ()
{
	setTimeout(function()
	{
		$('#ajaxLoader').hide();
	}, 1000);
}



function morph (n, titles)
{
	return titles[(n % 10 === 1 && n % 100 !== 11) ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
}