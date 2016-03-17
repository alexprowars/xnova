var options = {
	//	сокращение, скорость, тип двигателя, потр.дейтерия, грузоподъёмность
	shipsData: [
					['small-cargo', 5000, 0, 10, 5000],
					['large-cargo', 7500, 0, 50, 25000],
					['light-fighter', 12500, 0, 20, 50],
					['heavy-fighter', 10000, 1, 75, 100],
					['cruiser', 15000, 1, 300, 800],
					['battleship', 10000, 2, 500, 1500],
					['colony-ship', 2500, 1, 1000, 7500],
					['recycler', 2000, 0, 300, 20000],
					['esp-probe', 100000000, 0, 1, 5],
					['bomber', 4000, 1, 1000, 500],
					['destroyer', 5000, 2, 1000, 2000],
					['death-star', 100, 2, 1, 1000000],
					['battlecruiser', 10000, 2, 250, 750],

					['fly_base', 4500, 2, 40, 40000],
					['corvete', 12500, 1, 250, 800],
					['interceptor', 17000, 1, 330, 600],
					['dreadnought', 10000, 1, 700, 1800],
					['corsair', 10000, 1, 50, 500]
				],
	driveBonuses: [0, 0, 0],

	defConstraints: {
				min: null,
				max: null,
				def: 0,
				allowFloat: false,
				allowNegative: false
			},
	
	prm: {
		driveLevels: [0, 0, 0],
		uniSpeed: 1,
		departure: [1, 1, 1],
		destination: [1, 1, 1],
		ships: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
		startDT: 0,
		saveStartDT: 0,
		saveReturnDT: 0,
		saveTolerance: 0,
		mode: 0,
		flightData: [0],
		
		validate: function(field, value) {
			switch (field) {
				case 'driveLevels': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'uniSpeed': return validateNumber(parseFloat(value), 1, 10, 1);
				case 'departure': return validateNumber(parseFloat(value), 1, 1000, 1);
				case 'destination': return validateNumber(parseFloat(value), 1, 1000, 1);
				case 'ships': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'startDT': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'saveStartDT': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'saveReturnDT': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'saveTolerance': return validateNumber(parseFloat(value), 0, Infinity, 0);
				case 'mode': return validateNumber(parseFloat(value), 0, 1, 0);
				case 'flightData': return validateNumber(parseFloat(value), -Infinity, Infinity, 0);
				default: return value;
			}
		}
	},

	load: function() {
		try {
			loadFromCookie('options_flight', options.prm);
		} catch(e) {
			alert(e);
		}
	},

	save: function() {
		saveToCookie('options_flight', options.prm);
	}
};

function getMinSpeed() {
	var minSpeed = Infinity;
	for(i = 0; i < options.shipsData.length; i++)
	{
		// в shipsData[i][0] у нас сокращение - оно же имя поля для ввода количества кораблей
		var shipCount = getInputNumber($('#'+options.shipsData[i][0])[0]);
		var shipSpeed = options.shipsData[i][1] * (1 + (options.driveBonuses[options.shipsData[i][2]]/100));

		var bonus = 0;

		if ($('#officier').is(':checked'))
			bonus += 0.25;
		if ($('#race').is(':checked'))
			bonus += 0.10;

		if (bonus > 0)
			shipSpeed += Math.round(shipSpeed * bonus);

		// попутно с вычислением скорости самого медленного корабля во флоте выведем значения скорости всех кораблей
		$('#'+options.shipsData[i][0]+'-speed').text(numToOGame(Math.round(shipSpeed)));
		if(shipCount > 0 && shipSpeed > 0 && !isNaN(shipSpeed))
		{
			minSpeed = Math.min(minSpeed, shipSpeed);
		}
	}
	return minSpeed;
}

function getDistance(departure, destination) {
	dst = 0;
	if ((departure[0] - destination[0]) != 0) {
		dst = Math.abs(departure[0] - destination[0]) * 20000;
	} else if ((departure[1] - destination[1]) != 0) {
		dst = Math.abs(departure[1] - destination[1]) * 95 + 2700;
	} else if ((departure[2] - destination[2]) != 0) {
		dst = Math.abs(departure[2] - destination[2]) * 5 + 1000;
	} else {
		dst = 5;
	}
	return dst;
}

function getFlightDuration(minSpeed, distance, speedPercent, uniSpeedFactor) {
	return Math.round(((35000 / (speedPercent / 10) * Math.sqrt(distance * 10 / minSpeed) + 10) / uniSpeedFactor ));
}

