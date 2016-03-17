/**
 * Возвращает содержащееся в переданной строке число, вырезая всё, кроме цифр, знака "-" и десятичного разделителя.
 * Если в переданной строке нет ни одного допустимого символа, возвращает пустую строку.
 * @param s строка, содержащая число
 * @param allowNeg флаг - допустимы ли отрицательные значения
 * @param sepCode код символа, выступающего десятичным разделителем
 * @param def значение по умолчанию
 */
function getCorrectedValue (s, allowNeg, sepCode) {
	var sxx = "";
	s += "";
	var sx = s.toUpperCase ();
	var firstDigit = true;
	var firstSeparator = true;
	var signFound = false;
	var nextCharCode = 0;
	for (var i = 0; i < sx.length; i++) {
		nextCharCode = (i == sx.length-1) ? 0 : sx.charCodeAt(i+1);
		if (sx.charCodeAt (i) >= 49 && sx.charCodeAt (i) <= 57) {
			sxx += sx.charAt (i);
			firstDigit = false;
		}
		else if (sx.charCodeAt (i) == 48 && (sx.length == 1 || !firstDigit || nextCharCode == sepCode )) {
			sxx += sx.charAt (i);
			firstDigit = false;
		}
		else if (sx.charCodeAt (i) == 45 && firstDigit && ! signFound)
			signFound = true;
		else if (sx.charCodeAt (i) == sepCode && firstSeparator) {
			sxx += sx.charAt (i);
			firstSeparator = false;
		}
	}
	if (signFound && allowNeg)
		sxx = "-" + sxx;
	return sxx;
}

/**
 * Проверяет содержащееся в input-e число на соответствие заданным ограничениям (допустимость отрицательных значений, значений с плавающей точкой).
 * Input, для которого вызывается функция, берётся из контекста через this.
 * @param event Данные о событии. Поле event.data может содержать имя функции, которую нужно вызвать по завершению проверки.
 */
function validateInputNumber (event) {
	var input = event.currentTarget;
	var allowNeg = getConstraint(input, 'allowNegative', false);
	var decimalSeparator = getOptionValue('decimalSeparator', '.');
	// Если в поле можно вводить значения с плавающей точкой, то код десятичного разделителя берём из настроек, иначе примем его равным -1, чтобы посимвольное сравнение не приняло его за допустимый символ.
	var sepCode = getConstraint(input, 'allowFloat', false) ? decimalSeparator.charCodeAt(0) : -1;
	if (input.value.charAt(0) == decimalSeparator) {
		input.value = '0' + input.value;
	}
	if (input.value != getCorrectedValue (input.value, allowNeg, sepCode)) {
		input.value = getCorrectedValue (input.value, allowNeg, sepCode);
	}
	if (input.value == '') {
		input.value = getConstraint(input, 'def', 0);
	}
	// После проверки надо вызвать функцию, имя которой передано в свойствах события. На всякий случай вызовем её в контексте обрабатываемого поля ввода
	if (event != null && event.data != null)
		eval(event.data).apply(input);
}

/**
 * Проверяет содержащееся в input-e число на соответствие заданным ограничениям (допустимость отрицательных значений, значений с плавающей точкой,
 *  минимум/максимум) при потере полем фокуса.
 * Input, для которого вызывается функция, берётся из контекста через this.
 * При изменении значений, нарушающих ограничения мин/макс, показывается сообщение. id элементов для этого берутся из options.
 * @param event Данные о событии. Поле event.data может содержать имя функции, которую нужно вызвать по завершению проверки.
 */
