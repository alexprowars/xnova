<script type="text/javascript" src="<?=RPATH ?>scripts/jquery.cookie.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/jquery.ui.spinbtn.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/utils.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/common.js"></script>
<script type="text/javascript" src="<?=RPATH ?>scripts/calculate/costs.js"></script>

<link type="text/css" href="<?=RPATH ?>scripts/calculate/common.css" rel="stylesheet"/>

<style>
	#costs { margin: auto; width: 100%; }
	.ui-subheader, .ui-widget-content .ui-subheader { margin-bottom: 7px; }
	.level-input { width: 40px !important; text-align: center; margin-left: 2px !important; margin-right: 8px !important; 	border: 1px solid #79B7E7 !important;
		color: #1D5987 !important;
		font-weight: 700 !important; }
	.fleet-input { width: 50px !important; text-align: center; margin-left: 2px !important; margin-right: 8px !important; border: 1px solid #79B7E7 !important; color: #1D5987 !important;
			font-weight: 700 !important; }
	#technocrat, #engineer, #geologist, #admiral, #arhitector { margin-left: 0px; margin-right: 5px;}


	#tab-0, #tab-1, #tab-2 { padding: 0em; }
	#tab-0-2, #tab-0-3, #tab-0-4, #tab-0-5, #tab-0-6, #tab-1-2, #tab-1-3, #tab-1-4 { padding: .3em 0em; }


	#irn-calc { margin: auto; width: 350px; }
	div.irn-calc-info {padding: 3px; text-align: center; }
	#research-lab-level { margin-right: 2px; }
	#open-llc-dialog { margin-right: 8px; vertical-align: text-top;}
	#lab-levels-div { height: 228px; overflow: auto;}
	#hint { background: #E2F2FF; border: 1px solid #CFDFEC;}
</style>

<script type="text/javascript">
	// десятичный разделитель будет использоваться в функциях, проверяющих валидность чисел в input-ах
	options.decimalSeparator = ',';
	options.datetimeW = 'нд';
	options.datetimeD = 'д';
	options.datetimeH = 'ч';
	options.datetimeM = 'м';
	options.datetimeS = 'с';
	options.unitSuffix = 'КМГ';
	options.scShort = 'МТ';
	options.lcShort = 'БТ';
	options.scFull = 'Малый транспорт';
	options.lcFull = 'Большой транспорт';
	options.warnindDivId = 'warning';
	options.warnindMsgDivId = 'warning-message';
	options.fieldHint = 'поля [{0}]';
	options.planetNumStr = 'Планета №';
	options.doneTitle = 'Готово';
	options.cancelTitle = 'Отмена';
	options.msgMinConstraintViolated = 'Введённое значение {0} {1} меньше минимального {2}. Установлено минимальное значение поля.';
	options.msgMaxConstraintViolated = 'Введённое значение {1} больше максимального {2}. Установлено максимальное значение поля.';
	options.msgCantResearch = 'При данном уровне Исследовательской лаборатории исследование "{0}" невозможно.';

	options.techCosts = {
		<? global $pricelist, $resource;

		 foreach ($pricelist AS $id => $price)
		 {
		 	echo ''.$id.': ['.$price['metal'].', '.$price['crystal'].', '.$price['deuterium'].', '.$price['factor'].'],';
		 }

		 ?>
		999: []
	};
	options.techReqs = {
		106: 3, 108: 1, 109: 4, 110: 6, 111: 2, 113: 1, 114: 7, 115: 1, 117: 2, 118: 7, 120: 1, 121: 4, 122: 4, 123: 10, 124: 3, 199: 12                                        };

</script>

