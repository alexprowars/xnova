function addTagToElement (openTag, closeTag, type, element)
{
	var sel, scrollTop, scrollLeft, rep, url;

	element = $(element);

	scrollTop 	= element.scrollTop();
	scrollLeft 	= element.scrollLeft();

	if (type === 1)
		url = prompt('Введите ссылку:','');
	else if (type === 2)
		url = prompt('Введите ссылку на видео:','');
	else if (type === 3 || type === 4)
		url = prompt('Введите ссылку на картинку:','');
	else if (type === 6)
		url = prompt('Введите ссылку на песню:','');

	if (type > 0 && type <= 6 && (url === '' || url === null))
		return;

	if (document.selection)
    {
		element.focus();
		sel = document.selection.createRange();
	}
    else
    {
		var len 	= element.val().length;
	    var start 	= element[0].selectionStart;
		var end 	= element[0].selectionEnd;

        sel = element.val().substring(start, end);
	}

	if (type === 0)
		rep = openTag + sel + closeTag;
	else if (type === 1)
	{
		if (sel === "")
			rep = '[url]' + url + '[/url]';
		else
			rep = '[url=' + url + ']' + sel + '[/url]';
	}
	else if (type === 2)
		rep = '[youtube]'  + url + '[/youtube]';
	else if (type === 3)
		rep = '[img]'  + url + '[/img]';
	else if (type === 4)
		rep = '[img_big]'  + url + '[/img_big]';
	else if (type === 5)
	{
		var list = sel.split('\n');

		for (var i = 0;i < list.length; i++)
			list[i] = '[*]' + list[i] + '[/*]';

		rep = openTag + '\n' + list.join("\n") + '\n' +closeTag;
	}
	else if (type === 6)
		rep = '[mp3]'  + url + '[/mp3]';

	if (!document.selection)
    {
		element.scrollTop(scrollTop);
		element.scrollLeft(scrollLeft);
	}

	return element.val().substring(0, start) + rep + element.val().substring(end, len);
}