<?
/**
 * @var $parse array
 */
?>
	<table class="table">
	<tr>
		<td class="c" colspan="3">Привлечённые игроки</td>
	<tr>
	<? if (count($parse['ref']) > 0): ?>
		<tr>
			<td class="c">Ник</td>
			<td class="c">Дата регистрации</td>
			<td class="c">Уровень развития</td>
		</tr>
		<? foreach ($parse['ref'] AS $list): ?>
			<tr>
				<th><? if ($this->game->datezone("d", $list['create_time']) >= 15)
					echo '+&nbsp;'; ?><a href="<?=$this->url->get('players/'.$list['id'].'/') ?>"><?=$list['username'] ?></a></th>
				<th><?=$this->game->datezone("d.m.Y H:i", $list['create_time']) ?></th>
				<th>П:<?=$list['lvl_minier'] ?>, В:<?=$list['lvl_raid'] ?></th>
			</tr>
		<? endforeach; ?>
	<? else: ?>
		<tr>
			<th colspan="3">Нет привлеченных игроков</th>
		</tr>
	<? endif; ?>
</table>

<? if (isset($parse['you'])): ?>
	<br><br>
	<table class="table">
		<tr>
			<th>Вы были привлечены игроком:</th>
			<th><a href="<?=$this->url->get('players/'.$parse['you']['id'].'/') ?>"><?=$parse['you']['username'] ?></a></th>
		</tr>
	</table>
<? endif; ?>

<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
	<br><br>
	<table class="table">
		<tr>
			<th colspan="2" style="padding:15px;">
				Помоги проекту, поделись им с друзьями!<br><br>
				<script type="text/javascript" src="//yandex.st/share/share.js"
		charset="utf-8"></script>
		<div class="yashare-auto-init"
			data-yashareL10n="ru"
			data-yashareTheme="counter"
			data-yashareType="small"
			data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus"
			data-yashareLink="//uni<?=$this->config->game->universe ?>.xnova.su/?<?=$userId ?>"
			data-yashareTitle="<?=$this->config->app->get('name') ?>"
		></div>
		</th></tr></table>

	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c">Юзербар</td>
		</tr>
		<tr>
			<th>
				<br>
				<img src="/userbar<?=$userId ?>.jpg">

				<br><br>
				HTML код:
				<br>
				<input style="width:100%" type="text" value="<?=htmlspecialchars('<a href="//uni'.$this->config->game->universe.'.xnova.su/?'.$userId.'"><img src="http://uni'.$this->config->game->universe.'.xnova.su/userbar'.$userId.'.jpg"></a>') ?>" title="">
				<div class="separator"></div>
				BB код:
				<input style="width:100%" type="text" value="<?=htmlspecialchars('[url=http://uni'.$this->config->game->universe.'.xnova.su/?'.$userId.'][img]http://uni'.$this->config->game->universe.'.xnova.su/userbar'.$userId.'.jpg[/img][/url]') ?>" title="">
			</th>
		</tr>
	</table>
<? endif; ?>

<? if ($this->config->view->get('socialIframeView', 0) == 1 && $_SERVER['SERVER_NAME'] == 'ok1.xnova.su'): ?>
	<br><br>
	<table width="100%">
		<tr>
				<td class="c">Информация</td>
		<tr>
		<tr>
			<th>Приглашайте друзей в игру, и с каждым их полученным боевым и мирным уровнем вам будет начислено некоторое количество кредитов.
			<br><br>
			<input type="button" value="Пригласить друзей" onclick="FAPI.UI.showInvite('Пришло время воевать!', 'userId=<?=$userId ?>');">
			</th>
		</tr>
	</table>
<? endif; ?>