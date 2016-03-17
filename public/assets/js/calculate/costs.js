var options = {
	defConstraints: {
				min: null,
				max: null,
				def: 0,
				allowFloat: false,
				allowNegative: false
			},

	load: function()
	{
		var data;

		if (html5_storage())
			data = localStorage.getItem('options_costs');
		else
			data = $.cookie('options_costs');

		if (data) {
			data = data.split(',');
			if (data.length >= 12) {
				try {
					$('#shipyard-level')[0].value = validateNumber(parseFloat(data[0]), 0, Number.POSITIVE_INFINITY, 1);
					$('#robot-factory-level')[0].value = validateNumber(parseFloat(data[1]), 0, Number.POSITIVE_INFINITY, 0);
					$('#nanite-factory-level')[0].value = validateNumber(parseFloat(data[2]), 0, Number.POSITIVE_INFINITY, 0);
					$('#universe-speed')[0].value = validateNumber(parseFloat(data[3]), 0, Number.POSITIVE_INFINITY, 0);
					$('#research-lab-level')[0].value = validateNumber(parseFloat(data[4]), 0, Number.POSITIVE_INFINITY, 0);
					if (data[5] == 'true') {
						$('#technocrat')[0].checked = 'checked';
					}
					$('#tech-types-select')[0].value = 1;
					$('#tab2-from-level')[0].value = 0;
					$('#tab2-to-level')[0].value = 0;
					$('#energy-tech-level')[0].value = validateNumber(parseFloat(data[6]), 0, Number.POSITIVE_INFINITY, 0);
					$('#max-planet-temp')[0].value = validateNumber(parseFloat(data[7]), -134, 0, 0);
					if (data[8] == 'true') {
						$('#geologist')[0].checked = 'checked';
					}
					if (data[9] == 'true') {
						$('#engineer')[0].checked = 'checked';
					}
					
					loadLLCData();
					updateResultingLevel();
					
				} catch(e) {}
			}
		}
	},

	save: function() {
		var data = getInputNumber($('#shipyard-level')[0]) + ',' + getInputNumber($('#robot-factory-level')[0]) + ',' + getInputNumber($('#nanite-factory-level')[0]) + ',' + $('#universe-speed')[0].value +
			',' + getInputNumber($('#research-lab-level')[0]) + ',' + $('#technocrat')[0].checked+','+
			getInputNumber($('#energy-tech-level')[0]) + ',' + getInputNumber($('#max-planet-temp')[0])+','+$('#geologist')[0].checked+','+$('#engineer')[0].checked+','+
			getInputNumber($('#irn-level')[0]) + ',' + getInputNumber($('#planetsSpin')[0]) + ',';
			for (var i = 1; i <= getInputNumber($('#planetsSpin')[0]); i++) {
				if (i>1)
					data += ',';
				data += getInputNumber($('#lablevel_'+i)[0]) + ',' + $('#labchoice_'+i)[0].checked;
			}

		if (html5_storage())
			localStorage.setItem('options_costs', data);
		else
			$.cookie('options_costs', data, { expires: 7, path: '/' });
	},

	techData: {},
	techReqs: {},
	
	minPlanetsCount: 1,
	maxPlanetsCount: 99,
	defPlanetsCount: 8,
	currPlanetsCount: 8,
	resultingLabLevel: 0,
	resultingLabLevelComputed: false
};

function loadLLCData()
{
	var data;

	if (html5_storage())
		data = localStorage.getItem('options_costs');
	else
		data = $.cookie('options_costs');

	if (data) {
		data = data.split(',');
		if (data.length >= 12) {
			$('#irn-level')[0].value = data[10];
			$('#planetsSpin')[0].value = data[11];
			options.currPlanetsCount = data[11];
			var tbl = $('#lab-levels-table')[0];
			for (var i = tbl.rows.length-1; i > 0; i--) {
				$(tbl.rows[i]).remove();
			}
			for (var i = 1; i <= data[11]; i++) {
				$('#lab-levels-table').append('<tr class="'+((i % 2) === 1 ? 'odd' : 'even')+'">'+
						'<td align="center" >'+options.planetNumStr+i+'</td>'+
						'<td align="center" width="20%;"><input type="text" id="lablevel_'+i+'" name="lablevel_'+i+'>" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="'+data[11+2*i-1]+'" /></td>'+
						'<td align="center" width="20%;"><input type="radio" id="labchoice_'+i+'" name="start-pln" disabled="disabled"/></td>'+
						'</tr>');
				if (data[11+2*i-1] > 0)
					$('#labchoice_'+i)[0].disabled = false;
				if (data[11+2*i] == 'true')
					$('#labchoice_'+i)[0].checked = 'checked';
				
				$('#lablevel_'+i).keyup('changeLabLevel', validateInputNumber);
				$('#labchoice_'+i).click(updateResultingLevel);
			}
		}
	}
}

