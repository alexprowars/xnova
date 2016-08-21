<input type="hidden" name="message_id" id="message_id" value="1">

<div class="col-xs-12 th">
	<div id="shoutbox" class="shoutbox scrollbox"></div>
</div>
<div class="col-xs-12 th">
	<div id="chatEditor" style="float: right"></div>
	<input name="msg" type="text" id="chatMsg" maxlength="750" title="">
	<div class="separator"></div>
	<input type="button" name="clear" value="Очистить" id="clear" onClick="ClearChat()">
	<input type="button" name="send" value="Отправить" id="send">
	<br>
	<div id="new_msg"></div>
</div>

<script type="text/javascript" src="{{ static_url('assets/js/socket.io-1.4.5.js') }}"></script>
<script type="text/javascript" src="{{ static_url('assets/js/chat.js') }}"></script>
<script type="text/javascript">

var allowResize = {{ config.view.get('socialIframeView', 0) == 1 ? 0 : 1 }};

var userId = {{ user.getId() }};
var userName = '{{ user.username }}';
var key = '{{ md5(user.getId()~'|'~user.username~'SuperPuperChat') }}';
var server = 'https://uni5.xnova.su:6677';
var color = {{ user.color }};

$(document).ready(function()
{
	chatToolbar('chatMsg', 'chatEditor');

	if (allowResize)
		setTimeout(chatResize, 1500);

	setTimeout(initChat, 1000);
});
</script>

{% if request.hasQuery('frame') %}
	<style>#box {  width: 100%;  }</style>
{% endif %}