<div id="irn-calc" title="Расчёт уровня лаборатории">
	<div class="ui-widget-content ui-corner-all width: auto; ">
		<div>
			<table align="center">
				<tr>
					<td>Уровень М.И.С.</td>
					<td><input id="irn-level" type="text" name="irn-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0" /></td>
					<td>Количество планет</td>
					<td>
					<input id="planetsSpin" type="text" class="ui-corner-all input-2columns spin-button" value="8" />
					</td>
				</tr>
			</table>
		</div>
		<div class="irn-calc-info">
			Укажите уровни исследовательских лабораторий и выберите планету, на которой будут запускаться исследования.		</div>
		<div id="lab-levels-div">
			<table id="lab-levels-table" class="lined" width="100%;" cellpadding="0" cellspacing="1" border="0">
				<tr>
					<th>Планета</th><th>Уровень</th><th>Запуск</th>
				</tr>
								<tr class="odd">
					<td align="center">Планета №1</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_1" name="lablevel_1" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_1" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="even">
					<td align="center">Планета №2</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_2" name="lablevel_2" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_2" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="odd">
					<td align="center">Планета №3</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_3" name="lablevel_3" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_3" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="even">
					<td align="center">Планета №4</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_4" name="lablevel_4" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_4" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="odd">
					<td align="center">Планета №5</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_5" name="lablevel_5" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_5" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="even">
					<td align="center">Планета №6</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_6" name="lablevel_6" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_6" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="odd">
					<td align="center">Планета №7</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_7" name="lablevel_7" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_7" name="start-pln" disabled="disabled"/></td>
				</tr>
								<tr class="even">
					<td align="center">Планета №8</td>
					<td align="center" width="20%;"><input type="text" id="lablevel_8" name="lablevel_8" class="ui-state-default ui-corner-all ui-input input-3columns input-in-table" value="0" /></td>
					<td align="center" width="20%;"><input type="radio" id="labchoice_8" name="start-pln" disabled="disabled"/></td>
				</tr>
							</table>
		</div>
		<div class="irn-calc-info">
			<span>Итоговый уровень лаборатории:</span>&nbsp;<span id="resulting-level"><b>?</b></span>
		</div>
	</div>
</div>

<div id="costs">
<div class="ui-widget-content ui-corner-all">
<div id="reset" class="ui-state-error ui-corner-all" title="Сброс"><span class="ui-icon ui-icon-arrowrefresh-1-w"></span></div>
<div class="ui-widget-header ui-corner-all c-ui-main-header">OGame - Расчёт стоимости</div>
<div>
<div id="general-settings-panel" class="ui-widget-content c-ui-widget-content ui-corner-all ui-panel">
	<div id="general-settings">
		<table class="table" cellpadding="2" cellspacing="0" border="0" align="center">
			<tr>
				<td width="200"><label for="robot-factory-level">Фабрика роботов</label></td>
				<td>
					<input id="robot-factory-level" type="text" name="robot-factory-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$planet[$resource[14]] ?>"/>
				</td>
				<td><label for="nanite-factory-level">Фабрика нанитов</label></td>
				<td>
					<input id="nanite-factory-level" type="text" name="nanite-factory-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$planet[$resource[15]] ?>"/>
				</td>
				<td><label for="shipyard-level">Верфь</label></td>
				<td>
					<input id="shipyard-level" type="text" name="shipyard-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$planet[$resource[21]] ?>"/>
				</td>
			</tr>
			<tr>
				<td><label for="research-lab-level">Исследовательская лаборатория</label></td>
				<td>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<input id="research-lab-level" type="text" name="research-lab-level" class="ui-state-default ui-corner-all ui-input level-input" value="<?=$planet[$resource[31]] ?>"/>
							</td>
							<td>
								<div id="open-llc-dialog" class="ui-state-default ui-corner-all" title="Рассчитать">
									<span class="ui-icon ui-icon-calculator"></span></div>
							</td>
						</tr>
					</table>
				</td>
				<td>
					<input id="technocrat" type="checkbox" name="technocrat" class="ui-state-default ui-corner-all ui-input ui-input-margin"/><label for="technocrat">Технократ</label>
					<br><input id="arhitector" type="checkbox" name="arhitector" class="ui-state-default ui-corner-all ui-input ui-input-margin"/><label for="arhitector">Архитектор</label>
					<br>					<input id="geologist" type="checkbox" name="geologist" class="ui-state-default ui-corner-all ui-input ui-input-margin"/><label for="geologist">Геолог</label>
					<br>					<input id="engineer" type="checkbox" name="engineer" class="ui-state-default ui-corner-all ui-input ui-input-margin"/><label for="engineer">Инженер</label>
					<br>					<input id="admiral" type="checkbox" name="admiral" class="ui-state-default ui-corner-all ui-input ui-input-margin"/><label for="admiral">Адмирал</label>

				</td>
				<td></td>
				<td></td>
				<td>
					<input type="hidden" id="universe-speed" name="universe-speed" value="<?=\Xnova\system::getGameSpeed() ?>">
				</td>
			</tr>
		</table>
	</div>