function resetParams() {
	$('#shipyard-level')[0].value = 0;
	$('#robot-factory-level')[0].value = 0;
	$('#nanite-factory-level')[0].value = 0;
	$('#universe-speed')[0].selectedIndex = 0;
	$('#research-lab-level')[0].value = 0;
	$('#technocrat')[0].checked = false;

	$('#irn-level')[0].value = 0;
	$('#planetsSpin')[0].value = 8;
	options.currPlanetsCount = 8;
	var tbl = $('#lab-levels-table')[0];
	for (var i = tbl.rows.length-1; i > 0; i--) {
		$(tbl.rows[i]).remove();
	}
	for (var i = 1; i <= 8; i++) {
		$('#lab-levels-table').append('<tr class="'+((i % 2) === 1 ? 'odd' : 'even')+'">'+
				'<td align="center" >'+options.planetNumStr+i+'</td>'+
				'<td align="center" width="20%;"><input type="text" id="lablevel_'+i+'" name="lablevel_'+i+'>" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>'+
				'<td align="center" width="20%;"><input type="radio" id="labchoice_'+i+'" name="start-pln" disabled="disabled"/></td>'+
				'</tr>');
		$('#lablevel_'+i).keyup('changeLabLevel', validateInputNumber);
		$('#labchoice_'+i).click(updateResultingLevel);
	}
	updateResultingLevel();
	options.resultingLabLevelComputed = false;
	
	for (var outer = 0; outer < 2; outer++) {
		var innerNums = (outer == 0) ? [2, 3, 4, 5, 6] : [2, 3, 4];
		for (var innerIdx = 0; innerIdx < innerNums.length; innerIdx++) {
			var inner = innerNums[innerIdx];
			var rows = $('#table-'+outer+'-'+inner+' tr');
			for (var row = 1; row < rows.length-5; row++) {
				rows[row].children[2].children[0].value = 0;
				if (outer == 1)
					rows[row].children[3].children[0].value = 0;
				var firstDataCol = (outer == 1)?4:3;
				for (var cell = firstDataCol; cell < firstDataCol+6; cell++) {
					if (cell == firstDataCol+4)
						$(rows[row].children[cell]).html('0'+options.datetimeS);
					else
						$(rows[row].children[cell]).html('0');
				}
			}
		}
	}
	jQuery.each(options.techData, function(key, value) {
		options.techData[key] = null;
	});
	updateNumbers();

	$('#tech-types-select')[0].value = 1;
	$('#tab2-from-level')[0].value = 0;
	$('#tab2-to-level')[0].value = 0;
	$('#energy-tech-level')[0].value = 0;
	//$('#plasma-tech-level')[0].value = 0;
	$('#max-planet-temp')[0].value = 0;
	//$('#booster')[0].selectedIndex = 0;
	$('#geologist').checkbox("option", "checked", false);
	$('#engineer').checkbox("option", "checked", false);
	updateOneMultTab();
}

function getBuildEnergyCost(techID, techLevel) {
	// Технологии "Терраформер" и "Гравитационная технология" - особенные. Они требуют энергии для изучения. Но кроме них таких технологий нет, поэтому в основном массиве про это ни слова
	if (techLevel < 1)
		return [0, 0, 0];
	var data = options.techCosts[techID];
	if (data === undefined)
		return [0, 0, 0];
	var buildCost = 0;
	switch (techID*1) {
		case 33:
			buildCost = 1000 * Math.pow(data[3], techLevel - 1);
			break;
		case 199:
			buildCost = 300000 * Math.pow(data[3], techLevel - 1);
			break;
		default:
			buildCost = 0;
	}
	return buildCost;
}

function calcBuildCost(techID, techLevel) {
	if (techLevel < 1)
		return [0, 0, 0];
	var data = options.techCosts[techID];
	if (data === undefined)
		return [0, 0, 0];
	var cost = [0, 0, 0];
	var price = 0;
	// В редизайне астрофизика дорожает с коэффициентом 1.75, и стоимость округляется до сотен
	if (techID == 124) {
		for (var i = 0; i < 3; i++) {
			price = data[i] * Math.pow(1.75, (techLevel - 1));
			cost[i] = 100 * Math.round(0.01 * price);
		}
	} else {
		for (var i = 0; i < 3; i++)
		{
			cost[i] = Math.floor(data[i] * Math.pow(data[3], (techLevel - 1)));

			if (techID > 400 && techID < 600 && $('#engineer')[0].checked)
			{
				cost[i] *= 0.9;
			}
			if (techID > 200 && techID < 300 && $('#admiral')[0].checked)
			{
				cost[i] *= 0.9;
			}
		}
	}
	return cost;
}

function getBuildCost(techID, techLevelFrom, techLevelTo) {
	totalCost = [0, 0, 0];
	// После techID==200 идут корабли и оборона, у них прироста стоимости от количества нет - их будем считать не так, как постройки
	if (techID < 200 || (techID > 300 && techID < 400)) {
		// Получим стоимость строительства всех уровней техи от 1 до запрошенного и просто сложим результаты
		for (var i = 1*techLevelFrom + 1; i <= techLevelTo; i++) {
			cost = calcBuildCost(techID, i);
			totalCost[0] += cost[0];
			totalCost[1] += cost[1];
			totalCost[2] += cost[2];
		}
	} else {
		// Получим стоимость строительства одной единиы и умножим на количество
		var cost = calcBuildCost(techID, 1);
		totalCost[0] = techLevelTo * cost[0];
		totalCost[1] = techLevelTo * cost[1];
		totalCost[2] = techLevelTo * cost[2];
	}
	return totalCost;
}

