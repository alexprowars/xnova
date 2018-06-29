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
						var tvar = tval[0];

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

</script>

<form method="post" action="{{ url('xnsim/report/'~(config.view.get('socialIframeView', 0) ? '?ingame' : '')) }}" name="form" id="result" autocomplete="off" target="_blank">
	<input type="hidden" name="r" value="">
</form>

<div class="table-responsive">
<table id="form" class="table">
	<tr>
		<th>XNova SIM</th>
		<th colspan="{{ constant('MAX_SLOTS') * 2 + 2 }}" class="spezial">

			<select NAME="Att" SIZE="1" onchange='vis_cols("TH","gr",0,this.value);' title="">
				{% for i in 1..constant('MAX_SLOTS') %}
					<option value="{{ i }}">{{ i }}</option>
				{% endfor %}
			</select>

			Исходная ситуация

			<select NAME="Def" SIZE="1" onchange='vis_cols("TH","gr",{{ constant('MAX_SLOTS') }},this.value);' title="">
				{% for i in 1..constant('MAX_SLOTS') %}
					<option value="{{ i }}">{{ i }}</option>
				{% endfor %}
			</select>

		</th>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<th>Ведущий</th>
		{% for i in 1..(constant('MAX_SLOTS') - 1) %}
			<th class="angreifer leftcol_data" id='gr{{ i }}'>Атакующий&nbsp;{{ i }}</th>
		{% endfor %}
		<th>Планета</th>
		{% for i in (constant('MAX_SLOTS') + 1)..(constant('MAX_SLOTS') * 2 - 1) %}
			<th class="angreifer leftcol_data" id='gr{{ i }}'>Защитник&nbsp;{{ i - constant('MAX_SLOTS') }}</th>
		{% endfor %}
	</tr>
	<tr>
		<td class="c" colspan="{{ constant('MAX_SLOTS') * 2 + 2 }}">Исследования и офицеры</td>
	</tr>
	{% for techId in parse['tech'] %}
		<tr align="center">
			<th>{{ _text('xnova', 'tech', techId) }}</th>
			{% for i in 0..(constant('MAX_SLOTS') * 2 - 1) %}
				<th id="gr{{ i }}"><input class="number" value="{{ parse['slot_'~i] is defined and parse['slot_'~i][techId]['c'] is defined ? parse['slot_'~i][techId]['c'] : 0 }}" type="text" name="gr{{ i }}-{{ techId }}" maxlength="2" title=""></th>
			{% endfor %}
		</tr>
	{% endfor %}
	<tr>
		<td class="c" colspan="{{ constant('MAX_SLOTS') * 2 + 2 }}">Флот</td>
	</tr>
	{% for fleetId in registry.reslist['fleet'] %}
		<tr align="center">
			<th>{{ _text('xnova', 'tech', fleetId) }}</th>
			{% for i in 0..(constant('MAX_SLOTS') * 2 - 1) %}
				<th id="gr{{ i }}">
					{% if fleetId == 212 and i < constant('MAX_SLOTS') %}
						-
					{% else %}
						<input class="number" value="{{ parse['slot_'~i] is defined and parse['slot_'~i][fleetId]['c'] is defined ? parse['slot_'~i][fleetId]['c'] : 0 }}" type="text" name="gr{{ i }}-{{ fleetId }}" maxlength="7" title="">
					{% endif %}
				</th>
			{% endfor %}
		</tr>
	{% endfor %}
	<tr>
		<td class="c" colspan="{{ constant('MAX_SLOTS') * 2 + 2 }}">Оборона</td>
	</tr>
	{% for fleetId in registry.reslist['defense'] %}
		<tr align="center">
			<th>{{ _text('xnova', 'tech', fleetId) }}</th>
			{% for i in 0..(constant('MAX_SLOTS') * 2 - 1) %}
				<th id="gr{{ i }}">
					{% if i < constant('MAX_SLOTS') %}
						-
					{% else %}
						<input class="number" value="{{ parse['slot_'~i] is defined and parse['slot_'~i][fleetId]['c'] is defined ? parse['slot_'~i][fleetId]['c'] : 0 }}" type="text" name="gr{{ i }}-{{ fleetId }}" maxlength="7" title="">
					{% endif %}
				</th>
			{% endfor %}
		</tr>
	{% endfor %}
	<tr align="center">
		<th>&nbsp;</th>
		{% for i in 0..(constant('MAX_SLOTS') * 2 - 1) %}
			<th id='gr{{ i }}'><a href="javascript:" onClick='gclear("{{ i }}");'>Очистить</a></th>
		{% endfor %}
	</tr>
	<tr>
		<th colspan='{{ constant('MAX_SLOTS') * 2 + 2 }}'><input class="button" type="submit" id="SendBtn" value="Расчитать!" onclick="opt()"></th>
	</tr>
</table>
</div>
<script type="application/javascript">
	vis_cols("TH", "gr", 0, 1);
	vis_cols("TH", "gr", {{ constant('MAX_SLOTS') }}, 1);
	vis_row("TR", "ts");
	vis_row("TR", "sp");
	vis_row("TR", "gb");
	vis_row("TR", "of");
</script>