</div>
<div id="tabs">
<ul>
	<li><a id="tabtag-0" href="#tab-0">Все элементы - один уровень</a></li>
	<li><a id="tabtag-1" href="#tab-1">Все элементы - неск. уровней</a></li>
	<li><a id="tabtag-2" href="#tab-2">Один элемент - неск. уровней</a></li>
</ul>
<div id="tab-0" class="ui-panel no-mp">
<div id="tabs-0" class="no-mp">
<ul>
	<li><a id="tabtag-0-2" href="#tab-0-2">Постройки (планета)</a></li>
	<li><a id="tabtag-0-3" href="#tab-0-3">Постройки (луна)</a></li>
	<li><a id="tabtag-0-4" href="#tab-0-4">Исследования</a></li>
	<li><a id="tabtag-0-5" href="#tab-0-5">Флот</a></li>
	<li><a id="tabtag-0-6" href="#tab-0-6">Оборона</a></li>
</ul>
<div id="tab-0-2" class="ui-panel no-mp">
<table id="table-0-2" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
<tr>
	<th style="display: none;"></th>
	<th>Постройка</th>
	<th align="center">Уровень</th>
	<th align="center">Металл</th>
	<th align="center">Кристалл</th>
	<th align="center">Дейтерий</th>
	<th align="center">Энергия</th>
	<th align="center">Время</th>
	<th align="center">Очки</th>
</tr>

<? global $reslist; ?>
<? $i = 0; foreach ($reslist['allowed'][1] AS $id): ?>
	<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
		<td style="display: none;"><?=$id ?></td>
		<td class="title"><?=_getText('tech', $id) ?></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($planet['planet_type'] == 1 ? ($planet[$resource[$id]] + 1) : 0) ?>"/></td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0с</td>
		<td align="center">0</td>
	</tr>
<? $i++; endforeach; ?>

<tr class="odd">
	<td style="display: none;"></td>
	<td colspan="1" class="border-n">Итого</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-s border-w"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s border-e"><b>0</b></td>
</tr>
<tr class="odd">
	<td style="display: none;"></td>
	<td>Для доставки требуется</td>
	<td align="center">0 МТ</td>
	<td align="center">0 БТ</td>
	<td colspan="5"></td>
</tr>
<tr>
	<td colspan="9" height=5px;>&nbsp;</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td colspan="2" class="border-n border-w">Всего</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-e">0</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td class="border-s border-w">Для доставки требуется</td>
	<td align="center" class="border-s">0 МТ</td>
	<td align="center" class="border-s">0 БТ</td>
	<td colspan="4" align="center" class="border-s">&nbsp;</td>
	<td align="center" class="border-s border-e">&nbsp;</td>
</tr>
</table>
</div>
<div id="tab-0-3" class="ui-panel no-mp">
	<table id="table-0-3" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr>
			<th style="display: none;"></th>
			<th>Постройка</th>
			<th align="center">Уровень</th>
			<th align="center">Металл</th>
			<th align="center">Кристалл</th>
			<th align="center">Дейтерий</th>
			<th align="center">Энергия</th>
			<th align="center">Время</th>
			<th align="center">Очки</th>
		</tr>
		<? $i = 0; foreach ($reslist['allowed'][3] AS $id): ?>
			<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
				<td style="display: none;">100<?=$id ?></td>
				<td class="title"><?=_getText('tech', $id) ?></td>
				<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($planet['planet_type'] == 3 ? ($planet[$resource[$id]] + 1) : 0) ?>"/></td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0с</td>
				<td align="center">0</td>
			</tr>
		<? $i++; endforeach; ?>
		<tr class="even">
			<td style="display: none;"></td>
			<td colspan="1" class="border-n">Итого</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-s border-w"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s border-e"><b>0</b></td>
		</tr>
		<tr class="even">
			<td style="display: none;"></td>
			<td>Для доставки требуется</td>
			<td align="center">0 МТ</td>
			<td align="center">0 БТ</td>
			<td colspan="5"></td>
		</tr>
		<tr>
			<td colspan="9" height=5px;>&nbsp;</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td colspan="2" class="border-n border-w">Всего</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-e">0</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td class="border-s border-w">Для доставки требуется</td>
			<td align="center" class="border-s">0 МТ</td>
			<td align="center" class="border-s">0 БТ</td>
			<td colspan="4" align="center" class="border-s">&nbsp;</td>
			<td align="center" class="border-s border-e">&nbsp;</td>
		</tr>
	</table>