function getBuildTime(techID, techLevelFrom, techLevelTo) {
	if (techLevelFrom < 0)
		return 0;
	var data = options.techCosts[techID];
	if (data === undefined)
		return 0;
	if (techLevelFrom >= techLevelTo)
		return 0;
	var timeSpan = 1;
	// Узнаем стоимость постройки - она участвует в формулах расчёта времени строительства
	var cost = [0, 0, 0];
	// Техи с ID до 100 - это здания. Скорость их строительства зависит от наличия и уровня фабрик роботов и нанитов
	if (techID <= 100) {
		cost = getBuildCost(techID, techLevelFrom, techLevelTo);
		var robotsLevel = getInputNumber($('#robot-factory-level')[0]);
		var nanitesLevel = getInputNumber($('#nanite-factory-level')[0]);
		// Время постройки всех зданий, кроме Фабрики нанитов, Лунной базы, Фаланги и Ворот, снижается (вплоть до 8го уровня)
		var reduction = 1;
		if (techID != 15 && techID != 41 && techID != 42 && techID != 43) 
			reduction = Math.max(4 - techLevelTo / 2.0, 1);
		// Формула ОГейма даёт время в часах - переведём в секунды
		timeSpan = 3600 * (cost[0] + cost[1]) / (2500.0 * reduction * (robotsLevel + 1.0) * Math.pow(2.0, nanitesLevel));
		timeSpan *= (1 - ($('#arhitector')[0].checked ? 0.25 : 0));
	}

	// Техи с ID от 100 до 200 - это технологии. Скорость их исследования зависит от уровня исследовательской лаборатории и наличия технократа
	if (100 < techID && techID <= 200) {
		cost = getBuildCost(techID, techLevelFrom, techLevelTo);
		// С уровнем лаборатории есть заморочка: если он введён вручную, надо брать это значение, а если рассчитан в соответствующем диалоге, то надо пересчитать результирующий уровень лаборатории с учётом требований данной техи.
		if (!options.resultingLabLevelComputed) {
			var researchLabLevel = getInputNumber($('#research-lab-level')[0]);
			if (researchLabLevel < options.techReqs[techID])
				return -1;
		}
		else {
			var researchLabLevel = getLabLevel(options.techReqs[techID]);
			if (researchLabLevel == 0)
				return -1;
		}
		// Формула ОГейма даёт время в часах - переведём в секунды
		timeSpan = 3600 * (cost[0] + cost[1]) / (1000 * (1.0 + researchLabLevel));
		// Если есть технократ, время на исследование нужно умножить на его поправочный коэффициент
		var technocratFactor = $('#technocrat')[0].checked ? 0.75 : 1;
		timeSpan *= technocratFactor;
	}

	// Техи с ID больше 200 - это корабли и оборона. Скорость их строительства зависит от наличия и уровня верфи и фабрики нанитов
	if (techID > 200) {
		// Для кораблей и обороны нельзя считать время, исходя из полного кол-ва ресурсов - нужно считать по одному.
		cost = calcBuildCost(techID, 1);
		//((metal + crystal) / 5'000) * (2 / ((level shipyard) + 1)) * (0.5 ^ (level nanite factory))
		var shipyardLevel = getInputNumber($('#shipyard-level')[0]);
		var nanitesLevel = getInputNumber($('#nanite-factory-level')[0]);
		// Формула ОГейма даёт время в часах - переведём в секунды, округлим и умножим на кол-во единиц, которые нужно построить
		timeSpan = Math.floor(3600 * (cost[0] + cost[1]) / 5000.0 * 2.0 / (shipyardLevel + 1.0) * Math.pow(0.5, nanitesLevel));
		// При слишком высоких уровнях нанитки скорость постройки СС может стать 0 - надо это учесть
		if (timeSpan == 0) {
			timeSpan = 1;
		}
		timeSpan *= techLevelTo;
	}
	// Если расчёт заказан для ускоренной вселенной, разделим вычисленное время на поправочный коэффициент
	if ($('#universe-speed')[0].value > 1) {
		timeSpan /= $('#universe-speed')[0].value;
	}
	if (timeSpan < 1) {
		timeSpan = 1;
	}

	return timeSpan;
}

function getLabLevel(min) {
	var rows = $('#lab-levels-table tr');
	var labs = [];
	for (var i = 1; i < rows.length; i++) {
		if ($('#lablevel_'+i)[0].value > 0 && (min == 0 || $('#lablevel_'+i)[0].value >= min))
			labs.push([$('#lablevel_'+i)[0].value, $('#labchoice_'+i)[0].checked]);
	}
	labs.sort(compareLabs);
	var result = 0;
	var limit = Math.min(getInputNumber($('#irn-level')[0])+1, labs.length);
	for (var i = 0; i < limit; i++) {
		result += Number(labs[i][0]);
	}
	return result;
}

function showResearchImpossibleMessage(researchName) {
	// Если известны div-ы и текст для сообщения об ошибке, выведем туда это сообщение, а потом исправим значение
	if (getOptionValue('warnindDivId', null) != null && getOptionValue('msgCantResearch', null) != null) {
		$('#'+options.warnindMsgDivId).text(options.msgCantResearch.format(researchName));
		$('#'+options.warnindDivId).fadeIn(800, function () {
			setTimeout(function() {
				$('#'+options.warnindDivId).fadeOut(800);
			}, 5000);
		  });
	}
}

