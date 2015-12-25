function BuildTimeout(pp, pk, pl, at)
{
	var blc     	= $('#blc');
	var s           = pp;
	var m           = 0;
	var h           = 0;

	if ( s < 0 )
    {
		blc.html("Завершено<br>" + "<a href='#' onclick='load(\"?set=buildings&planet=" + pl + "\")'>Продолжить</a>");

		timeouts['build'+pk+'-'+pl] = window.setTimeout('load("?set=buildings&planet=' + pl + '");', 5000);

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
			blc.html(h + ":" + m + ":" + s + "<br><a href='#' onclick='load(\"?set=buildings&listid=" + pk + "&cmd=cancel&planet=" + pl + "\")'>Отменить</a>");
	}

	pp--;

	timeouts['build'+pk+'-'+pl] = window.setTimeout("BuildTimeout("+pp+", "+pk+", "+pl+", "+(at - 1)+");", 999);
}

function reloadPlanetList ()
{
	$('.planetList .list').load('ajax.php?action=getPlanetList');
}

$(document).ready(function()
{
	if (window.location.host.indexOf("cmle.ru") >= 0)
		eval("window.location.href = \"http://uni4.xnova.su/\";");

	if (typeof VK != 'undefined')
	{
		setInterval(function()
		{
			var d = $('#gamediv');

			VK.callMethod("resizeWindow", 900, (d.height() < 600 ? 600 : d.height()) + 100);

		}, 1000);
	}

	if (typeof FAPI != 'undefined')
	{
		setInterval(function()
		{
			var d = $('#gamediv');

			FAPI.UI.setWindowSize(800, (d.height() < 600 ? 600 : d.height()) + 100);
			
		}, 1000);
	}

	$('body').on('click', '.popup-user', function(e)
	{
		e.preventDefault();

		showWindow('', $(this).attr('href')+'&ajax&popup')
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
	});

	$('.planet-toggle').click(function(e)
	{
		e.preventDefault();
		$(this).toggleClass('act');
		$('.planet-sidebar').toggleClass('opened');
		$('.menu-sidebar').removeClass('opened');
		$('.menu-toggle').removeClass('act');
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
	var a = parse_str(document.location.search.substr(1));

	var url = '?set='+a['set']+''+(a['mode'] !== undefined ? '&mode='+a['mode'] : '')+'&cp='+pId+'&re=0';

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



var deg = Math.PI / 180;
var maxflakes = 20;
var flakes = [];
var scrollspeed = 64;
var snowspeed = 500;
var canvas, sky;
var snowingTimer, moveShowTimer;
var invalidateMeasure = false;

var strokes = ["#6cf", "#9cf", "#99f", "#ccf", "#66f", "#3cf"];

function rand(n)
{
	return Math.floor(n * Math.random());
}

// Запуск снегопада
function snow()
{
	if ($('body > canvas').length == 0)
	{
		canvas = document.createElement('canvas');
		canvas.style.position = 'fixed';
		canvas.style.top = '0px';
		canvas.style.left = '0px';
		canvas.style.zIndex = '-10';

		document.body.insertBefore(canvas, document.body.firstChild);
		sky = canvas.getContext('2d');

		ResetCanvas();

		snowingTimer = setInterval(createSnowflake, snowspeed);
		moveShowTimer = setInterval(moveSnowflakes, scrollspeed);
		window.addEventListener('resize', ResetCanvas, false);
	}
	else
	{
		if (maxflakes < 50)
		maxflakes += 5;

		if (snowspeed > 100)
		snowspeed -= 50;

		if (scrollspeed > 10)
			scrollspeed -= 5;
		clearInterval(snowingTimer);
		clearInterval(moveShowTimer);
		snowingTimer = setInterval(createSnowflake, snowspeed);
		moveShowTimer = setInterval(moveSnowflakes, scrollspeed);
	}
}

// Сброс размеров Canvas
function ResetCanvas()
{
	invalidateMeasure = true;
	canvas.width = document.body.offsetWidth;
	canvas.height = window.innerHeight;
}

// Отрисовка кривой Коха
function leg(n, len)
{
	sky.save();       // Сохраняем текущую трансформацию
	if (n == 0)
	{      // Нерекурсивный случай - отрисовываем линию
		sky.lineTo(len, 0);
	}
	else
	{
		sky.scale(1 / 3, 1 / 3);  // Уменьшаем масштаб в 3 раза
		leg(n - 1, len);
		sky.rotate(60 * deg);
		leg(n - 1, len);
		sky.rotate(-120 * deg);
		leg(n - 1, len);
		sky.rotate(60 * deg);
		leg(n - 1, len);
	}
	sky.restore();      // Восстанавливаем трансформацию
	sky.translate(len, 0); // переходим в конец ребра
}

// Отрисовка снежинки Коха
function drawFlake(x, y, angle, len, n, stroke, fill)
{
	sky.save();
	sky.strokeStyle = stroke;
	sky.fillStyle = fill;
	sky.beginPath();
	sky.translate(x, y);
	sky.moveTo(0, 0);
	sky.rotate(angle);
	leg(n, len);
	sky.rotate(-120 * deg);
	leg(n, len);
	sky.rotate(-120 * deg);
	leg(n, len);
	sky.closePath();
	sky.fill();
	sky.stroke();
	sky.restore();
}

// Создание пула снежинок
function createSnowflake()
{
	var order = 2 + rand(2);
	var size = 10 * order + rand(10);
	var x = rand(document.body.offsetWidth);
	var y = window.pageYOffset;
	var stroke = strokes[rand(strokes.length)];

	flakes.push({x: x, y: y, vx: 0, vy: 3 + rand(3), angle: 0, size: size, order: order, stroke: stroke, fill: 'transparent'});

	if (flakes.length > maxflakes) clearInterval(snowingTimer);
}

// Перемещение снежинок
function moveSnowflakes()
{
	sky.clearRect(0, 0, canvas.width, canvas.height);

	var maxy = canvas.height;

	for (var i = 0; i < flakes.length; i++)
	{
		var flake = flakes[i];

		flake.y += flake.vy;
		flake.x += flake.vx;

		if (flake.y > maxy) flake.y = 0;
		if (invalidateMeasure)
		{
			flake.x = rand(canvas.width);
		}

		drawFlake(flake.x, flake.y, flake.angle, flake.size, flake.order, flake.stroke, flake.fill);

		// Иногда меняем боковой ветер
		if (rand(4) == 1) flake.vx += (rand(11) - 5) / 10;
		if (flake.vx > 2) flake.vx = 2;
		if (flake.vx < -2) flake.vx = -2;
		if (rand(3) == 1) flake.angle = (rand(13) - 6) / 271;
	}
	if (invalidateMeasure) invalidateMeasure = false;
}