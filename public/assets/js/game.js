function ShowHiddenBlock (id)
{
    $('#'+id).toggle();
}

function load (url)
{
	application.$router.push(url, function() {},
	function() {
		application.loadPage(url);
	});
}

$(document).ready(function()
{
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

	if (typeof VK !== 'undefined')
	{
		try
		{
			VK.init(function()
			{
				console.log('vk init success');

				setInterval(function()
				{
					var height = 0;

					$('#application .main-content > div').each(function() {
						height += $(this).height();
					});

					VK.callMethod("resizeWindow", 1000, (height < 600 ? 700 : height + 200));

				}, 1000);
			},
			function() {}, '5.74');
		}
		catch (e) {}
	}
});

function showWindow (title, url, width)
{
	application.openPopup(title, url, width);
}

function closeWindow()
{
	jconfirm.instances.forEach(function(item) {
		item.close();
	});
}

jQuery.cachedScript = function (url, options)
{
	options = $.extend(options || {}, {
		dataType: "script",
		cache: true,
		url: url
	});

	return jQuery.ajax(options);
};