// Обновляет данные по строке, в которой сделано изменение и записывает изменённые значения в глобальный массив рассчитанных значений
function updateRow() {
	var techID = $(this.parentNode.parentNode.children[0]).html();
	if (techID == '' || 1*techID == 0)
		return;
	var row = $(this.parentNode.parentNode)[0];
	var tblID = this.parentNode.parentNode.parentNode.parentNode.id;
	var parts = tblID.split(/-/);
	if (parts.length < 3)
		return;
	var rowKey = techID + '-' + parts[1] + '-' + parts[2];
	var outerTab = Number(parts[1]);
	if (outerTab == 1) {
		var techLevelFrom = 1*row.children[2].children[0].value;
		var techLevelTo = 1*row.children[3].children[0].value;
		var firstDataCol = 4;
	} else {
		var techLevelTo = 1*row.children[2].children[0].value;
		var techLevelFrom = techLevelTo == 0 ? 0 : techLevelTo - 1;
		var firstDataCol = 3;
	}
	if (techID > 10000) {	// Здания на луне хранятся в таблицах с id на 10000 больше актуального
		techID -= 10000;
	}
	var dataRow = [0, 0, 0, 0, 0, 0];
	if (techLevelTo > techLevelFrom && techLevelTo >= 0) {
		var timeSpan = getBuildTime(techID, techLevelFrom, techLevelTo);
		// Если запрошено исследование и оно не может быть выполнено - обрабатываем этот случай особо
		if (timeSpan < 0) {
			if (outerTab == 1) {
				row.children[2].children[0].value = 0;
				row.children[3].children[0].value = 0;
			} else {
				row.children[2].children[0].value = 0;
			}
			$(row.children[firstDataCol]).html('0');
			$(row.children[firstDataCol+1]).html('0');
			$(row.children[firstDataCol+2]).html('0');
			$(row.children[firstDataCol+3]).html('0');
			$(row.children[firstDataCol+4]).html('0'+options.datetimeS);
			$(row.children[firstDataCol+5]).html('0');
			options.techData[rowKey] = null;
			updateNumbers();
			showResearchImpossibleMessage($(row.children[1]).html());
			return;
		}
		var resCost = getBuildCost(techID, techLevelFrom, techLevelTo);
		var energyCost = getBuildEnergyCost(techID, techLevelTo);
		var points = Math.round((1.0 * resCost[0] + 1.0 * resCost[1] + 1.0 * resCost[2]) / 1000.0);
		$(row.children[firstDataCol]).html(numberToShortenedString(resCost[0], options.unitSuffix));
		$(row.children[firstDataCol+1]).html(numberToShortenedString(resCost[1], options.unitSuffix));
		$(row.children[firstDataCol+2]).html(numberToShortenedString(resCost[2], options.unitSuffix));
		$(row.children[firstDataCol+3]).html(numberToShortenedString(energyCost, options.unitSuffix));
		$(row.children[firstDataCol+4]).html(timespanToShortenedString(timeSpan, options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true));
		$(row.children[firstDataCol+5]).html(numberToShortenedString(points, options.unitSuffix));
		dataRow[0] = resCost[0];
		dataRow[1] = resCost[1];
		dataRow[2] = resCost[2];
		dataRow[3] = energyCost;
		dataRow[4] = timeSpan;
		dataRow[5] = points;
		options.techData[rowKey] = dataRow;
	} else {
		$(row.children[firstDataCol]).html('0');
		$(row.children[firstDataCol+1]).html('0');
		$(row.children[firstDataCol+2]).html('0');
		$(row.children[firstDataCol+3]).html('0');
		$(row.children[firstDataCol+4]).html('0'+options.datetimeS);
		$(row.children[firstDataCol+5]).html('0');
		options.techData[rowKey] = null;
		// Отдельно на вкладке "Исследования" вкладки "Несклько уровней" надо не давать вводить уровни, если уровень лаборатории недостаточен для выполнения исследования
		if (Number(parts[2]) == 4 && outerTab == 1) {
			if (getBuildTime(techID, techLevelFrom, techLevelFrom+1) < 0) {
				row.children[2].children[0].value = 0;
				showResearchImpossibleMessage($(row.children[1]).html());
			}
		}
	}
	updateNumbers();
}

