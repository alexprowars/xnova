<script type="text/javascript">

	var groups = new Array(100);

	function vis_row(TAG, gID)
	{
		if (!groups[gID] == null || groups[gID] == 0)
			groups[gID] = 1;
		else
			groups[gID] = 0;

		$(TAG).each(function()
		{
			if (this.id == gID)
				this.style.display = (groups[gID] == 0) ? 'none' : '';
		});
	}

	function vis_cols(TAG, PRE, sID, gID)
	{
		var s = parseInt(sID);
		var g = parseInt(gID);

		for (var i = s; i < s + 10; i++)
		{
			if (i < s + g)
			{
				groups[PRE + i] = 0;
				vis_row(TAG, PRE + i);
			}
			else
			{
				groups[PRE + i] = 1;
				vis_row(TAG, PRE + i);
			}
		}
	}

	function opt()
	{
		var txt = "", tstr = "", tkey, tval;
		tkey = [];

		$('#form input[type=text]').each(function()
		{
			if (this.value > 0)
			{
				tstr = this.name;
				tval = tstr.split("-");

				if (tval[2] == undefined)
				{
					if ($("#" + tval[0] + "-" + tval[1] + "-l").length > 0)
					{
						tvar = tval[0];

						tval[0] = parseInt(tval[0].split('gr').join(''));

						if (tkey[tval[0]])
							tkey[tval[0]] += parseInt(tval[1]) + ',' + this.value + '!' + $("#" + tvar + "-" + tval[1] + "-l").val() + ';';
						else
							tkey[tval[0]] = parseInt(tval[1]) + ',' + this.value + '!' + $("#" + tvar + "-" + tval[1] + "-l").val() + ';';
					}
					else
					{
						tval[0] = parseInt(tval[0].split('gr').join(''));

						if (parseInt(tval[1]) < 200)
						{
							if (tkey[tval[0]])
								tkey[tval[0]] += parseInt(tval[1]) + ',' + this.value + ';';
							else
								tkey[tval[0]] = parseInt(tval[1]) + ',' + this.value + ';';
						}
						else
						{
							if (tkey[tval[0]])
								tkey[tval[0]] += parseInt(tval[1]) + ',' + this.value + '!0;';
							else
								tkey[tval[0]] = parseInt(tval[1]) + ',' + this.value + '!0;';
						}
					}
				}
			}
		});

		if (tkey != null)
		{
			if (tkey.length != null)
			{
				for (var i = 0; i < tkey.length; i++)
				{
					if (tkey[i])
						txt += tkey[i] + '|';
					else
						txt += '|';
				}
			}
		}

		$('#result input[name=r]').val(txt);
		$('#result').submit();
	}

	function gclear(gID)
	{
		var tstr = "", tval;

		$('input[type=text]').each(function()
		{
			if (this.name != "")
				tstr = this.name;
			else
				tstr = this.id;

			tval = tstr.split("-");
			tval[0] = parseInt(tval[0].charAt(2));

			if (gID == "all")
				this.value = 0;
			else if (tval[0] == gID)
				this.value = 0;
		});
	}

	if (XNova.isMobile == false)
	{
		$('#box, .game_content > .content').css('display', 'table');
	}

</script>

<style>
	input.number {
		width: 50px;
		text-align: right;
		padding: 2px;
	}

	input.lvl {
		width: 25px;
		padding: 2px;
		text-align: right;
	}

	.table-responsive .table {
		width: auto;
	}

	.table-responsive .table td, .table-responsive .table th {
		white-space: nowrap;
	}
</style>

<form method="post" action="/<?=BASE_DIR ?>xnsim/sim.php<?=(\Xcms\Core::getConfig('socialIframeView', 0) ? '?ingame' : '') ?>" name="form" id="result" autocomplete="off" <?=(\Xnova\User::get()->getUserOption('ajax_navigation') ? 'class="noajax"' : '') ?> target="_blank">
	<input type="hidden" name="r" value="">
</form>

