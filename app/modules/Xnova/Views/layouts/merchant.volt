{% if parse['type'] == 'metal' %}
	<script type="text/javascript">
		function calcul()
		{
			var merchant = $('#merchant');

			var Cristal 	= merchant.find('input[name=cristal]').val();
			var Deuterium 	= merchant.find('input[name=deut]').val();

			Cristal = Cristal * {{ parse['mod_ma_res_a'] }};
			Deuterium = Deuterium * {{ parse['mod_ma_res_b'] }};

			var metal = $("#metal");

			metal.html(Cristal + Deuterium);

			if (isNaN(merchant.find('input[name=cristal]').val()))
				metal.html("{{ _text('mod_ma_nbre') }}");

			if (isNaN(merchant.find('input[name=deut]').val()))
				metal.html("{{ _text('mod_ma_nbre') }}");
		}
	</script>
	<form id="merchant" action="{{ url('merchant/') }}" method="post">
		<input type="hidden" name="ress" value="metal">
		<table width="100%">
			<tr>
				<td class="c" colspan="3">{{ _text('mod_ma_buton') }} на металл</td>
			</tr>
			<tr>
				<th width="25%"></th>
				<th width="25%">{{ _text('mod_ma_cours') }}</th>
				<th></th>
			</tr>
			<tr>
				<th>{{ _text('Metal') }}</th>
				<th>{{ parse['mod_ma_res'] }}</th>
				<th><span id='metal'></span></th>
			</tr>
			<tr>
				<th>{{ _text('Crystal') }}</th>
				<th>{{ parse['mod_ma_res_a'] }}</th>
				<th><input name="cristal" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th>{{ _text('Deuterium') }}</th>
				<th>{{ parse['mod_ma_res_b'] }}</th>
				<th><input name="deut" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th colspan="2" class="negative">Внимание! Стоимость обмена 1 кр.</th>
				<th><input type="submit" value="{{ _text('mod_ma_excha') }}"></th>
			</tr>
		</table>
	</form>
{% elseif parse['type'] == 'cristal' %}
	<script type="text/javascript">
		function calcul()
		{
			var merchant = $('#merchant');

			var Metal = merchant.find('input[name=metal]').val();
			var Deuterium = merchant.find('input[name=deut]').val();

			Metal = Metal * {{ parse['mod_ma_res_a'] }};
			Deuterium = Deuterium * {{ parse['mod_ma_res_b'] }};

			var crystal = $("#cristal");

			crystal.html(Metal + Deuterium);

			if (isNaN(merchant.find('input[name=metal]').val()))
				crystal.html("{{ _text('mod_ma_nbre') }}");

			if (isNaN(merchant.find('input[name=deut]').val()))
				crystal.html("{{ _text('mod_ma_nbre') }}");
		}
	</script>
	<form id="merchant" action="{{ url('merchant/') }}" method="post">
		<input type="hidden" name="ress" value="cristal">
		<table width="100%">
			<tr>
				<td class="c" colspan="3">{{ _text('mod_ma_buton') }} на кристалл</td>
			</tr>
			<tr>
				<th width="25%"></th>
				<th width="25%">{{ _text('mod_ma_cours') }}</th>
				<th></th>
			</tr>
			<tr>
				<th>{{ _text('Crystal') }}</th>
				<th>{{ parse['mod_ma_res'] }}</th>
				<th><span id='cristal'></span></th>
			</tr>
			<tr>
				<th>{{ _text('Metal') }}</th>
				<th>{{ parse['mod_ma_res_a'] }}</th>
				<th><input name="metal" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th>{{ _text('Deuterium') }}</th>
				<th>{{ parse['mod_ma_res_b'] }}</th>
				<th><input name="deut" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th colspan="2" class="negative">Внимание! Стоимость обмена 1 кр.</th>
				<th><input type="submit" value="{{ _text('mod_ma_excha') }}"></th>
			</tr>
		</table>
	</form>
{% elseif parse['type'] == 'deut' %}
	<script type="text/javascript">
		function calcul()
		{
			var merchant = $('#merchant');

			var Metal 	= merchant.find('input[name=metal]').val();
			var Cristal = merchant.find('input[name=cristal]').val();

			Metal = Metal * {{ parse['mod_ma_res_a'] }};
			Cristal = Cristal * {{ parse['mod_ma_res_b'] }};

			var deuterium = $("#deut");

			deuterium.html(Metal + Cristal);

			if (isNaN(merchant.find('input[name=metal]').val()))
				deuterium.html("{{ _text('mod_ma_nbre') }}");

			if (isNaN(merchant.find('input[name=cristal]').val()))
				deuterium.html("{{ _text('mod_ma_nbre') }}");
		}
	</script>
	<form id="merchant" action="{{ url('merchant/') }}" method="post">
		<input type="hidden" name="ress" value="deuterium">
		<table width="100%">
			<tr>
				<td class="c" colspan="3">{{ _text('mod_ma_buton') }} на дейтерий</td>
			</tr>
			<tr>
				<th width="25%"></th>
				<th width="25%">{{ _text('mod_ma_cours') }}</th>
				<th></th>
			</tr>
			<tr>
				<th>{{ _text('Deuterium') }}</th>
				<th>{{ parse['mod_ma_res'] }}</th>
				<th><span id='deut'></span></th>
			</tr>
			<tr>
				<th>{{ _text('Metal') }}</th>
				<th>{{ parse['mod_ma_res_a'] }}</th>
				<th><input name="metal" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th>{{ _text('Crystal') }}</th>
				<th>{{ parse['mod_ma_res_b'] }}</th>
				<th><input name="cristal" type="number" value="" placeholder="введите кол-во" onkeyup="calcul()"/></th>
			</tr>
			<tr>
				<th colspan="2" class="negative">Внимание! Стоимость обмена 1 кр.</th>
				<th><input type="submit" value="{{ _text('mod_ma_excha') }}"></th>
			</tr>
		</table>
	</form>
{% else %}
	<div class="block start">
		<div class="title">Обмен сырья</div>
		<div class="content">
			<form action="{{ url('merchant/') }}" method="post">
				<input type="hidden" name="action" value="2">
				<table class="table noborder">
					<tr>
						<th>
							Вы можете вызвать межгалактического торговца для обмена ресурсов.<br>
							<div class="negative">Каждая операция обмена будет стоить вам 1 кредит.</div><br><br>
							Обменять сырьё &nbsp;<select name="choix" title="">
							<option value="metal">Металл</option>
							<option value="cristal">Кристалл</option>
							<option value="deut">Дейтерий</option>
						</select>
							&nbsp;&nbsp;(курс 2/1/0.5)<br><br>
						</th>
					</tr>
					<tr>
						<td class="c" align="center"><input type="submit" value="Обмен"/></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
{% endif %}