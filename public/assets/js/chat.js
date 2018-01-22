function chatToolbar(obj, id)
{
	var str 	= '';

    str += ("<div class=\"toolbar inline\">");
	str += ("<span class='buttons' title='Вставить ссылку' onClick=\"doAddTags('[url]','[/url]','" + obj + "',1)\"><span class='sprite bb_world_link'></span></span>");
	str += ("<span class='buttons' title='Вставить картинку' onClick=\"doAddTags('[img]','[/img]','" + obj + "',3)\"><span class='sprite bb_picture_add'></span></span>");
	str += ("<span class='buttons' title='Смайлы' onClick=\"showSmilesEx('" + obj + "', this);\"><span class='sprite bb_emoticon_grin'></span></span>");
    str += ("</div>");

	str += ('<div id="smiles" class="smiles" style="display:none"></div>');

	if (id == undefined)
		$('#editor').css('position', 'relative').html(str);
	else
		$('#'+id).css('position', 'relative').html(str);
}

var findS = [];
var replaceS = [];

$(document).ready(function()
{
	findS = [
		/script/g,
		/\[b\](.*?)\[\/b\]/gi,
		/\[i\](.*?)\[\/i\]/gi,
		/\[u\](.*?)\[\/u\]/gi,
		/\[s\](.*?)\[\/s\]/gi,
		/\[left\](.*?)\[\/left\]/gi,
		/\[center\](.*?)\[\/center\]/gi,
		/\[right\](.*?)\[\/right\]/gi,
		/\[justify\](.*?)\[\/justify\]/gi,
		/\[size=([1-9]|1[0-9]|2[0-5])\](.*?)\[\/size\]/gi,
		/\[img\](https?:\/\/.*?\.(?:jpg|jpeg|png))\[\/img\]/gi,
		/\[url=((?:ftp|https?):\/\/.*?)\](.*?)\[\/url\]/g,
		/\[url\]((?:ftp|https?):\/\/.*?)\[\/url\]/g,
		/\[p\](.*?)\[\/p\]/gi,
		/\[([1-9]{1}):([0-9]{1,3}):([0-9]{1,2})\]/gi
	];

	replaceS = [
		'',
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<span style="text-decoration: underline;">$1</span>',
		'<span style="text-decoration: line-through;">$1</span>',
		'<div align="left">$1<\/div>',
		'<div align="center">$1<\/div>',
		'<div align="right">$1<\/div>',
		'<div style="text-align:justify;">$1<\/div>',
		'<span style="font-size: $1px;">$2</span>',
		'<a href="$1" target="_blank"><img src="$1" style="max-width:350px;" alt="" /></a>',
		'<a href="$1" target="_blank">$2</a>',
		'<a href="$1" target="_blank">$1</a>',
		'<p>$1</p>',
		'<a href="'+options.path+'galaxy/$1/$2/">[$1:$2:$3]</a>'
	];
});

function showSmilesEx(obj, event)
{
	var o = $('#smiles');

	if (o.is(':visible'))
		$('#smiles').html('').hide();
	else
	{
		for (var i = 0; i < TextParser.smiles.length; i++)
			o.append('<img src="'+options.path+'assets/images/smile/'+TextParser.smiles[i]+'.gif" alt="'+TextParser.smiles[i]+'" onclick="AddSmile(\''+TextParser.smiles[i]+'\', \''+obj+'\')" style="cursor:pointer"> ');

		o.show();
	}

	descendreTchat();
}

function chatResize()
{
	if (isMobile)
		return;

	var height = $(window).height() - 135;

	$('#shoutbox').css('height', height);

	$(window).bind('resize', function()
	{
		$('#shoutbox').css('height', height);

		descendreTchat();
	});
}

function to(login)
{
	var msg = $('#chatMsg');

	msg.focus();
	msg.val('для [' + login + '] ' + msg.val());
	msg.focus();
}

function pp(login)
{
	var msg = $('#chatMsg');

	msg.focus();
	msg.val('приватно [' + login + '] ' + msg.val());
	msg.focus();
}

