function edToolbar(obj, id)
{
	var str 	= '';
	var count 	= 0;
	var c 		= new Array('00', '33', '66', '99', 'cc', 'ff');
	var buttonColors = new Array(215);

	for (var r = 0; r < 6; r++) {
		for (var g = 0; g < 6; g++) {
			for (var b = 0; b < 6; b++){
					buttonColors[count] = c[r] + c[g] + c[b];
					count++;
			}
		}
	}

    str += ("<div class=\"toolbar\">");
	str += ("<span class=\"gensmall\"><select name=\"btnSize\" onchange=\"doAddTags('[size=' + this.options[this.selectedIndex].value + ']','[/size]','" + obj + "',0); this.selectedIndex = 1;\"><option value=\"9\">Маленький</option><option value=\"11\" selected=\"selected\">Нормальный</option><option value=\"20\">Большой</option><option value=\"25\">Огромный</option></select></span>");
	str += ("<span class='buttons' title='Жирный' onClick=\"doAddTags('[b]','[/b]','" + obj + "',0)\"><span class='sprite bb_text_bold'></span></span>");
    str += ("<span class='buttons' title='Курсив' onClick=\"doAddTags('[i]','[/i]','" + obj + "',0)\"><span class='sprite bb_text_italic'></span></span>");
	str += ("<span class='buttons' title='Подчёркнутый' onClick=\"doAddTags('[u]','[/u]','" + obj + "',0)\"><span class='sprite bb_text_underline'></span></span>");
	str += ("<span class='buttons' title='Зачёркнутый' onClick=\"doAddTags('[s]','[/s]','" + obj + "',0)\"><span class='sprite bb_text_strikethrough'></span></span>");
	str += ("<span class='buttons' title='По центру' onClick=\"doAddTags('[center]','[/center]','" + obj + "',0)\"><span class='sprite bb_text_align_center'></span></span>");
	str += ("<span class='buttons' title='По левому краю' onClick=\"doAddTags('[left]','[/left]','" + obj + "',0)\"><span class='sprite bb_text_align_left'></span></span>");
	str += ("<span class='buttons' title='По правому краю' onClick=\"doAddTags('[right]','[/right]','" + obj + "',0)\"><span class='sprite bb_text_align_right'></span></span>");
	str += ("<span class='buttons' title='По ширине' onClick=\"doAddTags('[justify]','[/justify]','" + obj + "',0)\"><span class='sprite bb_text_align_justify'></span></span>");
	str += ("<span class='buttons' title='Спойлер' onClick=\"doAddTags('[spoiler=]','[/spoiler]','"+obj+"',0)\"><span class='sprite bb_eye'></span></span>");
	str += ("<span class='buttons' title='YOUTUBE' onClick=\"doAddTags('[youtube]','[/youtube]','"+obj+"',2)\"><span class='sprite bb_film_add'></span></span>");
	str += ("<span class='buttons' title='Вставить ссылку' onClick=\"doAddTags('[url]','[/url]','" + obj + "',1)\"><span class='sprite bb_world_link'></span></span>");
	str += ("<span class='buttons' title='Вставить картинку' onClick=\"doAddTags('[img]','[/img]','" + obj + "',3)\"><span class='sprite bb_picture_add'></span></span>");
	str += ("<span class='buttons' title='Вставить песню' onClick=\"doAddTags('[mp3]','[/mp3]','" + obj + "',6)\"><span class='sprite bb_sound_add'></span></span>");
	str += ("<span class='buttons' title='Вставить большую картинку' onClick=\"doAddTags('[img_big]','[/span_big]','" + obj + "',4)\"><span class='sprite bb_image_add'></span></span>");
	str += ("<span class='buttons' title='Нумерованый список' onClick=\"doAddTags('[NUMLIST]','[/NUMLIST]','" + obj + "',5)\"><span class='sprite bb_text_list_numbers'></span></span>");
	str += ("<span class='buttons' title='Список' onClick=\"doAddTags('[LIST]','[/LIST]','" + obj + "',5)\"><span class='sprite bb_text_list_bullets'></span></span>");
	str += ("<span class='buttons' title='Цитата' onClick=\"doAddTags('[quote]','[/quote]','" + obj + "',0)\"><span class='sprite bb_text_signature'></span></span>");
	str += ("<span class='buttons' title='Цитата' onClick=\"doAddTags('[quote author=]','[/quote]','" + obj + "',0)\"><span class='sprite bb_user_comment'></span></span>");
	str += ("<span class='buttons' title='Смайлы' onClick=\"showSmiles('" + obj + "');\"><span class='sprite bb_emoticon_grin'></span></span>");
	str += ("<span class='buttons' title='Цвет текста' onClick=\"ShowHiddenBlock('colorpicker');\"><span class='sprite bb_color_swatch'></span></span>");
	str += ("<span class='buttons' title='Цвет фона' onClick=\"ShowHiddenBlock('colorpicker2');\"><span class='sprite bb_palette'></span></span>");

	str += ("<span class='buttons' title='Предварительный просмотр' onClick=\"show();\"><span class='sprite bb_tick'></span></span>");
    str += ("</div>");

	str += ('<div id="colorpicker" class="colorpicker" style="display:none">');
	for (var clr = 1; clr <= buttonColors.length; clr++) {
		str += ('<span onclick="doAddTags(\'[color=#' + buttonColors[clr-1] + ']\',\'[/color]\',\'' + obj + '\',0)" style="background: #' + buttonColors[clr-1] + '">&nbsp;</span>');
		if(clr%54==0) {
			str += ('<br>');
		}
    }
    str += ('</div>');

	str += ('<div id="colorpicker2" class="colorpicker" style="display:none">');
	for (clr = 1; clr <= buttonColors.length; clr++) {
		str += ('<span onclick="doAddTags(\'[bgcolor=#' + buttonColors[clr-1] + ']\',\'[/bgcolor]\',\'' + obj + '\',0)" style="background: #' + buttonColors[clr-1] + '">&nbsp;</span>');
		if(clr%54==0) {
			str += ('<br>');
		}
    }

    str += ('</div>');

	str += ('<div id="smiles" class="colorpicker" style="display:none"></div>');

	if (id == undefined)
		$('#editor').html(str);
	else
		$('#'+id).html(str);
}

