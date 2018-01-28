var isMobile = /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
var statusMessages = {0: 'error', 1: 'success', 2: 'info', 3: 'warning'};
var timeouts	= [];

function ShowHiddenBlock (id)
{
    $('#'+id).toggle();
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
			close: function() {
				$('.ui-helper-hidden-accessible').html('');
			}
		});
	}

	var body = $("body");

	body.on('click', 'a', function(e)
	{
		var el = $(this);
		var url = el.attr('href');

		if (!url || el.hasClass('window') || url.indexOf('#') === 0)
			return false;

		if (url.indexOf('javascript') === 0 || url.indexOf('mailto') === 0 || url.indexOf('#') >= 0 || el.attr('target') === '_blank')
			return true;
		else
		{
			e.preventDefault();

			load(url);
		}

		return false;
	})
	.on('submit', '.main-content form[class!=noajax]', function(e)
	{
		e.preventDefault();

		var form = $(this);

		application.loader = true;

		var formData = new FormData(this);

		$.ajax({
		    url: form.attr('action'),
		    data: formData,
		    type: 'post',
			dataType: 'json',
		    contentType: false,
		    processData: false,
			success: function (result) {
				application.applyData(result.data);
			},
			error: function() {
				alert('Что-то пошло не так!? Попробуйте еще раз');
			},
			complete: function()
			{
				$('#tooltip').hide();
				application.loader = false;
			}
		});
	})
	.on('submit', '.jconfirm-dialog form', function(e)
	{
		e.preventDefault();

		application.loader = true;

		var formData = new FormData(this);
		formData.append('popup', 'Y');

		$.ajax({
			url: $(this).attr('action'),
			type: 'post',
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function (result)
			{
				if (result.data.message.length > 0 && result.data.message !== '')
				{
					result.data.message.forEach(function(item)
					{
						$.toast({
							text: item.message,
							icon: item.type
						});
					})
				}
				else if (result.data.html !== '')
				{
					$('.jconfirm-content').html(result.data.html);

					TextParser.parseAll();
				}
			},
			error: function() {
				alert('Что-то пошло не так!? Попробуйте еще раз');
			},
			complete: function() {
				application.loader = false;
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
	if (isMobile)
		return window.location.href = url.split('ajax').join('').split('popup').join('');

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
			return $.ajax({
				url: url,
				type: 'get',
				data: {'popup': 'Y'},
				success: function (result)
				{
					this.setTitle(result.data.title);
					this.setContent(result.data.html);
				}.bind(this)
			});
		}
	});
}

function closeWindow()
{
	jconfirm.instances.forEach(function(item) {
		item.close();
	});
}