{{ getDoctype() }}
<html lang="ru">
	<head>
		<meta http-equiv="content-type" content="text/html; utf-8">
		<meta http-equiv="content-language" content="ru">
		<title>XNova SIM v1.0</title>
		{{ assets.outputCss() }}
		{{ assets.outputJs() }}
	</head>
<body>
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

		for (var i = s; i < s + {{ config.game.get('maxSlotsInSim', 5) }}; i++)
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

		$('input[type=text]').each(function()
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

<form method="get" action="{{ url('xnsim/report/') }}" id="result" target="_blank">
	<input type="hidden" name="r" value="">
</form>

<table cellspacing="0" cellpadding="0" border="0" class="maintable">
<tr valign="top" class="main">
<td class="body leftcol main">
<table cellspacing="2" cellpadding="0" align="center">
<thead>
	<tr>
		<th class="spezial"> XNova SIM </th>
		<th colspan="11" class="spezial">
			<select name="Att" SIZE="1" onchange='vis_cols("TD","gr",0,this.value);' title="">
				{% for i in 1..config.game.get('maxSlotsInSim', 5) %}
					<option value="{{ i }}">{{ i }}</option>
				{% endfor %}
			</select>

			Исходная ситуация

			<select name="Def" SIZE="1" onchange='vis_cols("TD","gr",{{ config.game.get('maxSlotsInSim', 5) }},this.value);' title="">
				{% for i in 1..config.game.get('maxSlotsInSim', 5) %}
					<option value="{{ i }}">{{ i }}</option>
				{% endfor %}
			</select>
		</th>
	</tr>
	<tr>
		<th align="center" class="typ leftcol_type typ_td"> Тип </th>

		<th class="angreifer leftcol_data"> Ведущий </th>
		{% for i in 1..(config.game.get('maxSlotsInSim', 5) - 1) %}
			<td class="angreifer leftcol_data" id='gr{{ i }}'>Атакующий&nbsp;{{ i }}</td>
		{% endfor %}

		<th class="verteidiger leftcol_data "> Планета </th>
		{% for i in (config.game.get('maxSlotsInSim', 5) + 1)..(config.game.get('maxSlotsInSim', 5) * 2 - 1) %}
			<td class="angreifer leftcol_data" id='gr{{ i }}'>Защитник&nbsp;{{ i - config.game.get('maxSlotsInSim', 5) }}</td>
		{% endfor %}
	</tr>
</thead>
<tr>
	<td colspan="12" class="spezial" id="tech_td"><b>Исследования и офицеры</b></td>
</tr>
{% for techId in techList %}
	<tr align=center>
		<td><b>{{ _text('xnova', 'tech', techId) }}</b></td>
		{% for i in 0..(config.game.get('maxSlotsInSim', 5) * 2 - 1) %}
			<td id="gr{{ i }}"><input class="number" value="0" type="text" name="gr{{ i }}-{{ techId }}" maxlength="2" title=""></td>
		{% endfor %}
	</tr>
{% endfor %}

<tr>
	<td colspan="12" class="spezial" id="fleet_td"><b>Флот</b></td>
</tr>

{% for fleetId in registry.reslist['fleet'] %}
	<tr align=center>
		<td><b>{{ _text('xnova', 'tech', fleetId) }}</b></td>
		{% for i in 0..(config.game.get('maxSlotsInSim', 5) * 2 - 1) %}
			<td id="gr{{ i }}">
				{% if fleetId == 212 and i < config.game.get('maxSlotsInSim', 5) %}
					-
				{% else %}
					<input class="number" value="0" type="text" name="gr{{ i }}-{{ fleetId }}" maxlength="7" title="">
				{% endif %}
			</td>
		{% endfor %}
	</tr>
{% endfor %}

<tr>
	<td colspan="12" class="spezial" id="def_td"><b>Защита</b></td>
</tr>
	{% for fleetId in registry.reslist['defense'] %}
		<tr align=center>
			<td><b>{{ _text('xnova', 'tech', fleetId) }}</b></td>
			{% for i in 0..(config.game.get('maxSlotsInSim', 5) * 2 - 1) %}
				<td id="gr{{ i }}">
					{% if i < config.game.get('maxSlotsInSim', 5) %}
						-
					{% else %}
						<input class="number" value="0" type="text" name="gr{{ i }}-{{ fleetId }}" maxlength="7" title="">
					{% endif %}
				</td>
			{% endfor %}
		</tr>
	{% endfor %}


	<tr align="center">
		<td>&nbsp;</td>
		{% for i in 0..(config.game.get('maxSlotsInSim', 5) * 2 - 1) %}
			<td id='gr{{ i }}'><a href='javascript:;' onClick='gclear("{{ i }}");'>Очистить</a></td>
		{% endfor %}
  	</tr>
    <tr>
     	<td colspan="12" align="center">
    	<input name="SendBtn" type="submit" id="SendBtn" value="Расчитать!" onclick="opt()">
    </tr>
</table>

</td>
</tr>
</table>
<script type="text/javascript">
	vis_cols("TD","gr",0,1);
	vis_cols("TD","gr",{{ config.game.get('maxSlotsInSim', 5) }},1);
	vis_row("TR","ts");
	vis_row("TR","sp");
	vis_row("TR","gb");
	vis_row("TR","of");
</script>

<center>Made by AlexPro for <a href="http://xnova.su/" target="_blank">XNova Game</a></center>

	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter25961143 = new Ya.Metrika({id:25961143});
			} catch(e) { }
		});

	    var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); };
	    s.type = "text/javascript";
	    s.async = true;
	    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

	    if (w.opera == "[object Opera]") {
	        d.addEventListener("DOMContentLoaded", f, false);
	    } else { f(); }
	})(document, window, "yandex_metrika_callbacks");
	</script>
	<!-- /Yandex.Metrika counter -->

</body>
</html>