function getDeutConsumption(minSpeed, distance, duration, speedPercent, uniSpeedFactor) {
	var totalConsumption = 0;
	var shipConsumption = 0;
	var i;
    for(i = 0; i < options.shipsData.length; i++)
    {
        var shipsCount = getInputNumber($('#'+options.shipsData[i][0])[0]);
        options.prm.ships[i] = shipsCount;
		if (shipsCount > 0)
		{
	        var baseShipSpeed = (options.shipsData[i][1] * (1 + (options.driveBonuses[options.shipsData[i][2]]/100)));
	        var shipSpeedValue =  35000 / (duration * uniSpeedFactor - 10) * Math.sqrt(distance * 10 / baseShipSpeed);
	        shipConsumption = options.shipsData[i][3]* shipsCount;
	        totalConsumption += shipConsumption * distance / 35000 * ((shipSpeedValue / 10) + 1) * ((shipSpeedValue / 10) + 1);
		}
	}
    totalConsumption = Math.round(totalConsumption) + 1;
	return totalConsumption;
}

function getCargoCapacity(minSpeed, distance, duration, speedPercent, consumption, uniSpeedFactor) {
	var capacity = 0;
	var unusedProbeStorage = 0;
	var probeConsumption = 0;

	var probesCount =  getInputNumber($('#'+options.shipsData[8][0])[0]);

	if (probesCount > 0) {
		var speed = (options.shipsData[8][1] * (1 + (options.driveBonuses[options.shipsData[8][2]]/100)));
        var speedValue =  35000 / (duration * uniSpeedFactor - 10) * Math.sqrt(distance * 10 / speed);
        var baseConsumption = options.shipsData[8][3]* probesCount;
        probeConsumption += baseConsumption * distance / 35000 * ((speedValue / 10) + 1) * ((speedValue / 10) + 1);
	}
	probeConsumption = Math.round(probeConsumption) + 1;

	capacity = options.shipsData[8][4] * probesCount;
	var cap =  capacity - probeConsumption;
	if (cap > 0)
		unusedProbeStorage = cap;
	else
		unusedProbeStorage = 0;

	capacity = 0;
	var i;
    for(i = 0; i < options.shipsData.length; i++)
    {
        var shipCount = getInputNumber($('#'+options.shipsData[i][0])[0]);
		if (shipCount > 0)
			capacity += shipCount*options.shipsData[i][4];
	}

    capacity -= (consumption + unusedProbeStorage);
	return capacity ;
}

function checkCoordinates(point) {
	options.prm[point][0] = getInputNumber($('#'+point+'-g')[0]);
	options.prm[point][1] = getInputNumber($('#'+point+'-s')[0]);
	options.prm[point][2] = getInputNumber($('#'+point+'-p')[0]);
	if (options.prm[point][0] <=0 || options.prm[point][0] > getConstraint($('#'+point+'-g')[0], 'max', Infinity))
		return false;
	if (options.prm[point][1] <=0 || options.prm[point][1] > getConstraint($('#'+point+'-s')[0], 'max', Infinity))
		return false;
	if (options.prm[point][2] <=0 || options.prm[point][2] > getConstraint($('#'+point+'-p')[0], 'max', Infinity))
		return false;
	return true;
}

function validateDateField(id) {
	if ($('#'+id)[0].value.search('_') >= 0 || parseDate($('#'+id)[0].value, options.datetimeFormat) == 0) {
		// Если в поле вообще пусто, не будем раздражать пользователя красной рамкой.
		if ($('#'+id)[0].value == '' || $('#'+id)[0].value == '__.__.____ __:__:__') {
			$('#'+id).removeClass('ui-state-error').addClass('ui-state-default');
		} else {
			$('#'+id).removeClass('ui-state-default').addClass('ui-state-error');
		}
		return false;
	} else {
		// Если дата в поле парсится нормально, можно гарантированно присваивать соответствующий класс
		$('#'+id).removeClass('ui-state-error').addClass('ui-state-default');
		return true;
	}
}

function clearFlightTimesTable() {
	var ftTable = $('#flight-times tr');
	for (i=1; i<=10; i++) {
		$(ftTable[i].children[1]).html('');
		$(ftTable[i].children[2]).html('');
		$(ftTable[i].children[3]).html('');
		//ftTable[i].children[4].children[0].hidden = true;
	}
}