function validateInputNumberOnBlur (event) {
	validateInputNumber(event);
	var needRecalc = false;
	var input = event.currentTarget;
	if (input.value == '-') {
		input.value = '0';
		needRecalc = true;
	}
	var decimalSeparator = getOptionValue('decimalSeparator', '.');
	if (input.value.charAt(input.value.length - 1) == decimalSeparator) {
		input.value += '0';
		needRecalc = true;
	}
	var value = input.value.replace(decimalSeparator, '.');
	value = parseFloat(value);
	var minConstr = getConstraint(input, 'min', null);
	if (minConstr != null && value < minConstr) {
		// Если известны div-ы и текст для сообщения об ошибке, выведем туда это сообщение, а потом исправим значение
		if (getOptionValue('warnindDivId', null) != null && getOptionValue('msgMinConstraintViolated', null) != null) {
			// В атрибуте alt ожидаем увидеть локализованное название поля. Если его там нет, то в сообщении об ошибке указанаия на поле не будет.
			var fieldTitle = input.alt;
			var fieldHint = '';
			if (fieldTitle != '' && (getOptionValue('fieldHint', null) != null))
				fieldHint = getOptionValue('fieldHint', null).format(fieldTitle);
			$('#'+options.warnindMsgDivId).text(options.msgMinConstraintViolated.format(fieldHint, this.value, minConstr));
			$('#'+options.warnindDivId).fadeIn(800, function () {
				setTimeout(function() {
					$('#'+options.warnindDivId).fadeOut(800);
				}, 5000);
			  });
		}
		// Устанавливая изменённое значение, на всякий случай удостоверимся, что десятичный разделитель будет правильный
		input.value = (minConstr+'').replace('.', decimalSeparator);
		needRecalc = true;
	}
	var maxConstr = getConstraint(input, 'max', null);
	if (maxConstr != null && value > maxConstr) {
		// Если известны div-ы и текст для сообщения об ошибке, выведем туда это сообщение, а потом исправим значение
		if (getOptionValue('warnindDivId', null) != null && getOptionValue('msgMaxConstraintViolated', null) != null) {
			var fieldTitle = input.alt;
			var fieldHint = '';
			if (fieldTitle != '' && (getOptionValue('fieldHint', null) != null))
				fieldHint = getOptionValue('fieldHint', null).format(fieldTitle);
			$('#'+options.warnindMsgDivId).text(options.msgMaxConstraintViolated.format(fieldHint, this.value, maxConstr));
			$('#'+options.warnindDivId).fadeIn(800, function () {
				setTimeout(function() {
					$('#'+options.warnindDivId).fadeOut(800);
				}, 5000);
			  });
		}
		input.value = (maxConstr+'').replace('.', decimalSeparator);;
		needRecalc = true;
	}
	// Если что-то изменили - надо вызвать функцию, имя которой передано в свойствах события. На всякий случай вызовем её в контексте обрабатываемого поля ввода
	if (needRecalc && event != null && event.data != null)
		eval(event.data).apply(input);
}

/**
 * Проверяет, что num является числом и попадает в заданый диапазон. Если все ок, возвращается само число, иначе значение по умолчанию.
 * @param num проверяемое число
 * @param min минимальное допустимое значение
 * @param max макисимальное допустимое значение
 * @param def значение по умолчанию
 */
function validateNumber(num, min, max, def) {
	return (!isNaN(num) && num >= min && num <= max) ? num : def;
}

/**
 * Форматирует число в стиле ОГейма - проставляя точку в качестве разделителя тысяч.
 */
function numToOGame(n) {
	n += '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(n)) {
		n = n.replace(rgx, '$1' + '.' + '$2');
	}
	return n;
}

/**
 * Обрезает число до диапазона [min, max]. Если число выходит за одну из границ диапазона, то оно обрезается по этой границе.
 * @param n исходное число
 * @param min минимальное допустимое значение числа
 * @param max максимальное допустимое значение числа
 * @return вписанное в указанный диапазон значение
 */
function clampNumber(n, min, max) {
	if (n > max)
		n = max;
	else if (n < min)
		n = min;
	return n;
}

/**
 * Возвращает div со списком элементов, принадлежащих переданному.
 */
function debugElement(el) {
	var dbg = $('<div></div>');
	$.each(el, function(index, value) {
		dbg.append($('<div></div>').append($('<span></span>').css('color', 'blue').text(index)).append(': ' + value));
	});
	return dbg;
}

/**
 * Добавляет строку str к содержимому элемента #debug.
 */
function debugOutput(str) {
	$('#debug').append($('<div>' + str + '</div>'));
}

/**
 * Выводит строку str в отладочную консоль.
 */
function consoleLog(str) {
	if (typeof console != 'undefined') console.log(str);
}

/**
 * Проверяет, что значение существует.
 */
function isset(e) {
	return typeof(e) != 'undefined';
}

