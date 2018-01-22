var isMobile = /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
var statusMessages = {0: 'error', 1: 'success', 2: 'info', 3: 'warning'};
var timeouts	= [];

function ShowHiddenBlock (id)
{
    $('#'+id).toggle();
}

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

function clearTimers ()
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

function load (url)
{
	application.$router.push(url, function() {},
	function() {
		application.loadPage(url);
	});
}

var tooltipTimer;

$(document).ready(function()
{
	if ($.isFunction($(document).tooltip))
	{
		$(document).tooltip({
			items: ".tooltip",
			track: !isMobile,
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

	body.on('click', 'a', function(e)
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
	.on('submit', '.main-content form[class!=noajax]', function(e)
	{
		e.preventDefault();

		var form = $(this);

		showLoading();
		clearTimers();

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

				application.applyData(result.data);
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
   		$('.tooltip_sticky_div').removeClass('tooltip_sticky_div').hide();
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
	if (!isMobile)
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