function resetParams() {
	options.prm.driveLevels = [0, 0, 0];
	options.prm.uniSpeed = 1;
	options.prm.departure = [1, 1, 1];
	options.prm.destination = [1, 1, 1];
	options.prm.ships = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	options.prm.startDT = 0;
	options.prm.saveStartDT = 0;
	options.prm.saveReturnDT = 0;
	options.prm.saveTolerance = 0;
	
	$('#cmb-drive').val(options.prm.driveLevels[0]);
	$('#imp-drive').val(options.prm.driveLevels[1]);
	$('#hyp-drive').val(options.prm.driveLevels[2]);
	$('#universe-speed').val(options.prm.uniSpeed);
	$('#departure-g').val(options.prm.departure[0]);
	$('#departure-s').val(options.prm.departure[1]);
	$('#departure-p').val(options.prm.departure[2]);
	$('#destination-g').val(options.prm.destination[0]);
	$('#destination-s').val(options.prm.destination[1]);
	$('#destination-p').val(options.prm.destination[2]);
	for (i = 0; i < options.shipsData.length; i++){
		$('#'+options.shipsData[i][0]).val(options.prm.ships[i]);
	}
	$('#start-datetime').val(options.prm.startDT);
	var rows = $('#flight-data tr');
	for(var i = rows.length-1; i >= 0; i--) {
		removeFlightRow.apply(rows[i].children[2].children[0]);
	}
	//$('#save-start-datetime').val(options.prm.saveStartDT);
	//$('#save-return-datetime').val(options.prm.saveReturnDT);
	//$('#save-tolerance-time').val(options.prm.saveTolerance);

	updateNumbers();
	//updateArrival();
}

function updateNumbers() {
	options.prm.driveLevels[0] = getInputNumber($('#cmb-drive')[0]);
	options.prm.driveLevels[1] = getInputNumber($('#imp-drive')[0]);
	options.prm.driveLevels[2] = getInputNumber($('#hyp-drive')[0]);
	options.prm.uniSpeed = $('#universe-speed')[0].value;
	options.driveBonuses[0] = options.prm.driveLevels[0] * 10;
	options.driveBonuses[1] = options.prm.driveLevels[1] * 20;
	options.driveBonuses[2] = options.prm.driveLevels[2] * 30;
	// при изменении уровня движков кое-что меняется у МТ и бомбера
	if (options.prm.driveLevels[1] > 4)
		options.shipsData[0] = ['small-cargo', 10000, 1, 20,5000];
	else
		options.shipsData[0] = ['small-cargo', 5000, 0, 10, 5000];
	if (options.prm.driveLevels[2] > 7)
		options.shipsData[9] = ['bomber', 5000, 2, 1000, 500];
	else
		options.shipsData[9] = ['bomber', 4000, 1, 1000, 500];

	if (!checkCoordinates('departure') || !checkCoordinates('destination')) {
		$('#distance').text('-');
		clearFlightTimesTable();
		options.save();
		return;
	}
	var distance = getDistance(options.prm.departure, options.prm.destination);
	$('#distance').text(numToOGame(distance));

	var minSpeed = getMinSpeed();
	if (minSpeed === Infinity) {
		clearFlightTimesTable();
		options.save();
		return;
	}

	var ftTable = $('#flight-times tr');
	for (i=100; i>0; i-=10) {
		var dur = getFlightDuration(minSpeed, distance, i, options.prm.uniSpeed);
		var durStr = timespanToShortenedString(dur, options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS);
		var row = 1 + (100-i)/10;
		$(ftTable[row].children[1]).html(durStr);
		var cons = getDeutConsumption(minSpeed, distance, dur, i, options.prm.uniSpeed);
		$(ftTable[row].children[2]).html(numToOGame(cons));
		var cap = getCargoCapacity(minSpeed, distance, dur, i, cons, options.prm.uniSpeed);
		$(ftTable[row].children[3]).html(numToOGame(cap));
	}

	options.save();
}

function setDepartureNow() {
	options.prm.startDT = (new Date()).getTime();
	$('#start-datetime')[0].value = getDateStr(options.prm.startDT, options.datetimeFormat);	
	updateArrival();
}

function setSaveDepartureNow() {
	options.prm.saveStartDT = (new Date()).getTime();
	$('#save-start-datetime')[0].value = getDateStr(options.prm.saveStartDT, options.datetimeFormat); 
	options.save();
}

