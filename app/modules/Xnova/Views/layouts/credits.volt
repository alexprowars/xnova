<table class="table">
	<tr>
		<th align="center">
			<br>
			Для развития проекта Вы можете поддержать нас, получая кредиты по следующему курсу:<br><br>
			<div class="text-center">
				1 кредит - 1 руб.
			</div>
			<br><br>
		</th>
	</tr>
</table>
<div class="separator"></div>
<table class="table">
	<tr>
		<td class="c" colspan="5"><b>Покупка кредитов</b></td>
	</tr>
	<tr>
		<th>
			{% if request.hasPost('OutSum') is false %}
				<br><br>
				Ваш ID: <span class="neutral">{{ userId }}</span>
				<br><br>


				<form action="{{ url('credits/') }}" method="POST">
					Введите ID игрока, на счет которого будут зачислены кредиты:
					<br>(если поле не заполнено, то кредиты поступят на ваш счет)
					<br><br>
					<input type="text" name="userId" value="" title="">
					<br><br>
					Введите число желаемых кредитов:
					<br>
					<input type="text" name="OutSum" value="10" title="">
					<br>
					<input type="submit" value="Купить">
				</form>

				<br><br>
			{% else %}
				<br>
				Счет сформирован. Нажмите кнопку "перейти к оплате" для продолжения процедуры покупки кредитов
				<br><br>

				<form class="noajax" action="http://www.free-kassa.ru/merchant/cash.php" method="POST" target="_blank">
					<input type="hidden" name="MrchLogin" value="{{ config.robokassa.login }}">
					<input type="hidden" name="InvDesc" value="Покупка кредитов">
					<input type="hidden" name="InvId" value="{{ invid }}">
					<input type="hidden" name="Email" value="{{ userEmail }}">
					<input type="hidden" name="Shp_UID" value="{{ userId }}">
					<input type="hidden" name="SignatureValue" value="{{ md5(config.robokassa.login~":"~request.getPost('OutSum', 'int')~":"~invid~":"~config.robokassa.public~":Shp_UID="~userId) }}">
					<input type="hidden" name="Culture" value="RU">
					<input type="hidden" name="OutSum" value="{{ request.getPost('OutSum', 'int') }}">
					<br>
					<input type="submit" value="Перейти к оплате">
				</form>

				<br><br>
				Счет выставлен для ID
				<span class="neutral">{{ userId }}</span>

				<br><br>
			{% endif %}
		</th>
	</tr>
</table>