/**
 * Парсит значение числа из указанного элемента input
 * @param input
 */
function getInputNumber(input) {
	var decimalSeparator = getOptionValue('decimalSeparator', '.');
	var n = parseFloat(input.value.replace(decimalSeparator, '.'));
	return isNaN(n) ? 0 : n;
}

/**
 * Заменяет в строке вхождения вида {n} на элементы массива аргументов функции.
 */
String.prototype.format = function(){
    var pattern = /\{\d+\}/g;
    var args = arguments;
    return this.replace(pattern, function(capture){ return args[capture.match(/\d+/)]; });
};

/**
 * Возвращает значение из массива options или значение по умолчанию, если элемент options.opt не найден.
 * @param opt ключ для массива options
 * @param def значение по умолчанию
 */
function getOptionValue(opt, def) {
	if (typeof(options[opt]) === 'undefined')
		return def;
	else
		return options[opt];
}

/**
 * Возвращает ограничение, установленное для поля, или значение по умолчанию, если на самом поле и в массиве options такого ограничения нет.
 * @param element id поля, для которого запрашивается ограничение
 * @param constr название ограничения
 * @param def значение по умолчанию
 */
function getConstraint(element, constr, def) {
	var constraints = $(element).data('constrains');
	// Если не найдём ограничения в свойствах самого поля, поробуем вязть из options - если и там нет, вернём значение по умолчанию
	if (typeof(constraints) === 'undefined') {
		if (typeof(options.defConstraints) === 'undefined')
			return def;
		else
			return options.defConstraints[constr];
	} else {
		return (typeof(constraints[constr]) === 'undefined') ? def : constraints[constr];
	}
}

/**
 * Формирует строковое представление для промежутка времени. Если какого-то элемента (нед, д, ч, м, с) нет, он не включается в возвращаемую строку.
 * @param seconds Кол-во секунд в промежутке времени
 * @param w Обозначение недель
 * @param d Обозначение дней
 * @param h Обозначение часов
 * @param m Обозначение минут
 * @param s Обозначение секунд
 * @returns Строка вида [Xw] [Xd] [Xh] [Xm] [Xs]
 */
function timespanToShortenedString(seconds, w, d, h, m, s, minimize) {
	if (seconds == 0)
		return '0'+s;
	var timeStr = '';
	var haveWeeks = false, haveDays = false;
	if (seconds >= 604800) {
		timeStr += dropFraction(Math.floor(seconds / 604800), 3);
		timeStr += w+' ';
		seconds = seconds % 604800;
		haveWeeks = true;
	}
	if (seconds >= 86400 || timeStr.length > 0) {
		if (seconds / 86400 >= 1) {
			timeStr += dropFraction(Math.floor(seconds / 86400), 3);
			timeStr += d+' ';
		}
		seconds = seconds % 86400;
		haveDays = true;
	}
	if (seconds >= 3600 || timeStr.length > 0) {
		if (seconds / 3600 >= 1) {
			timeStr += dropFraction(Math.floor(seconds / 3600), 3);
			timeStr += h+' ';
		}
		seconds = seconds % 3600;
	}
	// Если есть недели, и запрошена минимизация - минуты отбрасываем
	if (minimize && haveWeeks)
		return timeStr;
	if (seconds >= 60 || timeStr.length > 0) {
		if (seconds / 60 >= 1) {
			timeStr += dropFraction(Math.floor(seconds / 60), 3);
			timeStr += m+' ';
		}
		seconds = seconds % 60;
	}
	// Если есть дни, и запрошена минимизация - секунды отбрасываем
	if (minimize && haveDays)
		return timeStr;
	if (seconds > 0) {
		timeStr += Math.floor(seconds);
		timeStr += s;
	}
	return timeStr;
}

function numberToShortenedString(number, suffixes) {
	var value = 0, suff = '';
	value = number;
	if (number >= 1000000000) {
		value = 0.001 * Math.floor(value / 1000000.0);
		suff = suffixes.substr(2, 1);
	} else if (number >= 1000000) {
		value = 0.001 * Math.floor(value / 1000.0);
		suff = suffixes.substr(1, 1);
	}
	value = dropFraction(value, 3);
	return numToOGame(value)+suff;
}