</div>
<div id="tab-0-4" class="ui-panel no-mp">
<table id="table-0-4" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
<tr>
	<th style="display: none;"></th>
	<th>Исследование</th>
	<th align="center">Уровень</th>
	<th align="center">Металл</th>
	<th align="center">Кристалл</th>
	<th align="center">Дейтерий</th>
	<th align="center">Энергия</th>
	<th align="center">Время</th>
	<th align="center">Очки</th>
</tr>
<? $i = 0; foreach ($reslist['tech'] AS $id): ?>
	<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
		<td style="display: none;"><?=$id ?></td>
		<td class="title"><?=_getText('tech', $id) ?></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($user[$resource[$id]] + 1) ?>"/></td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0с</td>
		<td align="center">0</td>
	</tr>
<? $i++; endforeach; ?>
<? foreach ($reslist['tech_f'] AS $id): ?>
	<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
		<td style="display: none;"><?=$id ?></td>
		<td class="title"><?=_getText('tech', $id) ?></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($user[$resource[$id]] + 1) ?>"/></td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0с</td>
		<td align="center">0</td>
	</tr>
<? $i++; endforeach; ?>
<tr class="odd">
	<td style="display: none;"></td>
	<td colspan="1" class="border-n">Итого</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-s border-w"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s border-e"><b>0</b></td>
</tr>
<tr class="odd">
	<td style="display: none;"></td>
	<td>Для доставки требуется</td>
	<td align="center">0 МТ</td>
	<td align="center">0 БТ</td>
	<td colspan="5"></td>
</tr>
<tr>
	<td colspan="9" height=5px;>&nbsp;</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td colspan="2" class="border-n border-w">Всего</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-e">0</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td class="border-s border-w">Для доставки требуется</td>
	<td align="center" class="border-s">0 МТ</td>
	<td align="center" class="border-s">0 БТ</td>
	<td colspan="4" align="center" class="border-s">&nbsp;</td>
	<td align="center" class="border-s border-e">&nbsp;</td>
</tr>
</table>
</div>
<div id="tab-0-5" class="ui-panel no-mp">
	<table id="table-0-5" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr>
			<th style="display: none;"></th>
			<th>Корабль</th>
			<th align="center">Количество</th>
			<th align="center">Металл</th>
			<th align="center">Кристалл</th>
			<th align="center">Дейтерий</th>
			<th align="center">Энергия</th>
			<th align="center">Время</th>
			<th align="center">Очки</th>
		</tr>
		<? $i = 0; foreach ($reslist['fleet'] AS $id): ?>
			<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
				<td style="display: none;"><?=$id ?></td>
				<td class="title"><?=_getText('tech', $id) ?></td>
				<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$planet[$resource[$id]] ?>"/></td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0с</td>
				<td align="center">0</td>
			</tr>
		<? $i++; endforeach; ?>
		<tr class="odd">
			<td style="display: none;"></td>
			<td colspan="1" class="border-n">Итого</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-s border-w"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s border-e"><b>0</b></td>
		</tr>
		<tr class="odd">
			<td style="display: none;"></td>
			<td>Для доставки требуется</td>
			<td align="center">0 МТ</td>
			<td align="center">0 БТ</td>
			<td colspan="5"></td>
		</tr>
		<tr>
			<td colspan="9" height=5px;>&nbsp;</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td colspan="2" class="border-n border-w">Всего</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-e">0</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td class="border-s border-w">Для доставки требуется</td>
			<td align="center" class="border-s">0 МТ</td>
			<td align="center" class="border-s">0 БТ</td>
			<td colspan="4" align="center" class="border-s">&nbsp;</td>
			<td align="center" class="border-s border-e">&nbsp;</td>
		</tr>
	</table>