function setDepartureZero() {
	var d = new Date();
	d.setHours(0);
	d.setMinutes(0);
	d.setSeconds(0);
	d.setMilliseconds(0);
	options.prm.startDT = d.getTime();
	$('#start-datetime')[0].value = getDateStr(options.prm.startDT, options.datetimeFormat); 
	updateArrival();
}

function getSecondsFromTimeField(text) {
	var emptyMask = "__ __:__:__";
	if (text.length == 0 || text == emptyMask)
		return 0;

	var rgx = /(\d\d) (\d\d):(\d\d):(\d\d)/;
	var parts = text.match(rgx);

	if (parts == null || parts.length != 5)
		return -1;

	var result = parts[1] * 24 * 3600;	// дни
	var tmpI = parts[2]; // часы
	if (tmpI <= 23) {
		result += tmpI * 3600;
	} else
		return -1;
	tmpI = parts[3]; // минуты
	if (tmpI <= 59) {
		result += tmpI * 60;
	} else
		return -1;
	tmpI = parts[4]; // секунды
	if (tmpI <= 59) {
		result += 1*tmpI;
	} else
		return -1;
	return result;
}

function updateArrival() {
	// Содержимое полей, определяющих момент старта и длительность полёта, проверяется регулярным выражением.
	// Невалидное значение там может быть только если поле содержит placeholder.
	var startDT = $('#start-datetime').inputmask('unmaskedvalue');
	var showResult = validateDateField('start-datetime');
	var t = parseDate(startDT, options.datetimeFormat);
	options.prm.startDT = t;
	while (options.prm.flightData.length > 0)
		options.prm.flightData.pop();

	var rows = $('#flight-data tr');
	var tmp = 0, sign = 1;
	for (var i=0; i<rows.length; i++){
		var elem = rows[i].children[1].children[0].children[0];
		tmp = getSecondsFromTimeField(elem.value);
		// если значение в поле корректное, добавим его к итогу и покажем, что с полем всё ок. иначе - обведём красной рамкой
		if (tmp >= 0) {
			// Надо подглядеть, что у нас там за знак рядом с этим полем
			sign = $(rows[i].children[0].children[0].children[0]).hasClass('ui-icon-plus') ? 1 : -1;
			options.prm.flightData.push(sign*tmp);
			//console.log(sign*tmp);
			t +=  sign*tmp*1000; // Функция getDateStr() принимает значения в миллисекундах - конвертируем.
			$(elem).removeClass('ui-state-error').addClass('ui-state-default');
		} else {
			$(elem).removeClass('ui-state-default').addClass('ui-state-error');
		}
	}

	if (showResult)
		$('#arrival-moment').text(getDateStr(t, options.datetimeFormat));
	else
		$('#arrival-moment').text('?');
	
	options.save();
}

function getFlightTimeStr(seconds) {
	if (seconds < 0)
		return '';
	var d = 0, h = 0, m = 0, s = 0;
	d = strPad(Math.floor(seconds / 86400), 2, '0', 'STR_PAD_LEFT') ;
	seconds = seconds % 86400;
	h = strPad(Math.floor(seconds / 3600), 2, '0', 'STR_PAD_LEFT') ;
	seconds = seconds % 3600;
	m = strPad(Math.floor(seconds / 60), 2, '0', 'STR_PAD_LEFT') ;
	seconds = seconds % 60;
	s = strPad(seconds, 2, '0', 'STR_PAD_LEFT') ;

	return d+' '+h+':'+m+':'+s;
}