function dropFraction(number, positions) {
	var value = number;
	var parts = (number+'').split(/\./);
	if (parts.length > 1 && parts[1].length > positions) {
		var frac = parts[1].substr(0, positions);
		value = parts[0] + '.' + frac;
		if (parts[1].indexOf('e') > 0){
			var fracParts = parts[1].split(/e/);
			value += 'e'+fracParts[1];
		}
	}
	return value;
}

/**
 * Дополняет строку до указанной длины
 * @param input Входная строка
 * @param pad_length Требуемая длина строки
 * @param pad_string Строка, используемая для дополнения
 * @param pad_type Направление - справа, слева, с обеих сторон. Одна из констант 'STR_PAD_LEFT', 'STR_PAD_RIGHT', 'STR_PAD_BOTH'
 * @returns Изменённая строка
 */
function strPad(input, pad_length, pad_string, pad_type) {
	var half = '', pad_to_go;
	input += '';
	var str_pad_repeater = function(s, len){
			var collect = '', i;

			while(collect.length < len) collect += s;
			collect = collect.substr(0,len);

			return collect;
		};
	if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }
	if ((pad_length - input.length) > 0) {
		pad_to_go = pad_length - input.length;
		if (pad_type == 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }
		else if (pad_type == 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
		else if (pad_type == 'STR_PAD_BOTH') {
			half = str_pad_repeater(pad_string, Math.ceil(pad_to_go/2));
			input = half + input + half;
			input = input.substr(0, pad_length);
		}
	}
	return input;
}

function dayOfMonth(day, month, year) {
	var days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if (year % 4 == 0)
		days[1] = 29;
	if (day > days[month-1])
		return false;
	else
		return true;
}

/**
 * Парсит дату/время из строки. Заточено для поля inputmask с определениями 'm.d.y H:s:s' и 'd.m.y H:s:s'
 * @param str Содержимое поля, полученное методом inputmask('unmaskedvalue')
 * @param template Определение даты
 * @returns Кол-во миллисекунд с начала эпохи (результат работы Date.parse() на обработанной строке)
 */
function parseDate(str, template) {
	// Поскольку у нас в inputmask используются только два определения даты - 'm.d.y H:s:s' и 'd.m.y H:s:s',
	// достаточно сравнить переданный шаблон с эталоном и определиться, как парсить дату
	// Метод inputmask('unmaskedvalue') возвращает содержимое то в виде "ddmmyyyyhhmmss", то "dd.mm.yyyy hh:mm:ss". Регекспы надо использовать соответствующие
	var rgx1 = /(\d{2})(\d{2})(\d{4})(\d{2})(\d{2})(\d{2})/;
	var rgx2 = /(\d{2})\.(\d{2})\.(\d{4})\s(\d{2}):(\d{2}):(\d{2})/;
	var pts;
	if (str.search(/\./)>0) {
		pts = str.match(rgx2);
	}
	else {
		pts = str.match(rgx1);
	}
	if (pts == null){
		return 0;
	}
	var t;
	// Распарсим дату/время, расположив элементы на нужных позициях. Если сочетание день+месяц неадекватное, считаем, что дата не распарсилась.
	if (template == 'm.d.y H:s:s') {
		t = Date.parse(pts[1] + "/" + pts[2] + "/" + pts[3] + " " + pts[4] + ":" + pts[5]  + ":" + pts[6]);
		if (!dayOfMonth(pts[2], pts[1], pts[3]))
			t = 0;
	}
	else {
		t = Date.parse(pts[2] + "/" + pts[1] + "/" + pts[3] + " " + pts[4] + ":" + pts[5]  + ":" + pts[6]);
		if (!dayOfMonth(pts[1], pts[2], pts[3]))
			t = 0;
	}
	return t;
}

/**
 * Формирует строку с датой/временем
 * @param time Кол-во миллисекунд с начала эпохи
 * @param template Определение даты для поля inputmask ('m.d.y H:s:s' или 'd.m.y H:s:s')
 * @returns Строковое представление даты с нужным порядком элементов
 */
