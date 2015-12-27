<script type="text/javascript" src="<?=RPATH ?>scripts/jquery.cookie.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/jquery.ui.spinbtn.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/jquery.inputmask.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/utils.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/common.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/flight.js"></script>

<link type="text/css" href="/scripts/calculate/common.css" rel="stylesheet"/>

<style>

	#flight { margin: auto; width: 100%; }
	.level-input { width: 40px !important; text-align: center; margin-left: 2px !important; margin-right: 8px !important; }
	.level-input-small { width: 30px !important; text-align: center; margin-left: 2px !important; margin-right: 8px !important; }
	.coord-input { width: 30px !important; text-align: center; margin-left: 0px !important; margin-right: 0px !important; }
	.coord-input-small { width: 20px !important; text-align: center; margin-left: 0px; margin-right: 0px; }
	.count-input { width: 60px !important; text-align: center; margin-left: 2px !important; margin-right: 12px !important; }
	.speed-label { font-size: 0.8em; margin: 8px;}
	.startdate-input { width: 120px !important; text-align: center; margin: 2px !important;}
	.flight-time-input { width: 80px; text-align: center; margin: 2px;}
	.tolerance-time-input { width: 50px; text-align: center; margin: 2px;}
	.button-remove, .button-toggle { padding: 0px; cursor: pointer; margin: 0px; }
	.button-taketocalc { cursor: pointer; }

	#tabtag1 { padding: .1em 1em; }
	#tabtag2 { padding: .1em 1em; }
	#flight-times-panel { padding: 0px; }
	#save-points-panel { padding: 3px; }

	#set-save-departure-now span { display: inline; padding: 0px; font-weight: lighter;}
	#calculate-savepoints span { display: inline; }
	#hint { background: #E2F2FF; border: 1px solid #CFDFEC;}

</style>

<? global $resource; ?>

	<script type="text/javascript">
		$(function() {
			$("#tabs").tabs({	cookie: {	expires: 365, path: '/' } });	// UI сохраняет в куках номер открытой вкладки
			$("button").button();

			$("#start-datetime").inputmask("d.m.y H:s:s");
			$("#flight-time").inputmask("99 H:s:s");
			$("#save-start-datetime").inputmask("d.m.y H:s:s", { "oncomplete": function(){ $("#save-return-datetime")[0].focus(); } });
			$("#save-return-datetime").inputmask("d.m.y H:s:s", { "oncomplete": function(){ $("#save-tolerance-time")[0].focus(); } });
			$("#save-tolerance-time").inputmask("H:s", { "oncomplete": function(){ $("#calculate-savepoints")[0].focus(); } });
		});
		// десятичный разделитель будет использоваться в функциях, проверяющих валидность чисел в input-ах
		options.decimalSeparator=',';
		options.datetimeW = 'нд';
		options.datetimeD = 'д';
		options.datetimeH = 'ч';
		options.datetimeM = 'м';
		options.datetimeS = 'с';
		options.datetimeFormat = 'd.m.y H:s:s';
		options.flightTimeFormat = '99 H:s:s';
		options.flightTimeFormatHint = 'дд чч:мм:сс';
		options.toggleSignHint = 'Изменить знак';
		options.removeRowHint = 'Удалить строку';
		options.departureTitle = 'Отправление';
		options.arrivalTitle = 'Прибытие';
		options.warnindDivId = 'warning';
		options.warnindMsgDivId = 'warning-message';
		options.fieldHint = 'поля [{0}]';
		options.msgMinConstraintViolated = 'Введённое значение {0} {1} меньше минимального {2}. Установлено минимальное значение поля.';
		options.msgMaxConstraintViolated = 'Введённое значение {0} {1} больше максимального {2}. Установлено максимальное значение поля.';
		options.msgNoShips = "Нет кораблей в отправляемом флоте.";
		options.msgWrongDepartureTime = "Неверное время отправления.";
		options.msgWrongReturnTime = "Неверное время возврата.";
		options.msgDepartureAfterReturn = "Дата отправления не может быть позже даты возврата.";
		options.msgWrongTolerance = "Неверное значение максимальной погрешности.";
		options.msgWrongDepartureCoordinates = "Координаты пункта отправления заданы неверно.";
		options.msgNoSavepointsFound = "Подходящих для сейва координат не найдено.";
		options.flightmodesNote = "Для удобства подбора момента отправления при известном моменте прибытия переключите режим калькулятора времени. В режиме Прибытие-Полёт-Отправление новые строки добавляются в таблицу калькулятора со знаком -, установленным автоматически.";
		options.savepointsNote = "Когда будут найдены подходящие для сейва координаты, щёлкните по ссылке в любой строке таблицы. Откроется вкладка Время в пути с заполненными данными о полёте туда и обратно.";
	</script>