function addFlightRow(event) {
	var rows = $('#flight-data tr');
	var elem = $(rows[rows.length-1].children[1].children[0].children[0]);

	// Если в последней строке не пусто, надо добавить ещё одну. Иначе - просто запишем пришедшие данные в поле или поставим туда курсор
	if (elem[0].value != '' && elem[0].value != '00 00:00:00') {
		$('#flight-data').append('<tr>'+
			'<td>'+
				'<div class="ui-state-default ui-corner-all button-toggle" title="'+options.toggleSignHint+'">'+
					'<span class="ui-icon ui-icon-plus"></span>'+
				'</div>'+
			'</td>'+
			'<td>'+
				'<div style="margin: 0px;"><input type="text" class="ui-state-default ui-corner-all ui-input flight-time-input"  title="'+options.flightTimeFormatHint+'"/></div>'+
			'</td>'+
			'<td>'+
				'<div class="ui-state-default ui-corner-all button-remove" title="'+options.removeRowHint+'">'+
					'<span class="ui-icon ui-icon-close"></span>'+
				'</div>'+
			'</td></tr>');

		// приходится работать без id элементов, поэтому сначала отвязываем события у всех ранее созданных, а потом привязываем всем имеющимся
		$('div.button-toggle').unbind();
		$('div.button-toggle').click(toggleFlightTimeSign);
		$('div.button-remove').unbind();
		$('div.button-remove').click(removeFlightRow);
		$("input.flight-time-input").unbind();
		$("input.flight-time-input").inputmask(options.flightTimeFormat);
		$("input.flight-time-input").keyup(updateArrival);

		var rows = $('#flight-data tr');
		var elem = $(rows[rows.length-1].children[1].children[0].children[0]);
	}

	// Метод вызывается либо по щелчку на кнопке, и тогда параметр - это объект с информацией о событии, либо из кода, и тогда параметр - время, которое нужно записать в поле
	if (typeof(event) != 'object') {
		elem[0].value = getFlightTimeStr(Math.abs(event));
		if (1*event < 0) {
			$(rows[rows.length-1].children[0].children[0].children[0]).removeClass('ui-icon-plus').addClass('ui-icon-minus');
		}
	}
	else
		elem.focus();
	updateArrival();
}

function toggleFlightTimeSign() {
	var elem = $(this.children[0]);
	if (elem.hasClass('ui-icon-plus')) {
		elem.removeClass('ui-icon-plus');
		elem.addClass('ui-icon-minus');
	} else {
		elem.removeClass('ui-icon-minus');
		elem.addClass('ui-icon-plus');
	}
	updateArrival();
}

function takeToCalc() {
	var distance = getDistance(options.prm.departure, options.prm.destination);
	var minSpeed = getMinSpeed();
	var perCentText = $(this.parentNode.parentNode.children[0]).eq(0).html();
	var perCent = perCentText.split('%')[0];
	var dur = getFlightDuration(minSpeed, distance, perCent, options.prm.uniSpeed);
	var sign = (options.prm.mode == 1)? -1 : 1;
	addFlightRow(sign*dur);
	options.save();
}

function removeFlightRow() {
	var rows = $('#flight-data tr');
	// последнюю строку не надо удалять, её достаточно просто очистить
	if (rows.length == 1) {
		$(rows[0].children[1].children[0].children[0])[0].value='';
		$(rows[0].children[0].children[0].children[0]).removeClass('ui-icon-minus');
		$(rows[0].children[0].children[0].children[0]).addClass('ui-icon-plus');
	}
	else
		$(this.parentNode.parentNode).remove();
	updateArrival();
}

function clearSavePointsTable() {
	var tables = ['savepoints-galaxies', 'savepoints-systems', 'savepoints-planets'];
	for (tblidx = 0; tblidx < 3; tblidx++) {
		var tbl = $('#'+tables[tblidx])[0];
		for (var i = tbl.rows.length-1; i > 0; i--) {
			$(tbl.rows[i]).remove();
		}
	}
}

function compareSavePoints(a, b) {
	//если скорости у флотов, летящих к обеим точкам сейва одинаковые, наверх списка поднимаем ту точку, лететь к которой дешевле
	if (a[0] == b[0])
		return (a[2] - b[2]);
	else
		// если скорости разные, наверх поднимаем точку, у которой скорость меньше
		return a[0] - b[0];
}

function validateSPParams() {
	var firstWrong = '';
	if (!checkCoordinates('departure'))
		firstWrong = 'departure-g';

	// Найдём скорость самого медленного корабля во флоте. Если minSpeed === Infinity, значит, ни одного корабля нет.
	var minSpeed = getMinSpeed();
	if (minSpeed === Infinity  && firstWrong == '') {
		firstWrong = 'esp-probe';
	}

	var startDT = $('#save-start-datetime')[0].value;
	if (!validateDateField('save-start-datetime') && firstWrong == '')
		firstWrong = 'save-start-datetime';

	var returnDT = $('#save-return-datetime')[0].value;
	if (!validateDateField('save-return-datetime') && firstWrong == '')
		firstWrong = 'save-return-datetime';

	// Даже если с самими значениями в полях даты/времени отправления и возврата всё в порядке, надо ещё проверить, какая из дат раньше
	if (validateDateField('save-start-datetime') && validateDateField('save-return-datetime')) {
		if ((parseDate(startDT, options.datetimeFormat) > parseDate(returnDT, options.datetimeFormat)) && firstWrong == '')
			firstWrong = 'return-start';
	}

	// В поле "допустимая погрешность" только время. Если placeholder-ов нет, то всё ок: регексп не даст ввести туда кривое значение
	var tolerance = $('#save-tolerance-time')[0].value;
	if (tolerance.search('_') >= 0) {
		// если поле вообще пустое - не будем рисовать на нём красную рамку
		if (tolerance == '__:__')
			$('#save-tolerance-time').removeClass('ui-state-error').addClass('ui-state-default');
		else
			$('#save-tolerance-time').removeClass('ui-state-default').addClass('ui-state-error');
		if (firstWrong == '')
			firstWrong = 'save-tolerance-time';
	} else {
		if (tolerance == '')
			firstWrong = 'save-tolerance-time';
		$('#save-tolerance-time').removeClass('ui-state-error').addClass('ui-state-default');
	}

	return firstWrong;
}

