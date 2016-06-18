function BuildTimeout(pp, pk, pl, at)
{
	var blc     	= $('#blc');
	var s           = pp;
	var m           = 0;
	var h           = 0;

	if ( s < 0 )
    {
		blc.html("Завершено<br>" + "<a href='#' onclick='load(\""+XNova.path+"buildings/index/planet/" + pl + "/\")'>Продолжить</a>");

		timeouts['build'+pk+'-'+pl] = setTimeout('load("'+XNova.path+'buildings/index/planet/' + pl + '/");', 5000);

		return;
	}
    else
    {
		if ( s > 59) {
			m = Math.floor( s / 60);
			s = s - m * 60;
		}
		if ( m > 59) {
			h = Math.floor( m / 60);
			m = m - h * 60;
		}
		if ( s < 10 ) {
			s = "0" + s;
		}
		if ( m < 10 ) {
			m = "0" + m;
		}

		if (at > timestamp - 5)
			blc.html(h + ":" + m + ":" + s);
		else
			blc.html(h + ":" + m + ":" + s + "<br><a href='#' onclick='load(\""+XNova.path+"buildings/index/listid/" + pk + "/cmd/cancel/planet/" + pl + "/\")'>Отменить</a>");
	}

	pp--;

	timeouts['build'+pk+'-'+pl] = setTimeout("BuildTimeout("+pp+", "+pk+", "+pl+", "+(at - 1)+");", 1000);
}

function reloadPlanetList ()
{
	$('.planetList .list').load('ajax.php?action=getPlanetList');
}

$(document).ready(function()
{
	if (window.location.host.indexOf("cmle.ru") >= 0)
		eval("window.location.href = \"http://uni5.xnova.su/\";");

	if (typeof VK != 'undefined')
	{
		setInterval(function()
		{
			var d = $('#gamediv > .content-row');

			VK.callMethod("resizeWindow", 900, (d.height() < 600 ? 600 : d.height()) + 200);

		}, 1000);
	}

	if (typeof FAPI != 'undefined')
	{
		setInterval(function()
		{
			var d = $('#gamediv > .content-row');

			FAPI.UI.setWindowSize(800, (d.height() < 600 ? 600 : d.height()) + 200);
			
		}, 1000);
	}

	$('body').on('click', '.popup-user', function(e)
	{
		e.preventDefault();

		showWindow('', $(this).attr('href'))
	});

	if (false && $('.planetList .list').length)
	{
		if( !isMobile )
		{
			$('.planetList .list').css('height', $(window).height() - 100);

			$(window).bind('resize', function()
			{
				$('.planetList .list').css('height', $(window).height() - 100);
			});

			/*if (ajax_nav == 1)
			{
				setInterval(function()
				{
					reloadPlanetList();
				}, 1200000);
			}*/
		}
		else
			$('.planetList .list').css('height', 'auto').css('min-height', 'auto');
	}

	/*
	$(document).on('keydown', function(event)
	{
		if (location.search.indexOf('galaxy') > 0)
		{
			if (event.keyCode == $.ui.keyCode.DOWN)
			{
				event.preventDefault();
				galaxy_submit('galaxyRight');
			}
			else if (event.keyCode == $.ui.keyCode.UP)
			{
				event.preventDefault();
				galaxy_submit('galaxyLeft');
			}
			else if (event.keyCode == $.ui.keyCode.RIGHT)
			{
				event.preventDefault();
				galaxy_submit('systemRight');
			}
			else if (event.keyCode == $.ui.keyCode.LEFT)
			{
				event.preventDefault();
				galaxy_submit('systemLeft');

			}
		}
	});
	*/

	$('.menu-toggle').click(function(e)
	{
		e.preventDefault();
		$(this).toggleClass('act');
		$('.menu-sidebar').toggleClass('opened');
		$('.planet-sidebar').removeClass('opened');
		$('.planet-toggle').removeClass('act');

		$('html').toggleClass('menu_opened');
	});

	$('.planet-toggle').click(function(e)
	{
		e.preventDefault();
		$(this).toggleClass('act');
		$('.planet-sidebar').toggleClass('opened');
		$('.menu-sidebar').removeClass('opened');
		$('.menu-toggle').removeClass('act');

		$('html').toggleClass('menu_opened');
	});

 	$('.menu-sidebar a').click(function(e)
	{
		$('.menu-toggle').removeClass('act');
		$('.menu-sidebar').removeClass('opened');
	});

	$('.menu-sidebar, .planet-sidebar').show();
});

function changePlanet (pId)
{
	var a = window.location.pathname.split('/');

	var url = '/'+a[1]+(a[2] !== undefined && a[2] != '' && a[1] != 'fleet' ? '/'+a[2] : '')+'/?chpl='+pId+'';

	if (ajax_nav == 1)
		load(url);
	else
		window.location.href = url;
}

function parse_str (url)
{
	var result = [];

	var lit = url.split('&');

	for (var x=0; x < lit.length; x++)
	{
		var tmp = lit[x].split('=');
		result[unescape(tmp[0])] = unescape(tmp[1]).replace(/[+]/g, ' ');
	}

	return result;
}