function ChatMsg(time, Player, To, Msg, Private, Me, My)
{
	var str = "";

	var j = 0;

	for (var i = 0; i < TextParser.smiles.length; i++)
	{
		while (Msg.indexOf(':'+TextParser.smiles[i]+':') >= 0)
		{
			Msg = Msg.replace(':'+TextParser.smiles[i]+':', '<img src="'+options.path+'assets/images/smile/' + TextParser.smiles[i] + '.gif" onclick="AddSmile(\'' + TextParser.smiles[i] + '\', \'chatMsg\')" style="cursor:pointer">');

			if (++j >= 3)
				break;
		}

		if(j >= 3)
			break;
	}

	if (!time)
		return;

	if (Me > 0)
		str += "<span class='date2' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";
	else if (My > 0)
		str += "<span class='date3' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";
	else
		str += "<span class='date1' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";

	if (!Player)
		str += date('H:m', time) + "</span> ";
	else
	{
		str += date('H:m', time) + "</span> ";

		if (My == 1)
			str += "<span class='negative'>";
		else
			str += "<span class='to' onclick='to(\"" + Player + "\");'>";

		str += Player + "</span>: ";
	}

	if (To.length > 0)
	{
		str += '<span class="'+(Private ? 'private' : 'player')+'">'+(Private ? 'приватно' : 'для')+' [';

		for (i in To)
		{
			if (To.hasOwnProperty(i))
				str += (i > 0 ? ', ' : '') +'<a href=\'javascript:'+(Private ? 'pp' : 'to')+'("'+To[i]+'");\'>'+To[i]+'</a>';
		}

		str += ']</span> ';
	}

	for (i in findS)
	{
		Msg = Msg.replace(findS[i], replaceS[i]);
	}

	str += Msg;

	$('#shoutbox').append('<div align="left">' + str + '</div>');

	descendreTchat();
	setTimeout(function(){descendreTchat();}, 500);
}

function descendreTchat()
{
	var elDiv = $('#shoutbox')[0];
	elDiv.scrollTop = elDiv.scrollHeight - elDiv.offsetHeight;
}

function addMessage()
{
	var data = $("#chatMsg").val();

	while (data.indexOf('\'') >= 0) data = data.replace('\'', '`');

	$("#chatMsg").val('');

	return data;
}

function ShowSmiles()
{
	var obj = $("#smiles");

	if (obj.is(':visible'))
	{
		obj.html('').hide();
		$('#shoutbox').show();
	}
	else
	{
		for (var i = 0; i < TextParser.smiles.length; i++)
			obj.append('<img src="'+options.path+'assets/images/smile/'+TextParser.smiles[i]+'.gif" alt="'+TextParser.smiles[i]+'" onclick="AddSmile(\''+TextParser.smiles[i]+'\')" style="cursor:pointer"> ');

		obj.show();
		$('#shoutbox').hide();
	}
}

function ClearChat()
{
	$("#shoutbox").html('');
}

var lastMessageId = 0;

function initChat ()
{
	var socket = io.connect(server, {query: 'userId='+userId+'&userName='+userName+'&key='+key, secure: true});

	socket.on('connecting', function ()
	{
		ChatMsg(Math.floor(Date.now() / 1000), '', [], 'Соединение...', 0, 0, 0);
	});

	socket.on('connect', function ()
	{
		ChatMsg(Math.floor(Date.now() / 1000), '', [], 'Соединение установлено', 0, 0, 0);

		socket.on('message', function (message)
		{
			if (message['id'] <= lastMessageId)
					return false;

			lastMessageId = message['id'];

			ChatMsg(message['time'], message['user'], message['to'], message['text'], message['private'], message['me'], message['my']);
		});

		$('#chatMsg').on('keypress', function(e)
		{
			if (e.which == '13')
			{
				var msg = addMessage();

				socket.send(encodeURIComponent(msg), userId, userName, color, key);
			}
		});

		$('#send').on('click', function(e)
		{
			e.preventDefault();

			var msg = addMessage();

			if ($("#smiles").is(':visible'))
				ShowSmiles();

			socket.send(encodeURIComponent(msg), userId, userName, color, key);
		});
	});
}