// Учитывает изменения в параметрах: уровни фабрики роботов, фабрики нанитов, верфи, иссл.лабы, скорость вселенной, галочка "технократ". Обновляет время в соответствующих полях глобального массива рассчитанных значений
function updateParams() {
	// Изменения в параметрах повлияют только на время строительства/исследования, при этом обрабатывать можно не все вкладки
	switch (this.id) {
		case 'robot-factory-level': var techTypes = [2, 3]; break;
		case 'nanite-factory-level': var techTypes = [2, 3, 5, 6]; break;
		case 'shipyard-level': var techTypes = [5, 6]; break;
		case 'research-lab-level': var techTypes = [4]; break;
		case 'technocrat': var techTypes = [4]; break;
		case 'arhitector': var techTypes = [2, 3]; break;
		case 'engineer': var techTypes = [6]; break;
		case 'admiral': var techTypes = [5]; break;
		case 'universe-speed': var techTypes = [2, 3, 4, 5, 6]; break;
	}
	var needUpd = {0: false, 1: false};
	jQuery.each(options.techData, function(key, value) {
		if (value == null)
			return;
		var keyParts = key.split(/-/);
		if (jQuery.inArray(1*keyParts[2], techTypes) >= 0) {
			// мы знаем id техи, которую надо пересчитать, и номера внешней и внутренней вкладок (эти же номера позволят получить id таблицы).
			// Чтобы пересчитать теху, надо получить все строки таблицы, в которой она сидит, и найти там нужную строку по id
			var rows = $('#table-'+keyParts[1]+'-'+keyParts[2]+' tr');
			for (var idx = 1; idx < rows.length; idx++) {
				var rowID = $(rows[idx].children[0]).html();
				if (rowID == keyParts[0]) {
					// Нашли нужную строку. Пересчитаем время и установим флаг, что для даннй вкладки надо вызвать метод updateNumbers(), который обновит итоги.
					if (keyParts[1]*1 == 1) {
						var techLevelFrom = 1*rows[idx].children[2].children[0].value;
						var techLevelTo = 1*rows[idx].children[3].children[0].value;
					} else {
						var techLevelTo = 1*rows[idx].children[2].children[0].value;
						var techLevelFrom = techLevelTo == 0 ? 0 : techLevelTo - 1;
					}
					var techID = (rowID*1 > 10000)?(rowID*1 - 10000):(rowID*1);	// Здания на луне хранятся в таблицах с id на 10000 больше актуального
					var newTime = getBuildTime(techID, techLevelFrom, techLevelTo);
					var firstDataCol = (keyParts[1]*1 == 1)?4:3;
					// Если оказалось, что исследование невозможно выполнить, придётся стереть всю строку
					if (newTime > 0) {
						options.techData[key][4] = newTime;						
						$(rows[idx].children[firstDataCol+4]).html(timespanToShortenedString(newTime, options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true));
						var resCost = getBuildCost(techID, techLevelFrom, techLevelTo);
						var energyCost = getBuildEnergyCost(techID, techLevelTo);
						$(rows[idx].children[firstDataCol]).html(numberToShortenedString(resCost[0], options.unitSuffix));
						$(rows[idx].children[firstDataCol+1]).html(numberToShortenedString(resCost[1], options.unitSuffix));
						$(rows[idx].children[firstDataCol+2]).html(numberToShortenedString(resCost[2], options.unitSuffix));
						$(rows[idx].children[firstDataCol+3]).html(numberToShortenedString(energyCost, options.unitSuffix));

					} else {
						rows[idx].children[2].children[0].value = 0;
						if (keyParts[1]*1 == 1)
							rows[idx].children[3].children[0].value = 0;
						$(rows[idx].children[firstDataCol]).html('0');
						$(rows[idx].children[firstDataCol+1]).html('0');
						$(rows[idx].children[firstDataCol+2]).html('0');
						$(rows[idx].children[firstDataCol+3]).html('0');
						$(rows[idx].children[firstDataCol+4]).html('0'+options.datetimeS);
						$(rows[idx].children[firstDataCol+5]).html('0');
						showResearchImpossibleMessage($(rows[idx].children[1]).html());
					}
					needUpd[keyParts[1]] = true;
				}
			}
		}
	});
	updateNumbers(needUpd);
	// пусть заодно обновится и 3я вкладка - она достаточно маленькая, чтобы не заниматься уточнениями
	updateOneMultTab();
}

// Обновляет промежуточные и общие итоги на основании данных из глобального массива рассчитанных значений
function updateNumbers(needUpd) {
	for (var outer = 0; outer < 2; outer++) {
		// Если метод вызывается из updateParams(), то может быть запрошено обновление не всех вкладок
		if (needUpd && needUpd[outer] == false)
			continue;
		var innerNums = (outer == 0) ? [2, 3, 4, 5, 6] : [2, 3, 4];
		var firstDataCol = (outer == 0) ? 3 : 4;
		var grandTotals = [0, 0, 0, 0, 0, 0];
		for (var innerIdx = 0; innerIdx < innerNums.length; innerIdx++) {
			var inner = innerNums[innerIdx];
			var rows = $('#table-'+outer+'-'+inner+' tr');
			var totals = [0, 0, 0, 0, 0, 0];
			var takenFields = 0;
			for (var row = 1; row < rows.length-5; row++) {
				var techID = $(rows[row].children[0]).html();
				var buildingLevelCol = outer == 1 ? 3 : 2;
				takenFields += 1*$(rows[row].children[buildingLevelCol].children[0]).val();
				// Поищем в рассчитанных данных сведения об этой строке
				var rowKey = techID + '-' + outer + '-' + inner;
				if (options.techData[rowKey]) {
					totals[0] += options.techData[rowKey][0];
					totals[1] += options.techData[rowKey][1];
					totals[2] += options.techData[rowKey][2];
					totals[3] += options.techData[rowKey][3];
					totals[4] += options.techData[rowKey][4];
					totals[5] += options.techData[rowKey][5];
				}
			}
			$(rows[row].children[2]).html(innerIdx < 2 ? takenFields : '');
			$(rows[row].children[3]).html('<b>'+numberToShortenedString(totals[0], options.unitSuffix)+'</b>');
			$(rows[row].children[4]).html('<b>'+numberToShortenedString(totals[1], options.unitSuffix)+'</b>');
			$(rows[row].children[5]).html('<b>'+numberToShortenedString(totals[2], options.unitSuffix)+'</b>');
			$(rows[row].children[6]).html('<b>'+numberToShortenedString(totals[3], options.unitSuffix)+'</b>');
			$(rows[row].children[7]).html('<b>'+timespanToShortenedString(totals[4], options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true)+'</b>');
			$(rows[row].children[8]).html('<b>'+numberToShortenedString(totals[5], options.unitSuffix)+'</b>');
			var subTotalRes = totals[0] + totals[1] + totals[2];
			var needSC = Math.ceil(subTotalRes / 5000.0);
			var needLC = Math.ceil(subTotalRes / 25000.0);
			$(rows[row+1].children[2]).html(needSC + ' <abbr title="'+options.scFull+'">'+options.scShort+'</abbr>'); 
			$(rows[row+1].children[3]).html(needLC + ' <abbr title="'+options.lcFull+'">'+options.lcShort+'</abbr>');

			grandTotals[0] += totals[0];
			grandTotals[1] += totals[1];
			grandTotals[2] += totals[2];
			grandTotals[3] += totals[3];
			grandTotals[4] += totals[4];
			grandTotals[5] += totals[5];
		}
		// После того, как обработали все данные на внутренних вкладках, надо показать общий итог по данной внешней вкладке.
		// Запишем его во все таблицы внутренних вкладок, чтобы создать впечатление сквозной таблицы итогов.
		for (var innerIdx = 0; innerIdx < innerNums.length; innerIdx++) {
			var inner = innerNums[innerIdx];
			var rows = $('#table-'+outer+'-'+inner+' tr');
			var row = rows.length-2;
			$(rows[row].children[2]).html('<b>'+numberToShortenedString(grandTotals[0], options.unitSuffix)+'</b>');
			$(rows[row].children[3]).html('<b>'+numberToShortenedString(grandTotals[1], options.unitSuffix)+'</b>');
			$(rows[row].children[4]).html('<b>'+numberToShortenedString(grandTotals[2], options.unitSuffix)+'</b>');
			$(rows[row].children[5]).html('<b>'+numberToShortenedString(grandTotals[3], options.unitSuffix)+'</b>');
			$(rows[row].children[6]).html('<b>'+timespanToShortenedString(grandTotals[4], options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true)+'</b>');
			$(rows[row].children[7]).html('<b>'+numberToShortenedString(grandTotals[5], options.unitSuffix)+'</b>');
			var totalRes = grandTotals[0] + grandTotals[1] + grandTotals[2];
			var needSC = Math.ceil(totalRes / 5000.0);
			var needLC = Math.ceil(totalRes / 25000.0);
			$(rows[row+1].children[2]).html(needSC + ' ' + '<abbr title="'+options.scFull+'">'+options.scShort+'</abbr>');
			$(rows[row+1].children[3]).html(needLC + ' ' + '<abbr title="'+options.lcFull+'">'+options.lcShort+'</abbr>');
		}
	}
	options.save();
}