function updateSavePoints() {
	clearSavePointsTable();

	// Запустим проверку параметров. Функция обработает все необходимые поля, но вернёт id первого из них, где что-то не так
	var wrongField = validateSPParams();
	// если что-то не в порядке, надо показать сообщение об ошибке и поставить курсор в нужное поле
	if (wrongField != '') {
		var msgText = '';
		switch (wrongField) {
			case 'departure-g': msgText = options.msgWrongDepartureCoordinates; break;
			case 'esp-probe': msgText = options.msgNoShips; break;
			case 'save-start-datetime': msgText = options.msgWrongDepartureTime; break;
			case 'save-return-datetime': msgText = options.msgWrongReturnTime; break;
			case 'save-tolerance-time': msgText = options.msgWrongTolerance; break;
			case 'return-start': msgText = options.msgDepartureAfterReturn; wrongField = 'save-start-datetime'; break;
		}

		$('#'+options.warnindMsgDivId).text(msgText);
		$('#'+options.warnindDivId).fadeIn(800, function () {
			setTimeout(function() {
				$('#'+options.warnindDivId).fadeOut(800);
			}, 5000);
		  });
		$('#'+wrongField)[0].focus();

		return;
	}

	// Если выполнение попало сюда - значит, все параметры проверены. Просто собираем значения.
	var startDT = $('#save-start-datetime')[0].value;
	var returnDT = $('#save-return-datetime')[0].value;
	var tolerance = $('#save-tolerance-time')[0].value;
	var minSpeed = getMinSpeed();

	var startDTValue = parseDate(startDT, options.datetimeFormat);
	options.prm.saveStartDT = startDTValue; 
	var returnDTValue = parseDate(returnDT, options.datetimeFormat);
	options.prm.saveReturnDT = returnDTValue;
	var duration = Math.round(Math.ceil((returnDTValue - startDTValue) / 1000.0) / 2);

	var rgx = /(\d\d):(\d\d)/;
	var parts = tolerance.match(rgx);
	var toleranceValue = Math.round((parts[1]*3600 + parts[2]*60)/2);
	options.prm.saveTolerance = toleranceValue*2; 

	var coords = options.prm.departure;
	var destination = [0, 0, 0];
	var deltas = [0, 0, 0];
	var limit = 0;
	var savePoints = new Array();
	var haveResults = false;
	var distance = 0;

	for (var coordElem = 0; coordElem < 3; coordElem++) {
		switch (coordElem) {
			case 0: {
				limit = 9;
				var targetTable = 'savepoints-galaxies';
				var coordFormat = "{0}:xxx:xx";
				break;
			}
			case 1: {
				limit = 499;
				var targetTable = 'savepoints-systems';
				var coordFormat = coords[0] + ":{0}:xx";
				break;
			}
			case 2: {
				limit = 16;
				var targetTable = 'savepoints-planets';
				var coordFormat = coords[0] + ":" + coords[1] + ":{0}";
				break;
			}
		}

		delta = 0;
		deltas[0] = 0;
		deltas[1] = 0;
		deltas[2] = 0;
		while (true) {
			delta++;
			distance = 0;

			if (coords[coordElem] - delta > 0) {
				deltas[coordElem] = -delta;
				destination[0] = coords[0] + deltas[0];
				destination[1] = coords[1] + deltas[1];
				destination[2] = coords[2] + deltas[2];
				distance = getDistance(options.prm.departure, destination);
			} else if (coords[coordElem] + delta <= limit) {
				deltas[coordElem] = delta;
				destination[0] = coords[0] + deltas[0];
				destination[1] = coords[1] + deltas[1];
				destination[2] = coords[2] + deltas[2];
				distance = getDistance(options.prm.departure, destination);
			}
			// Если расстояние так и не удалось вычислить - значит, дельта слишком большая, можно завершать цикл
			if (distance == 0) {
				break;
			}
			for (var speed = 100; speed > 0; speed -= 10) {
				var flightDuration = getFlightDuration(minSpeed, distance, speed,  options.prm.uniSpeed);
				var cost = getDeutConsumption(minSpeed, distance, flightDuration, i, options.prm.uniSpeed);
				// Если длительность полёта на 100% больше запрашиваемой -значит, точно забрались далеко, можно завершать цикл
				if (speed == 100 && flightDuration > duration + tolerance) {
					break;
				}
				if (flightDuration > duration - toleranceValue && flightDuration < duration + toleranceValue) {
					// нашли расстояние, удовлетворяющее условиям - запомним соответствующие точки
					if (coords[coordElem] - delta > 0) {
						savePoints.push([speed, coordFormat.format(coords[coordElem] - delta), cost, coords[0] + deltas[0], coords[1] + deltas[1], coords[2] + deltas[2]]);
					}
					if (coords[coordElem] + delta <= limit) {
						savePoints.push([speed, coordFormat.format(coords[coordElem] + delta), cost, coords[0] + deltas[0], coords[1] + deltas[1], coords[2] + deltas[2]]);
					}
				}
			}
		}
		if (savePoints.length > 0)
			haveResults = true;
		savePoints.sort(compareSavePoints);
		for (var spi = 0; spi < savePoints.length; spi++) {
			$('#'+targetTable).append('<tr class='+((spi % 2) === 1 ? 'odd' : 'even')+'><td>'+savePoints[spi][0]+'%</td><td>'+
					'<a href="#" onclick="showFlightTime(['+savePoints[spi][3]+','+savePoints[spi][4]+','+savePoints[spi][5]+'],\''+startDT+'\','+savePoints[spi][0]+');">'+savePoints[spi][1]+'</a>'+
					'</td><td>'+numToOGame(savePoints[spi][2])+'</td><tr>');
		}

		savePoints = new Array();
	}
	// Если ничего не нашли - надо сказать об этом, а то юзер будет в недоумении
	if (!haveResults) {
		$('#'+options.warnindMsgDivId).text(options.msgNoSavepointsFound);
		$('#'+options.warnindDivId).fadeIn(800, function () {
			setTimeout(function() {
				$('#'+options.warnindDivId).fadeOut(800);
			}, 5000);
		  });
	}
	options.save();
}