<div id="flight">
	<div class="ui-widget-content ui-corner-all">
		<div id="reset" class="ui-state-error ui-corner-all" title="Сброс"><span class="ui-icon ui-icon-arrowrefresh-1-w"></span></div>
		<div class="ui-widget-header ui-corner-all c-ui-main-header">Калькулятор времени полёта</div>
		<div>
			<div id="general-settings-panel" class="ui-widget-content c-ui-widget-content ui-corner-all ui-panel">
				<div id="general-settings">
					<table cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td><label for="cmb-drive">Рак. двигатель</label></td>
							<td><input id="cmb-drive" type="text" name="cmb-drive" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$user[$resource[115]]?>" tabindex="1" /></td>
							<td><label for="imp-drive">Имп. двигатель</label></td>
							<td><input id="imp-drive" type="text" name="imp-drive" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$user[$resource[117]]?>" tabindex="2" /></td>
							<td><label for="hyp-drive">Гипер. двигатель</label></td>
							<td><input id="hyp-drive" type="text" name="hyp-drive" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$user[$resource[118]]?>" tabindex="3" /></td>
							<td></td>
							<td>
								<input type="hidden" id="universe-speed" name="universe-speed" value="<?=(\Xcms\Core::getConfig('fleet_speed')/ 2500)?>">

								<input type="checkbox" name="race" value="1" id="race"><label for="race">Древние</label>
								<input type="checkbox" name="officier" value="1" id="officier"><label for="officier">Адмирал</label>
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td><label for="departure-g">Пункт отправления&nbsp;</label></td>
							<td>
								<input id="departure-g" type="text" name="departure-g" class="ui-state-default ui-corner-all ui-input coord-input-small ui-input-margin" value="<?=$planet['galaxy'] ?>" alt="Пункт отправления-Галактика" tabindex="5" />:<input id="departure-s" type="text" name="departure-s" class="ui-state-default ui-corner-all ui-input coord-input ui-input-margin" value="<?=$planet['system'] ?>" alt="Пункт отправления-Система" tabindex="6" />:<input id="departure-p" type="text" name="departure-p" class="ui-state-default ui-corner-all ui-input coord-input-small ui-input-margin" value="<?=$planet['planet'] ?>" alt="Пункт отправления-Планета" tabindex="7" />
							</td>
							<td style="width: 30px;">&nbsp;</td>
							<td><label for="esp-probe">Шпионский зонд</label></td>
							<td><label id="esp-probe-speed" class="speed-label">1 000 000</label></td>
							<td><input id="esp-probe" type="text" name="esp-probe" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="8" /></td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td><label for="small-cargo">Малый транспорт</label></td>
							<td><label id="small-cargo-speed" class="speed-label">0</label></td>
							<td><input id="small-cargo" type="text" name="small-cargo" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="9" /></td>

							<td><label for="cruiser">Крейсер</label></td>
							<td><label id="cruiser-speed" class="speed-label">0</label></td>
							<td><input id="cruiser" type="text" name="cruiser" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="13" /></td>

							<td><label for="battlecruiser">Линейный крейсер</label></td>
							<td><label id="battlecruiser-speed" class="speed-label">0</label></td>
							<td><input id="battlecruiser" type="text" name="battlecruiser" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="17" /></td>
						</tr>
						<tr>
							<td><label for="large-cargo">Большой транспорт</label></td>
							<td><label id="large-cargo-speed" class="speed-label">0</label></td>
							<td><input id="large-cargo" type="text" name="large-cargo" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="10" /></td>

							<td><label for="battleship">Линкор</label></td>
							<td><label id="battleship-speed" class="speed-label">0</label></td>
							<td><input id="battleship" type="text" name="battleship" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="14" /></td>

							<td><label for="death-star">Звезда смерти</label></td>
							<td><label id="death-star-speed" class="speed-label">0</label></td>
							<td><input id="death-star" type="text" name="death-star" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="18" /></td>
						</tr>
						<tr>
							<td><label for="light-fighter">Лёгкий истребитель</label></td>
							<td><label id="light-fighter-speed" class="speed-label">0</label></td>
							<td><input id="light-fighter" type="text" name="light-fighter" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="11" /></td>

							<td><label for="destroyer">Уничтожитель</label></td>
							<td><label id="destroyer-speed" class="speed-label">0</label></td>
							<td><input id="destroyer" type="text" name="destroyer" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="15" /></td>

							<td><label for="colony-ship">Колонизатор</label></td>
							<td><label id="colony-ship-speed" class="speed-label">0</label></td>
							<td><input id="colony-ship" type="text" name="colony-ship" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="19" /></td>
						</tr>
						<tr>
							<td><label for="heavy-fighter">Тяжёлый истребитель</label></td>
							<td><label id="heavy-fighter-speed" class="speed-label">0</label></td>
							<td><input id="heavy-fighter" type="text" name="heavy-fighter" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="12" /></td>

							<td><label for="bomber">Бомбардировщик</label></td>
							<td><label id="bomber-speed" class="speed-label">0</label></td>
							<td><input id="bomber" type="text" name="bomber" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="16" /></td>

							<td><label for="recycler">Переработчик</label></td>
							<td><label id="recycler-speed" class="speed-label">0</label></td>
							<td><input id="recycler" type="text" name="recycler" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="20" /></td>
						</tr>
						<tr>
							<td><label for="fly_base">Передвижная база</label></td>
							<td><label id="fly_base-speed" class="speed-label">0</label></td>
							<td><input id="fly_base" type="text" name="fly_base" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="21" /></td>

							<td><label for="corvete">Корвет</label></td>
							<td><label id="corvete-speed" class="speed-label">0</label></td>
							<td><input id="corvete" type="text" name="corvete" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="22" /></td>

							<td><label for="interceptor">Перехватчик</label></td>
							<td><label id="interceptor-speed" class="speed-label">0</label></td>
							<td><input id="interceptor" type="text" name="interceptor" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="23" /></td>
						</tr>
						<tr>
							<td><label for="dreadnought">Дредноут</label></td>
							<td><label id="dreadnought-speed" class="speed-label">0</label></td>
							<td><input id="dreadnought" type="text" name="dreadnought" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="24" /></td>

							<td><label for="corsair">Корсар</label></td>
							<td><label id="corsair-speed" class="speed-label">0</label></td>
							<td><input id="corsair" type="text" name="corsair" class="ui-state-default ui-corner-all ui-input count-input ui-input-margin" value="0" tabindex="25" /></td>
				</tr>
					</table>
				</div>
			</div>
			<div id="tabs">
				<div id="flight-times-panel">
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td valign="top">
								<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
									<tr>
										<td><label for="destination-g">Пункт назначения&nbsp;</label></td>
										<td>
											<input id="destination-g" type="text" name="destination-g" class="ui-state-default ui-corner-all ui-input coord-input-small ui-input-margin" value="1" alt="Пункт назначения-Галактика" tabindex="23" />:<input id="destination-s" type="text" name="destination-s" class="ui-state-default ui-corner-all ui-input coord-input ui-input-margin" value="1" alt="Пункт назначения-Система" tabindex="24" />:<input id="destination-p" type="text" name="destination-p" class="ui-state-default ui-corner-all ui-input coord-input-small ui-input-margin" value="1" alt="Пункт назначения-Планета" tabindex="25" />
										</td>
										<td style="width: 30px;">&nbsp;</td>
										<td><label>Расстояние</label></td>
										<td style="width: 10px;">&nbsp;</td>
										<td><label id="distance"></label></td>
									</tr>
								</table>
								<table id="flight-times" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
									<tr>
										<th>Скорость</th>
										<th>Длительность полёта</th>
										<th>Затраты дейтерия</th>
										<th>Грузоподъёмность</th>

									</tr>
																		<tr class="even" style="height: 18px;">
										<td align="center">100%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="odd" style="height: 18px;">
										<td align="center">90%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="even" style="height: 18px;">
										<td align="center">80%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="odd" style="height: 18px;">
										<td align="center">70%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="even" style="height: 18px;">
										<td align="center">60%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="odd" style="height: 18px;">
										<td align="center">50%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="even" style="height: 18px;">
										<td align="center">40%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="odd" style="height: 18px;">
										<td align="center">30%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="even" style="height: 18px;">
										<td align="center">20%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
																		<tr class="odd" style="height: 18px;">
										<td align="center">10%</td>
										<td align="center"></td>
										<td align="center"></td>
										<td align="center"></td>

									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div id="warning" class="ui-state-highlight ui-corner-all">
		<div id="warning-message">msg</div>
	</div>

</div>