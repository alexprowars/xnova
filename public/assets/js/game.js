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
	txt = txt.replace(/<th width="220">/gi, "[th(w=33)]");
	txt = txt.replace(/<th width="25%">/gi, "[th(w=25)]");
	txt = txt.replace(/<br>/gi, " ");
	txt = txt.replace(/<\/a>/gi, "[\/url]");
	txt = txt.replace(/<a href="(.*?)">/gi, "[url=https://x.xnova.su$1]");

	raport.html(txt);
}