function getHourlyConsumption(techID, techLevel) {
	if (techLevel < 1)
		return 0;
	var universeSpeedFactor = $('#universe-speed')[0].value;
	var powerFactor = 1.0;
	var consump;
	switch (techID*1) {
		case 1: // рудник металла. потребляет энергию
		case 2: // рудник кристалла. потребляет энергию
			consump = Math.ceil(10.0 * techLevel * Math.pow(1.1, techLevel) * powerFactor);
			break;
		case 12: // термоядерная электростанция. потребляет дейтерий
			consump = Math.ceil(10.0 * techLevel * Math.pow(1.1, techLevel)) * universeSpeedFactor * powerFactor;
			break;
		case 3: // синтезатор дейтерия. потребляет энергию
			consump = Math.ceil(20.0 * techLevel * Math.pow(1.1, techLevel) * powerFactor);
			break;
		default:
			return 0;
	}
	return consump;
}

function updateOneMultTab() {
	var techID = $('#tech-types-select')[0].value;
	var idx = $('#tech-types-select')[0].selectedIndex;
	var techName = $('#tech-types-select')[0].options[idx].text;
	var isProducer = techID == 1 || techID == 2 || techID == 3 || techID == 4 || techID == 12 || techID == 212;
	var isConsumer = techID == 1 || techID == 2 || techID == 3 || techID == 12;
	var targetTable = '';
	if (isProducer) {
		$('#prods-table-div').show();
		$('#commons-table-div').hide();
		targetTable = 'prods-table';
	} else {
		$('#prods-table-div').hide();
		$('#commons-table-div').show();
		targetTable = 'commons-table';
	}
	var tbl = $('#'+targetTable)[0];
	var footer = $('#'+targetTable+' tr').slice(tbl.rows.length-3).detach();
	for (var i = tbl.rows.length-1; i > 0; i--) {
		$(tbl.rows[i]).remove();
	}
	
	var levelFrom = getInputNumber($('#tab2-from-level')[0]);
	var levelTo = getInputNumber($('#tab2-to-level')[0]);
	var buildTime = getBuildTime(techID, levelFrom, levelTo);
	// Если это исследование, и оно невозможно - покажем сообщение и рассчитаем пустую таблицу. Это нужно для того, чтобы проще обновить итоги.
	if (buildTime < 0) {
		$('#tab2-from-level')[0].value = 0;
		$('#tab2-to-level')[0].value = 0;
		levelFrom = 0;
		levelTo = 0;
		showResearchImpossibleMessage(techName);
	}

	if (techID == 0) {
		levelFrom = 0;
		levelTo = 0;
	}
	var resCost = [0, 0, 0];
	var totalMet = 0, totalCrys = 0, totalDeut = 0, energy = 0, maxEnrg = 0, totalTime = 0, production = 0, maxProd = 0, consumption = 0, maxCons = 0, points= 0, totalPts = 0, time = 0;
	var rowData = Array();
	var rowStr = '';
	for (var i = levelFrom; i < levelTo; i++) {
		rowData = Array();
		rowStr = '';
		rowData.push(i+1);
		resCost = getBuildCost(techID, i, i + 1);
		rowData.push(numberToShortenedString(resCost[0], options.unitSuffix));
		rowData.push(numberToShortenedString(resCost[1], options.unitSuffix));
		rowData.push(numberToShortenedString(resCost[2], options.unitSuffix));
		totalMet += resCost[0];
		totalCrys += resCost[1];
		totalDeut += resCost[2];
		energy = getBuildEnergyCost(techID, i + 1);
		rowData.push(numberToShortenedString(energy, options.unitSuffix));
		maxEnrg = Math.max(maxEnrg, energy);
		time = getBuildTime(techID, i, i + 1);
		rowData.push(timespanToShortenedString(time, options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true));
		totalTime += time;
		points = (1.0 * resCost[0] + 1.0 * resCost[1] + 1.0 * resCost[2]) / 1000.0;
		totalPts += points;
		rowData.push(numberToShortenedString(Math.round((resCost[0] + resCost[1] + resCost[2])/1000.0), options.unitSuffix));
		if (isProducer) {
			var energyTechLevel = getInputNumber($('#energy-tech-level')[0]);
			var plasmaTechLevel = 0;
			var maxTemp = getInputNumber($('#max-planet-temp')[0]);
			var uniSpeed = $('#universe-speed')[0].value;
			var booster = 0;
			var geologist = $('#geologist')[0].checked;
			var engineer = $('#engineer')[0].checked;
			production = getProductionRate(techID, i + 1, energyTechLevel, plasmaTechLevel, maxTemp, uniSpeed, geologist, engineer, 1, 1, booster);
			rowData.push(numberToShortenedString(production, options.unitSuffix));
			maxProd = Math.max(maxProd, production);
			// Производящие что-то здания могут потреблять или не потреблять, а остальным техам эта ячейка таблицы не нужна
			if (isConsumer) {
				consumption = getHourlyConsumption(techID, i + 1);
				rowData.push(numberToShortenedString(consumption, options.unitSuffix));
				maxCons = Math.max(maxCons, consumption);
			} else {
				rowData.push('-');
			}
		}

		var rowStr = '<tr class='+((i % 2) === 1 ? 'odd' : 'even')+'>';
		for (var cellNum = 0; cellNum < rowData.length; cellNum++) {
			rowStr += '<td align="center">'+rowData[cellNum]+'</td>';
		}
		rowStr += '</tr>';
		$('#'+targetTable).append(rowStr);
	}
	footer.appendTo('#'+targetTable);
	var rows = $('#'+targetTable+' tr');
	var totalsRow = rows.length - 2;
	rows[totalsRow].children[1].innerHTML = '<b>'+numberToShortenedString(totalMet, options.unitSuffix)+'</b>';
	rows[totalsRow].children[2].innerHTML = '<b>'+numberToShortenedString(totalCrys, options.unitSuffix)+'</b>';
	rows[totalsRow].children[3].innerHTML = '<b>'+numberToShortenedString(totalDeut, options.unitSuffix)+'</b>';
	rows[totalsRow].children[4].innerHTML = '<b>'+numberToShortenedString(maxEnrg, options.unitSuffix)+'</b>';
	rows[totalsRow].children[5].innerHTML = '<b>'+timespanToShortenedString(totalTime, options.datetimeW, options.datetimeD, options.datetimeH, options.datetimeM, options.datetimeS, true)+'</b>';
	rows[totalsRow].children[6].innerHTML = '<b>'+numberToShortenedString(Math.round(totalPts), options.unitSuffix)+'</b>';
	if (isProducer) {
		rows[totalsRow].children[7].innerHTML = '<b>'+numberToShortenedString(maxProd, options.unitSuffix)+'</b>';
		rows[totalsRow].children[8].innerHTML = '<b>'+numberToShortenedString(maxCons, options.unitSuffix)+'</b>';
	}
	var totalRes = totalMet + totalCrys + totalDeut;
	var needSC = Math.ceil(totalRes / 5000.0);
	var needLC = Math.ceil(totalRes / 25000.0);
	rows[totalsRow+1].children[1].innerHTML = numToOGame(needSC) + ' <abbr title="'+options.scFull+'">'+options.scShort+'</abbr>';
	rows[totalsRow+1].children[2].innerHTML = numToOGame(needLC) + ' <abbr title="'+options.lcFull+'">'+options.lcShort+'</abbr>';
	options.save();
}