function showFlightTime(point, depTime, speed) {
	$("#tabs").tabs("select", 0);
	$('#destination-g')[0].value = point[0];
	$('#destination-s')[0].value = point[1];
	$('#destination-p')[0].value = point[2];
	options.prm.destination = point;
	updateNumbers();
	$('#start-datetime')[0].value = depTime;
	var rows = $('#flight-data tr');
	for(var i = 0; i < rows.length; i++) {
		removeFlightRow.apply(rows[i].children[2].children[0]);
	}
	var distance = getDistance(options.prm.departure, options.prm.destination);
	var minSpeed = getMinSpeed();
	var dur = getFlightDuration(minSpeed, distance, speed, options.prm.uniSpeed);
	addFlightRow(dur);
	addFlightRow(dur);
	updateArrival();
}

function showTabsHints(activeTab) {
	// С параметром функция вызывается при загрузке страницы - тогд данные берутся из куков.
	// Без параметра - перед переключением вкладок, т.е. активной станет вкладка, которая на момент вызова функции неактивна.
	if (activeTab === undefined) {
		var firstTab = $('#flight-times-panel')[0];
		activeTab = $(firstTab).hasClass('ui-tabs-hide')? 0 : 1;
	}
	$('#hint-message').text((activeTab == 0)?options.flightmodesNote:options.savepointsNote);
}

function toggleFlightMode() {
	if (options.prm.mode == 1) {
		$('#flight-title-1').text(options.departureTitle);
		$('#flight-title-2').text(options.arrivalTitle);
		options.prm.mode = 0;
	} else {
		$('#flight-title-2').text(options.departureTitle);
		$('#flight-title-1').text(options.arrivalTitle);
		options.prm.mode = 1;
	}
	options.save();
}