</div>
<div id="tab-0-6" class="ui-panel no-mp">
	<table id="table-0-6" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr>
			<th style="display: none;"></th>
			<th>Постройка</th>
			<th align="center">Количество</th>
			<th align="center">Металл</th>
			<th align="center">Кристалл</th>
			<th align="center">Дейтерий</th>
			<th align="center">Энергия</th>
			<th align="center">Время</th>
			<th align="center">Очки</th>
		</tr>
		<? $i = 0; foreach ($reslist['defense'] AS $id): ?>
			<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
				<td style="display: none;"><?=$id ?></td>
				<td class="title"><?=_getText('tech', $id) ?></td>
				<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$planet[$resource[$id]] ?>"/></td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0с</td>
				<td align="center">0</td>
			</tr>
		<? $i++; endforeach; ?>
		<tr class="odd">
			<td style="display: none;"></td>
			<td colspan="1" class="border-n">Итого</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-s border-w"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s border-e"><b>0</b></td>
		</tr>
		<tr class="odd">
			<td style="display: none;"></td>
			<td>Для доставки требуется</td>
			<td align="center">0 МТ</td>
			<td align="center">0 БТ</td>
			<td colspan="5"></td>
		</tr>
		<tr>
			<td colspan="9" height=5px;>&nbsp;</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td colspan="2" class="border-n border-w">Всего</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-e">0</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td class="border-s border-w">Для доставки требуется</td>
			<td align="center" class="border-s">0 МТ</td>
			<td align="center" class="border-s">0 БТ</td>
			<td colspan="4" align="center" class="border-s">&nbsp;</td>
			<td align="center" class="border-s border-e">&nbsp;</td>
		</tr>
	</table>
</div>
</div>
</div>
<div id="tab-1" class="ui-panel no-mp">
<div id="tabs-1" class="no-mp">
<ul>
	<li><a id="tabtag-1-2" href="#tab-1-2">Постройки (планета)</a></li>
	<li><a id="tabtag-1-3" href="#tab-1-3">Постройки (луна)</a></li>
	<li><a id="tabtag-1-4" href="#tab-1-4">Исследования</a></li>
</ul>
<div id="tab-1-2" class="ui-panel no-mp">
<table id="table-1-2" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
<tr>
	<th style="display: none;"></th>
	<th>Постройка</th>
	<th align="center">Уровень</th>
	<th align="center">До уровня</th>
	<th align="center">Металл</th>
	<th align="center">Кристалл</th>
	<th align="center">Дейтерий</th>
	<th align="center">Энергия</th>
	<th align="center">Время</th>
	<th align="center">Очки</th>
</tr>
<? $i = 0; foreach ($reslist['allowed'][1] AS $id): ?>
	<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
		<td style="display: none;"><?=$id ?></td>
		<td class="title"><?=_getText('tech', $id) ?></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($planet['planet_type'] == 1 ? $planet[$resource[$id]] : 0) ?>"/></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/></td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0с</td>
		<td align="center">0</td>
	</tr>
<? $i++; endforeach; ?>
<tr class="odd">
	<td style="display: none;"></td>
	<td colspan="2" class="border-n">Итого</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-s border-w"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s border-e"><b>0</b></td>
</tr>
<tr class="odd">
	<td style="display: none;"></td>
	<td>Для доставки требуется</td>
	<td align="center">0 МТ</td>
	<td align="center">0 БТ</td>
	<td colspan="6"></td>
</tr>
<tr>
	<td colspan="10" height=5px;>&nbsp;</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td colspan="3" class="border-n border-w">Всего</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-e">0</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td class="border-s border-w">Для доставки требуется</td>
	<td align="center" class="border-s">0 МТ</td>
	<td align="center" class="border-s">0 БТ</td>
	<td colspan="5" align="center" class="border-s">&nbsp;</td>
	<td align="center" class="border-s border-e">&nbsp;</td>