function show()
{
	if ($('#showpanel').is(':visible'))
	{
		$('#showbox').html('');
		$('#showpanel').hide();
	}
	else
	{
		var txt = $('#text').val();
		if (txt != "")
		{
			Text (txt, 'showbox');
			$('#showpanel').show();
		}
	}
}

function showSmiles(obj)
{
	if ($('#smiles').css('display') == 'block') {
		$('#smiles').html('');
		$('#smiles').attr('style', 'display:none');
	} else {
		for (var i = 0; i < sm_repl.length; i++) {
        	$('#smiles').append('<img src="images/smile/'+sm_repl[i]+'.gif" alt="'+sm_repl[i]+'" onclick="AddSmile(\''+sm_repl[i]+'\', \''+obj+'\')" style="cursor:pointer"> ');
		}

		$('#smiles').attr('style', 'display:block');
	}
}

function doAddTags(tag1,tag2,obj,type)
{
	var sel, scrollTop, scrollLeft, rep, url, textarea;

	if ($('#colorpicker').is(':visible'))
		$('#colorpicker').hide();

	if ($('#colorpicker2').is(':visible'))
		$('#colorpicker2').hide();

	textarea = $('#'+obj);

	scrollTop 	= textarea.scrollTop();
	scrollLeft 	= textarea.scrollLeft();

	if (type == 1) {
		url = prompt('Введите ссылку:','');
	} else if (type == 2) {
		url = prompt('Введите ссылку на видео:','');
	} else if (type == 3 || type == 4) {
		url = prompt('Введите ссылку на картинку:','');
	} else if (type == 6) {
		url = prompt('Введите ссылку на песню:','');
	}

	if (type > 0 && type <= 6 && (url == '' || url == null))
    {
			return false;
	}

	if (document.selection)
    {
		textarea.focus();
		sel = document.selection.createRange();
	}
    else
    {
		var len 	= textarea.val().length;
	    var start 	= textarea[0].selectionStart;
		var end 	= textarea[0].selectionEnd;

        sel = textarea.val().substring(start, end);
	}

	if (type == 0) {
		rep = tag1 + sel + tag2;
	} else if (type == 1) {
		if (sel == ""){
			rep = '[url]' + url + '[/url]';
		} else {
			rep = '[url=' + url + ']' + sel + '[/url]';
		}
	} else if (type == 2) {
		rep = '[youtube]'  + url + '[/youtube]';
	} else if (type == 3) {
		rep = '[img]'  + url + '[/img]';
	} else if (type == 4) {
		rep = '[img_big]'  + url + '[/img_big]';
	} else if (type == 5) {
		var list = sel.split('\n');

		for(var i = 0;i < list.length; i++) {
			list[i] = '[*]' + list[i] + '[/*]';
		}

		rep = tag1 + '\n' + list.join("\n") + '\n' +tag2;
	} else if (type == 6) {
		rep = '[mp3]'  + url + '[/mp3]';
	}

	if (document.selection)
    {
		sel.text = rep;
	}
    else
    {
		textarea.val(textarea.val().substring(0,start) + rep + textarea.val().substring(end,len));

		textarea.scrollTop(scrollTop);
		textarea.scrollLeft(scrollLeft);
	}

	return true;
}