$(document).ready(function() {
	options.load();
	//$('#cmb-drive')[0].value = options.prm.driveLevels[0];
	//$('#imp-drive')[0].value = options.prm.driveLevels[1];
	//$('#hyp-drive')[0].value = options.prm.driveLevels[2];
	//$('#universe-speed')[0].value = options.prm.uniSpeed;
	//$('#departure-g')[0].value = options.prm.departure[0];
	//$('#departure-s')[0].value = options.prm.departure[1];
	//$('#departure-p')[0].value = options.prm.departure[2];
	$('#destination-g')[0].value = options.prm.destination[0];
	$('#destination-s')[0].value = options.prm.destination[1];
	$('#destination-p')[0].value = options.prm.destination[2];
	for (var i = 0; i < options.shipsData.length; i++){
		$('#'+options.shipsData[i][0])[0].value = options.prm.ships[i];
	}

	//$('#save-start-datetime')[0].value = getDateStr(options.prm.saveStartDT, options.datetimeFormat);
	//$('#save-return-datetime')[0].value = getDateStr(options.prm.saveReturnDT, options.datetimeFormat);
	//$('#save-tolerance-time')[0].value = getTimeStr(options.prm.saveTolerance);
	//var flightData = options.prm.flightData.slice();
	
	// для удобства перевернём значение и вызовем функцию, которая его ещё раз перевернёт
	options.prm.mode = options.prm.mode == 0 ? 1 : 0;
	toggleFlightMode();
	
	// на всякий случай удалим все строки в калькуляторе, и создадим их заново с полученными значениями
	var rows = $('#flight-data tr');
	var i = 0;
	for(i = 0; i < rows.length; i++) {
		removeFlightRow.apply(rows[i].children[2].children[0]);
	}
	
	//for (i = 0; i < flightData.length; i++)
	//	addFlightRow(flightData[i]);
	//updateArrival();
	var tabsData = $.cookie('ui-tabs-1');
	if (tabsData) {
		showTabsHints(tabsData);
	}

	$('input').focusin(function() {
		$(this).addClass('ui-state-focus');
	});
	$('input').focusout(function() {
		$(this).removeClass('ui-state-focus');
	});

	// После того, как событие будет обработано, нужно вызвать функцию пересчета. Её имя передаём в поле data событий.
	$('#flight input:text').keyup('updateNumbers', validateInputNumber);
	$('#flight input:text').blur('updateNumbers', validateInputNumberOnBlur);
	$('#flight select').keyup(updateNumbers);
	$('#flight select, #flight input[type=checkbox]').change(updateNumbers);
	$('#flight select').mousemove(updateNumbers);
	$('#reset').click(resetParams);
	$('#set-departure-now').click(setDepartureNow);
	$('#set-departure-zero').click(setDepartureZero);
	$('#add-flight-time').click(addFlightRow);

	$('#start-datetime').unbind();
	$('#start-datetime').keyup(updateArrival);
	$('div.button-taketocalc').click(takeToCalc);

	$('input.flight-time-input').unbind();
	$('input.flight-time-input').inputmask(options.flightTimeFormat);
	$('input.flight-time-input').keyup(updateArrival);
	$('#toggle-mode').click(toggleFlightMode);
	$('div.button-toggle').unbind();
	$('div.button-toggle').click(toggleFlightTimeSign);
	$('div.button-remove').unbind();
	$('div.button-remove').click(removeFlightRow);

	$('#set-save-departure-now').click(setSaveDepartureNow);

	$('#save-start-datetime').unbind();
	$('#save-start-datetime').keyup(validateSPParams);
	$('#save-return-datetime').unbind();
	$('#save-return-datetime').keyup(validateSPParams);
	$('#save-tolerance-time').unbind();
	$('#save-tolerance-time').keyup(validateSPParams);
	$('#save-tolerance-time').blur(validateSPParams);

	$('#calculate-savepoints').click(updateSavePoints);

	$("#tabs").bind("tabsselect", function(event, ui) {
		showTabsHints();
	});

	// Настраиваем ограничения на поля ввода координат
	$('#departure-g').data('constrains', {'min': 1, 'def': 0, 'max': 12});
	$('#destination-g').data('constrains', {'min': 1, 'def': 0, 'max': 12});
	$('#departure-s').data('constrains', {'min': 1, 'def': 0, 'max': 499});
	$('#destination-s').data('constrains', {'min': 1, 'def': 0, 'max': 499});
	$('#departure-p').data('constrains', {'min': 1, 'def': 0, 'max': 16});
	$('#destination-p').data('constrains', {'min': 1, 'def': 0, 'max': 16});

	updateNumbers();
});