function getDateStr(time, template) {
	if (time == 0)
		return '';
	// Поскольку у нас в inputmask используются только два определения даты - 'm.d.y H:s:s' и 'd.m.y H:s:s',
	// достаточно сравнить переданный шаблон с эталоном и определиться, как формировать дату
	var date = new Date();
	date.setTime(time);
	var year = date.getFullYear();
	var month = strPad(date.getMonth() + 1, 2, '0', 'STR_PAD_LEFT');
	var day = strPad(date.getDate(), 2, '0', 'STR_PAD_LEFT');
	var hours = strPad(date.getHours(), 2, '0', 'STR_PAD_LEFT');
	var minutes = strPad(date.getMinutes(), 2, '0', 'STR_PAD_LEFT');
	var seconds = strPad(date.getSeconds(), 2, '0', 'STR_PAD_LEFT');
	if (template == 'm.d.y H:s:s')
		return month+'.'+day+'.'+year+' '+hours+':'+minutes+':'+seconds;
	else
		return day+'.'+month+'.'+year+' '+hours+':'+minutes+':'+seconds;
}

/**
 * Формирует строку с временем в формате ЧЧ:ММ
 * @param time Кол-во секунд
 * @returns Строковое представление времени по формату H:s 
 */
function getTimeStr(time) {
	var date = new Date();
	date.setTime(0);
	date.setSeconds(time, 0);
	var hours = strPad(date.getUTCHours(), 2, '0', 'STR_PAD_LEFT');
	var minutes = strPad(date.getUTCMinutes(), 2, '0', 'STR_PAD_LEFT');
	return hours+':'+minutes;
}

/**
 * Сохраняет поля переданного объекта в куке с именем name.
 * Сохраняются пары "ключ;значение", разделённые запятыми. Если поле объекта - массив, ключ имеет вид "property|index1|index2". Функции игнорируютя. 
 * @param name - имя куки, в которую будут сохранены данные
 * @param data - объект, свойства (поля) которого требуется сохранить в куку
 */
function saveToCookie(name, data) {
	var saveStr = 'key-value;true,';
	$.each(data, function(key, value) {
			if (jQuery.type(data[key]) == 'function') {
				return;
			}
			if (jQuery.type(data[key]) == 'array') {
				var arr = data[key];
				for (var i = 0; i < arr.length; i++) {
					if (jQuery.type(arr[i]) == 'array') {
						var row = arr[i];
						for (var j = 0; j < row.length; j++) {
							saveStr += key+'|'+i+'|'+j+';'+row[j]+',';
						}
					}
					else {
						saveStr += key+'|'+i+';'+arr[i]+',';
					}
				}
				return;
			}
			saveStr += key+';'+value+',';
		}
	);
	saveStr = saveStr.substring(0, saveStr.length-1); // последний символ - запятая, она не нужна
	$.cookie(name, saveStr, { expires: 365, path: '/' });
}

/**
 * Загружает из куки с именем name данные и складывает их в объект params.
 * В куке ожидаются пары "ключ;значение", разделённые запятыми. Если целевое поле объекта - массив, ключ должен иметь вид "property|index1|index2". Максимальная размерность массива - 2.
 * Целевой объект должен содержать функцию validate, принимающую имя целевого поля объекта и значение-кандидат, и возвращающую проверенное значение, которое можно записывать в поле.
 * Если в куке отстутствует подстрока "key-value", загрузка не производится. 
 * @param - имя куки, из которой будут загружены данные
 * @param params - объект, свойства (поля) которого требуется загрузить из куки
 */
function loadFromCookie(name, params) {
	var data = $.cookie(name);
	if (!data || data.indexOf('key-value') == -1)
		return;
	var strings = data.split(',');
	$.each(strings, function(key, value) {
			var parts = value.split(';');
			if (parts[0].indexOf('|') > 0) {
				var arrparts = parts[0].split('|');
				if (!arrparts[0] in params)
					return;
				if (arrparts.length == 2) {
					params[arrparts[0]][arrparts[1]] = params.validate(arrparts[0], parts[1]);
				}
				if (arrparts.length == 3) {
					params[arrparts[0]][arrparts[1]][arrparts[2]] = params.validate(arrparts[0], parts[1]);
				}
				return;
			}
			else {
				if (parts[0] in params) {
					params[parts[0]] = params.validate(parts[0], parts[1]);
				}
			}
		}
	);
}