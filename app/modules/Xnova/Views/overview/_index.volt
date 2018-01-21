{% if parse['bonus'] %}
	<table class="table">
		<tr>
			<td class="c">Ежедневный бонус</td>
		</tr>
		<tr>
			<th>
				Сейчас вы можете получить по <b>{{ parse['bonus_multi'] * 500 * game.getSpeed('mine') }}</b> Металла, Кристаллов и Дейтерия.<br>
				Каждый день размер бонуса будет увеличиваться.<br>
				<br>
				<a href="{{ url("overview/bonus/") }}">ПОЛУЧИТЬ БОНУС</a><br><br>

				Помоги проекту, поделись им с друзьями!
				<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
				<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareTitle="{{ option('site_title') }}" data-yashareLink="//uni{{ config.game.universe }}.xnova.su/" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter" data-yashareType="small"></div>
			</th>
		</tr>
	</table>
	<div class="separator"></div>
{% endif %}