<div class="table-responsive">
<table id="form" class="table">
	<tr>
		<th> XNova SIM</th>
		<th colspan="<?=(MAX_SLOTS * 2 + 1) ?>" class="spezial">

			<SELECT NAME="Att" SIZE="1" onchange='vis_cols("TH","gr",0,this.value);'>
				<? for ($i = 1; $i <= MAX_SLOTS; $i++): ?>
					<option value="<?=$i ?>"><?=$i ?></option>
				<? endfor; ?>
			</SELECT>

			Исходная ситуация

			<SELECT NAME="Def" SIZE="1" onchange='vis_cols("TH","gr",<?=MAX_SLOTS ?>,this.value);'>
				<? for ($i = 1; $i <= MAX_SLOTS; $i++): ?>
					<option value="<?=$i ?>"><?=$i ?></option>
				<? endfor; ?>
			</SELECT>

		</th>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<th>Ведущий</th>
		<? for ($i = 1; $i < MAX_SLOTS; $i++): ?>
			<th class="angreifer leftcol_data" id='gr<?=$i ?>'>Атакующий&nbsp;<?=$i ?></th>
		<? endfor; ?>
		<th>Планета</th>
		<? for ($i = MAX_SLOTS + 1; $i < MAX_SLOTS * 2; $i++): ?>
			<th class="angreifer leftcol_data" id='gr<?=$i ?>'>Защитник&nbsp;<?=($i - MAX_SLOTS) ?></th>
		<? endfor; ?>
	</tr>
	<tr>
		<td class=c colspan="<?=(MAX_SLOTS * 2 + 2) ?>">Исследования и офицеры</td>
	</tr>
	<? foreach ($parse['tech'] AS $techId): ?>
		<tr align=center>
			<th><?=_getText('tech', $techId) ?></th>
			<? for ($i = 0; $i < MAX_SLOTS * 2; $i++): ?>
				<th id="gr<?=$i ?>"><input class="number" value="<?=((isset($parse['slot_'.$i]) && isset($parse['slot_'.$i][$techId]['c'])) ? $parse['slot_'.$i][$techId]['c'] : 0) ?>" type="text" name="gr<?=$i ?>-<?=$techId ?>" maxlength="2"></th>
			<? endfor; ?>
		</tr>
	<? endforeach; ?>
	<tr>
		<td class=c colspan="<?=(MAX_SLOTS * 2 + 2) ?>">Флот</td>
	</tr>
	<? global $reslist; foreach ($reslist['fleet'] AS $fleetId): ?>
		<tr align=center>
			<th><?=_getText('tech', $fleetId) ?></th>
			<? for ($i = 0; $i < MAX_SLOTS * 2; $i++): ?>
				<th id="gr<?=$i ?>">
					<? if ($fleetId == 212 && $i < MAX_SLOTS): ?>
						-
					<? else: ?>
						<input class="number" value="<?=((isset($parse['slot_'.$i]) && isset($parse['slot_'.$i][$fleetId]['c'])) ? $parse['slot_'.$i][$fleetId]['c'] : 0) ?>" type="text" name="gr<?=$i ?>-<?=$fleetId ?>" maxlength="7">
					<? endif; ?>
					<? if (in_array($fleetId + 100, $reslist['tech_f'])): ?>
						<input class="lvl" value="<?=((isset($parse['slot_'.$i]) && isset($parse['slot_'.$i][$fleetId]['l'])) ? $parse['slot_'.$i][$fleetId]['l'] : 0) ?>" type="text" id="gr<?=$i ?>-<?=$fleetId ?>-l" maxlength="2">
					<? endif; ?>
				</th>
			<? endfor; ?>
		</tr>
	<? endforeach; ?>
	<tr>
		<td class=c colspan="<?=(MAX_SLOTS * 2 + 2) ?>">Оборона</td>
	</tr>
	<? foreach ($reslist['defense'] AS $fleetId): ?>
		<tr align=center>
			<th><?=_getText('tech', $fleetId) ?></th>
			<? for ($i = 0; $i < MAX_SLOTS * 2; $i++): ?>
				<th id="gr<?=$i ?>">
					<? if ($i < MAX_SLOTS): ?>
						-
					<? else: ?>
						<input class="number" value="<?=((isset($parse['slot_'.$i]) && isset($parse['slot_'.$i][$fleetId]['c'])) ? $parse['slot_'.$i][$fleetId]['c'] : 0) ?>" type="text" name="gr<?=$i ?>-<?=$fleetId ?>" maxlength="7">
						<? if (in_array($fleetId - 50, $reslist['tech_f'])): ?>
							<input class="lvl" value="<?=((isset($parse['slot_'.$i]) && isset($parse['slot_'.$i][$fleetId]['l'])) ? $parse['slot_'.$i][$fleetId]['l'] : 0) ?>" type="text" id="gr<?=$i ?>-<?=$fleetId ?>-l" maxlength="2">
						<? endif; ?>
					<? endif; ?>
				</th>
			<? endfor; ?>
		</tr>
	<? endforeach; ?>
	<tr align="center">
		<th>&nbsp;</th>
		<? for ($i = 0; $i < MAX_SLOTS * 2; $i++): ?>
			<th id='gr<?=$i ?>'><a href="javascript:" onClick='gclear("<?=$i ?>");'>Очистить</a></th>
		<? endfor; ?>
	</tr>
	<tr>
		<th colspan='<?=(MAX_SLOTS * 2 + 2) ?>'><input class="button" type="submit" id="SendBtn" value="Расчитать!" onclick="opt()"></th>
	</tr>
</table>
</div>
<script type="application/javascript">
	vis_cols("TH", "gr", 0, 1);
	vis_cols("TH", "gr", <?=MAX_SLOTS ?>, 1);
	vis_row("TR", "ts");
	vis_row("TR", "sp");
	vis_row("TR", "gb");
	vis_row("TR", "of");
</script>