</tr>
</table>
</div>
<div id="tab-1-3" class="ui-panel no-mp">
	<table id="table-1-3" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr>
			<th style="display: none;"></th>
			<th>Постройка</th>
			<th align="center">Уровень</th>
			<th align="center">До уровня</th>
			<th align="center">Металл</th>
			<th align="center">Кристалл</th>
			<th align="center">Дейтерий</th>
			<th align="center">Энергия</th>
			<th align="center">Время</th>
			<th align="center">Очки</th>
		</tr>
		<? $i = 0; foreach ($reslist['allowed'][3] AS $id): ?>
			<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
				<td style="display: none;">100<?=$id ?></td>
				<td class="title"><?=_getText('tech', $id) ?></td>
				<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=($planet['planet_type'] == 3 ? $planet[$resource[$id]] : 0) ?>"/></td>
				<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/></td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0</td>
				<td align="center">0с</td>
				<td align="center">0</td>
			</tr>
		<? $i++; endforeach; ?>
		<tr class="even">
			<td style="display: none;"></td>
			<td colspan="2" class="border-n">Итого</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-s border-w"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s"><b>0</b></td>
			<td align="center" class="border-n border-s border-e"><b>0</b></td>
		</tr>
		<tr class="even">
			<td style="display: none;"></td>
			<td>Для доставки требуется</td>
			<td align="center">0 МТ</td>
			<td align="center">0 БТ</td>
			<td colspan="6"></td>
		</tr>
		<tr>
			<td colspan="10" height=5px;>&nbsp;</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td colspan="3" class="border-n border-w">Всего</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n">0</td>
			<td align="center" class="border-n border-e">0</td>
		</tr>
		<tr>
			<td style="display: none;"></td>
			<td class="border-s border-w">Для доставки требуется</td>
			<td align="center" class="border-s">0 МТ</td>
			<td align="center" class="border-s">0 БТ</td>
			<td colspan="5" align="center" class="border-s">&nbsp;</td>
			<td align="center" class="border-s border-e">&nbsp;</td>
		</tr>
	</table>
</div>
<div id="tab-1-4" class="ui-panel no-mp">
<table id="table-1-4" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
<tr>
	<th style="display: none;"></th>
	<th>Исследование</th>
	<th align="center">Уровень</th>
	<th align="center">До уровня</th>
	<th align="center">Металл</th>
	<th align="center">Кристалл</th>
	<th align="center">Дейтерий</th>
	<th align="center">Энергия</th>
	<th align="center">Время</th>
	<th align="center">Очки</th>
</tr>
<? $i = 0; foreach ($reslist['tech'] AS $id): ?>
	<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
		<td style="display: none;"><?=$id ?></td>
		<td class="title"><?=_getText('tech', $id) ?></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$user[$resource[$id]] ?>"/></td>
		<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/></td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0</td>
		<td align="center">0с</td>
		<td align="center">0</td>
	</tr>
<? $i++; endforeach; ?>
	<? foreach ($reslist['tech_f'] AS $id): ?>
		<tr class="<?=($i%2 == 0 ? 'odd' : 'even') ?>">
			<td style="display: none;"><?=$id ?></td>
			<td class="title"><?=_getText('tech', $id) ?></td>
			<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="<?=$user[$resource[$id]] ?>"/></td>
			<td class="input" align="center"><input type="text" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/></td>
			<td align="center">0</td>
			<td align="center">0</td>
			<td align="center">0</td>
			<td align="center">0</td>
			<td align="center">0с</td>
			<td align="center">0</td>
		</tr>
	<? $i++; endforeach; ?>
<tr class="odd">
	<td style="display: none;"></td>
	<td colspan="2" class="border-n">Итого</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-s border-w"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s"><b>0</b></td>
	<td align="center" class="border-n border-s border-e"><b>0</b></td>
</tr>
<tr class="odd">
	<td style="display: none;"></td>
	<td>Для доставки требуется</td>
	<td align="center">0 МТ</td>
	<td align="center">0 БТ</td>
	<td colspan="6"></td>
</tr>
<tr>
	<td colspan="10" height=5px;>&nbsp;</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td colspan="3" class="border-n border-w">Всего</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n">0</td>
	<td align="center" class="border-n border-e">0</td>
</tr>
<tr>
	<td style="display: none;"></td>
	<td class="border-s border-w">Для доставки требуется</td>
	<td align="center" class="border-s">0 МТ</td>
	<td align="center" class="border-s">0 БТ</td>
	<td colspan="5" align="center" class="border-s">&nbsp;</td>
	<td align="center" class="border-s border-e">&nbsp;</td>