function changePlanetsCount(newVal, oldVal) {
	if (newVal < options.minPlanetsCount || newVal > options.maxPlanetsCount)
		return;
	if (newVal < oldVal) {
		if (oldVal >= 2)
			$('#lab-levels-table tr:last').remove();
	} else {
		$('#lab-levels-table').append('<tr class="'+((newVal % 2) === 1 ? 'odd' : 'even')+'">'+
				'<td align="center" >'+options.planetNumStr+newVal+'</td>'+
				'<td align="center" width="20%;"><input type="text" id="lablevel_'+newVal+'" name="lablevel_'+newVal+'>" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>'+
				'<td align="center" width="20%;"><input type="radio" id="labchoice_'+newVal+'" name="start-pln" value="0" disabled="disabled"/></td>'+
				'</tr>');
		$('#lablevel_'+newVal).keyup('changeLabLevel', validateInputNumber);
		$('#labchoice_'+newVal).click(updateResultingLevel);
	}
	updateResultingLevel();
}

function changeLabLevel() {
	var parts = this.id.split(/_/);
	var num = parts[1];
	if (this.value == 0) {
		$('#labchoice_'+num)[0].disabled = 'disabled';
		$('#labchoice_'+num)[0].checked = false;
	}
	else
		$('#labchoice_'+num)[0].disabled = false;
	updateResultingLevel();
}

