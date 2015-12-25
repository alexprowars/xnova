function chatToolbar(obj, id)
{
	var str 	= '';

    str += ("<div class=\"toolbar inline\">");
	str += ("<span class=\"gensmall\"><select name=\"btnSize\" onchange=\"doAddTags('[size=' + this.options[this.selectedIndex].value + ']','[/size]','" + obj + "',0); this.selectedIndex = 1;\"><option value=\"9\">Маленький</option><option value=\"11\" selected=\"selected\">Нормальный</option><option value=\"20\">Большой</option><option value=\"25\">Огромный</option></select></span>");
	str += ("<span class='buttons' title='Жирный' onClick=\"doAddTags('[b]','[/b]','" + obj + "',0)\"><span class='sprite bb_text_bold'></span></span>");
    str += ("<span class='buttons' title='Курсив' onClick=\"doAddTags('[i]','[/i]','" + obj + "',0)\"><span class='sprite bb_text_italic'></span></span>");
	str += ("<span class='buttons' title='Подчёркнутый' onClick=\"doAddTags('[u]','[/u]','" + obj + "',0)\"><span class='sprite bb_text_underline'></span></span>");
	str += ("<span class='buttons' title='Зачёркнутый' onClick=\"doAddTags('[s]','[/s]','" + obj + "',0)\"><span class='sprite bb_text_strikethrough'></span></span>");
	str += ("<span class='buttons' title='По центру' onClick=\"doAddTags('[center]','[/center]','" + obj + "',0)\"><span class='sprite bb_text_align_center'></span></span>");
	str += ("<span class='buttons' title='По левому краю' onClick=\"doAddTags('[left]','[/left]','" + obj + "',0)\"><span class='sprite bb_text_align_left'></span></span>");
	str += ("<span class='buttons' title='По правому краю' onClick=\"doAddTags('[right]','[/right]','" + obj + "',0)\"><span class='sprite bb_text_align_right'></span></span>");
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

var findS = [
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

var replaceS = [
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
	'<a href="$1" target="_blank"><img src="$1" style="max-width:350px;" alt="XNova" /></a>',
	'<a href="$1" target="_blank">$2</a>',
	'<a href="$1" target="_blank">$1</a>',
	'<p>$1</p>',
	'<a href="?set=galaxy&amp;r=3&amp;galaxy=$1&amp;system=$2">[$1:$2:$3]</a>'
];

function showSmilesEx(obj, event)
{
	if ($('#smiles').is(':visible'))
	{
		$('#smiles').html('').hide();
	}
	else
	{
		for (var i = 0; i < sm_repl.length; i++)
		{
        	$('#smiles').append('<img src="'+XNova.path+'images/smile/'+sm_repl[i]+'.gif" alt="'+sm_repl[i]+'" onclick="AddSmile(\''+sm_repl[i]+'\', \''+obj+'\')" style="cursor:pointer"> ');
		}

		$('#smiles').show();
	}

	descendreTchat();
}

function chatResize()
{
	if (isMobile)
		return;

	$('#shoutbox').css('height', $(window).height() - ($('#gamediv').length ? 220 : 144));

	$(window).bind('resize', function()
	{
		$('#shoutbox').css('height', $(window).height() - ($('#gamediv').length ? 220 : 144));

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

var ChatTimer;

function StopChatTimer()
{
	clearTimeout(ChatTimer);
}

function RefreshChat()
{
	StopChatTimer();
	showMessage();

	ChatTimer = setTimeout(RefreshChat, 10000);
}

function MsgSent(msg_id)
{
	StopChatTimer();

	$("#message_id").val(msg_id);

	ChatTimer = setTimeout(showMessage, 5000);
}

function ChatMsg(Time, Player, Msg, Me, My)
{
	var str = "";

	for (var i = 0; i < sm_repl.length; i++)
	{
		Msg = Msg.replace(sm_find[i], '<img src="'+XNova.path+'images/smile/' + sm_repl[i] + '.gif" onclick="S(\'' + sm_repl[i] + '\')" style="cursor:pointer">');
	}

	if (!Time)
		return;

	if (Me > 0)
		str += "<span class='date2' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";
	else if (My > 0)
		str += "<span class='date3' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";
	else
		str += "<span class='date1' onclick='pp(\"" + Player + "\");' style='cursor:pointer;'>";

	if (!Player)
		str += print_date(Time, 1) + "</span> ";
	else
	{
		str += print_date(Time, 1) + "</span> [<span class='username ";

		if (My == 1)
			str += "negative'>";
		else
			str += "to' onclick='to(\"" + Player + "\");' style='cursor:pointer;'>";

		str += Player + "</span>] ";
	}

	for (i in findS)
	{
		Msg = Msg.replace(findS[i], replaceS[i]);
	}

	str += Msg;

	$('#shoutbox').append('<div align="left">' + str + '</div>');

	$('span.username').contextMenu('chatMenu',
	{
		menuStyle: {
			'width': '150px'
		},
		bindings:
		{
			'private': function(e)
			{
				pp($(e).text());
			},
			'message': function (e)
			{
				to($(e).text());
			},
			'mail': function(e)
			{
				showWindow($(e).text()+': отправить сообщение', '?set=messages&mode=write&id='+$(e).text()+'&ajax&popup', 680)
			},
			'info': function (e)
			{
				showWindow('', XNova.path+'?set=players&id='+$(e).text()+'&ajax&popup');
			}
		}
	});

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

	data = data.replace('%', '%25');
	while (data.indexOf('+') >= 0) data = data.replace('+', '%2B');
	while (data.indexOf('#') >= 0) data = data.replace('#', '%23');
	while (data.indexOf('&') >= 0) data = data.replace('&', '%26');
	while (data.indexOf('?') >= 0) data = data.replace('?', '%3F');
	while (data.indexOf('\'') >= 0)data = data.replace('\'', '`');

	$("#chatMsg").val('');

	StopChatTimer();
	HideSmiles();

	$.ajax({
		type: "POST",
		url: XNova.path+"daemon/chat.php",
		data: "msg=" + data + "",
		success: function()
		{
			ChatTimer = setTimeout(showMessage, 500);
		},
		error: function()
		{
			ChatTimer = setTimeout(showMessage, 500);
		}
	});
}

function showMessage()
{
	$.ajax({
		type:"GET",
		url: XNova.path+"daemon/chat.php",
		data:"message_id=" + parseInt($("#message_id").val()) + "",
		dataType: 'script',
		success:function (msg)
		{
			//eval(msg)
		}
	});
}

function S(name)
{
	var msg = $('#chatMsg');

	msg.val(msg.val()+':' + name + ':');
	msg.focus();
}

var sml = 0;

function ShowSmiles()
{
	var str = "";

	if (sml == 1)
	{
		HideSmiles();
		return;
	}

	sml = 1;

	for (var i = 0; i < sm_repl.length; i++)
	{
		str += '<img src="'+XNova.path+'images/smile/' + sm_repl[i] + '.gif" ALT="' + sm_repl[i] + '" onclick="S(\'' + sm_repl[i] + '\')" style="cursor:pointer"> ';
	}

	$('#smiles').html(str).show();
	$('#shoutbox').hide();
}
function HideSmiles()
{
	$('#smiles').html('').attr('style', 'display:none');
}

function ClearChat()
{
	$("#shoutbox").html('');
}

function doSomething(e)
{
	if (!e)
		e = window.event;
	if (e.keyCode == 13)
		addMessage();

	return true;
}

window.document.onkeydown = doSomething;

$(document).ready(function()
{
	setTimeout(showMessage, 1000);
});