</tr>
</table>
</div>
</div>
</div>
<div id="tab-2" class="ui-panel no-mp">
	<div>
		<table class="table" cellpadding="0" cellspacing="1" border="0">
			<tr>
				<td colspan="4">
					<select id="tech-types-select" name="tech-types-select" class="ui-state-default ui-corner-all ui-input">
						<optgroup label="Постройки (планета)">
							<? foreach ($reslist['allowed'][1] AS $id): ?>
								<option value="<?=$id ?>"><?=_getText('tech', $id) ?></option>
							<? endforeach; ?>
						</optgroup>
						<optgroup label="Постройки (луна)">
							<? foreach ($reslist['allowed'][3] AS $id): ?>
								<option value="<?=$id ?>"><?=_getText('tech', $id) ?></option>
							<? endforeach; ?>
						</optgroup>
						<optgroup label="Исследования">
							<? foreach ($reslist['tech'] AS $id): ?>
								<option value="<?=$id ?>"><?=_getText('tech', $id) ?></option>
							<? endforeach; ?>
						</optgroup>
					</select>
				</td>
				<td><label for="energy-tech-level">Ур. Энерг.техн.</label></td>
				<td>
					<input id="energy-tech-level" type="text" name="energy-tech-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/>
				</td>
				<td width="10px;">&nbsp;</td>
				<td></td>
				<td>
				</td>
				<td width="10px;">&nbsp;</td>
				<td>
				</td>
			</tr>
			<tr>
				<td><label for="tab2-from-level">&nbsp;От уровня</label></td>
				<td>
					<input id="tab2-from-level" type="text" name="tab2-from-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/>
				</td>
				<td><label for="tab2-to-level">До уровня</label></td>
				<td>
					<input id="tab2-to-level" type="text" name="tab2-to-level" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0"/>
				</td>
				<td><label for="max-planet-temp">Макс. t° планеты</label></td>
				<td>
					<input id="max-planet-temp" type="text" name="max-planet-temp" class="ui-state-default ui-corner-all ui-input level-input ui-input-margin" value="0" alt="Макс. t° планеты"/>
				</td>
				<td width="10px;">&nbsp;</td>
				<td></td>
				<td align="center">

				</td>
				<td width="10px;">&nbsp;</td>
				<td>
				</td>
			</tr>
		</table>
	</div>
	<div id="prods-table-div">
		<table id="prods-table" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
			<tr>
				<th>Уровень</th>
				<th>Металл</th>
				<th>Кристалл</th>
				<th>Дейтерий</th>
				<th>Энергия</th>
				<th>Время</th>
				<th>Очки</th>
				<th>Произв. в час</th>
				<th>Потреб. в час</th>
			</tr>
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>
			<tr class="odd">
				<td align="center" class="border-n">Итого</td>
				<td align="center" class="border-n border-s border-w"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s border-e"><b>0</b></td>
			</tr>
			<tr class="odd">
				<td align="center">Доставка</td>
				<td align="center">0 МТ</td>
				<td align="center">0 БТ</td>
				<td colspan="6"></td>
			</tr>
		</table>
	</div>
	<div id="commons-table-div" style="display: none;">
		<table id="commons-table" class="lined" cellpadding="0" cellspacing="1" border="0" width="100%">
			<tr>
				<th>Уровень</th>
				<th>Металл</th>
				<th>Кристалл</th>
				<th>Дейтерий</th>
				<th>Энергия</th>
				<th>Время</th>
				<th>Очки</th>
			</tr>
			<tr>
				<td colspan="7">&nbsp;</td>
			</tr>
			<tr class="odd">
				<td align="center" class="border-n">Итого</td>
				<td align="center" class="border-n border-s border-w"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s"><b>0</b></td>
				<td align="center" class="border-n border-s border-e"><b>0</b></td>
			</tr>
			<tr class="odd">
				<td align="center">Доставка</td>
				<td align="center">0 МТ</td>
				<td align="center">0 БТ</td>
				<td colspan="4"></td>
			</tr>
		</table>
	</div>
</div>
</div>
</div>
</div>
<div id="warning" class="ui-state-highlight ui-corner-all">
	<div id="warning-message"></div>
</div>
<div id="hint" class="ui-corner-all">
	<table>
		<tr>
			<td valign="top">
				<span class="ui-icon ui-icon-info"></span>
			</td>
			<td>
				<span id="hint-message">При расчёте продолжительности строительства/исследования используются только значения уровней Фабрик, Верфи и Лаборатории, введённые пользователем в верхней панели. Для вычисления суммарного уровня Лаборатории при наличии МИС нажмите кнопку "Рассчитать" рядом с полем ввода уровня Лаборатории.</span>
			</td>
		</tr>
	</table>
</div>
</div>