function compareLabs(a, b) {
	// Выбранную лабораторию (в которой будет запущено исследование) надо поднять наверх списка, т.к. она в любом случае участвует в исследовании.
	if (b[1] === true)
		return 1;
	if (a[1] === true)
		return -1;
	// Если ни одна из сравниваемых не выбрана, поднимем наверх ту лабораторию, у которой уровень больше
	return (b[0] - a[0]);
}

function updateResultingLevel() {
	var rows = $('#lab-levels-table tr');
	var haveSelection = false;
	var button = $('#done-btn')[0];
	for (var i = 1; i < rows.length; i++) {
		if ($('#labchoice_'+i)[0].checked) {
			haveSelection = true;
			break;
		}
	}
	if (!haveSelection) {
		$('#resulting-level')[0].innerHTML = '<b>?</b>';
		$(button).css('display', 'none');
		return;
	}
	var resultingLevel = getLabLevel(0);
	options.resultingLabLevel = resultingLevel;
	$('#resulting-level')[0].innerHTML = '<b>'+resultingLevel+'</b>';
	
	$(button).css('display', 'inline');
}

$(document).ready(function() {
	// этот вызов нужен, чтобы установить "скин" на чекбоксы и радиокнопки
	//$("div#costs input").filter(":checkbox,:radio").checkbox();
	$("#tabs").tabs({	cookie: {	expires: 365 } });	// UI сохраняет в куках номер открытой вкладки	
	$("#tabs-0").tabs({	cookie: {	expires: 365 } });
	$("#tabs-1").tabs({	cookie: {	expires: 365 } });
	
	$( "#irn-calc" ).dialog({
		autoOpen: false,
		height: 445,
		width: 400,
		modal: true,
		resizable: false,
		buttons: {
			dt: function() {
				$(this).dialog("option", "execute", true);
				$('#research-lab-level')[0].value = options.resultingLabLevel;
				options.resultingLabLevelComputed = true;
				updateParams.apply($('#research-lab-level')[0]);
				$(this).dialog("close");
			},
			ccl: function() {
				$(this).dialog("option", "execute", false);
				$(this).dialog( "close" );
			}
		},
		close: function() {
			if (!$(this).dialog("option", "execute")) {
				loadLLCData();
				updateResultingLevel();
			}
		}
	});
	
	//options.load();

	$('input').focusin(function() {
		$(this).addClass('ui-state-focus');
	});
	$('input').focusout(function() {
		$(this).removeClass('ui-state-focus');
	});

	// После того, как событие будет обработано, нужно вызвать функцию пересчета. Её имя передаём в поле data событий.
	$('#irn-calc input:text').keyup('changeLabLevel', validateInputNumber);
	$('#irn-calc input:radio').click(updateResultingLevel);
	
	$('#irn-level').unbind();
	$('#irn-level').keyup('updateResultingLevel', validateInputNumber);
	// При изменении значения уровня лаборатории вручную надо запомнить это
	$('#research-lab-level').keyup(function(){ 
		options.resultingLabLevelComputed = false; updateParams.apply($('#research-lab-level')[0]);
	});
	
	$('#max-planet-temp').data('constrains', {'min': -134, 'def': 0, 'allowNegative': true});

	// После того, как событие будет обработано, нужно вызвать функцию пересчета. Её имя передаём в поле data событий.
	$('#tab-0 input:text').keyup('updateRow', validateInputNumber);
	$('#tab-1 input:text').keyup('updateRow', validateInputNumber);
	$('#tab-2 input:text').keyup('updateOneMultTab', validateInputNumber);
	$('#tab-2 input:text').blur('updateOneMultTab', validateInputNumberOnBlur);

	$('#tab-0 input:text').each(function()
	{
		$(this).keyup();
	});

	$('#general-settings input:text').keyup('updateParams', validateInputNumber);
	$('#general-settings select').keyup(updateParams);
	$('#general-settings select').change(updateParams);
	$('#technocrat').click(updateParams);
	$('#arhitector').click(updateParams);
	$('#admiral').click(updateParams);
	$('#open-llc-dialog').click(function() {
		$("#irn-calc").dialog("option", "execute", false);
		$("#irn-calc").dialog( "open" );
	});
	
	$('#engineer').click(updateParams);
	$('#geologist').click(updateParams);
	$('#reset').click(resetParams);

	$('#tech-types-select').unbind();
	$('#tech-types-select').keyup(updateOneMultTab);
	$('#tech-types-select').change(updateOneMultTab);
	
	//$('#booster').unbind();
	//$('#booster').keyup(updateOneMultTab);
	//$('#booster').change(updateOneMultTab);

	
	$("#planetsSpin").unbind();
	var spinOptions = { min: 1, max: 99, step: 1, reset: 1, lock: true, onChange: changePlanetsCount };
	$("#planetsSpin").SpinButton(spinOptions);
	$('#planetsSpin')[0].value = options.currPlanetsCount;
	
	updateNumbers();
	updateOneMultTab();
});
