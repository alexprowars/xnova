<div class="block">
	<div class="title">Git History</div>
	<div class="content border-0">
		<div class="table">
			<div class="row">
				<div class="col-2 c">Дата</div>
				<div class="col-10 c">Новости</div>
			</div>
			{% for hash, news in history %}
			<div class="row">
				<div class="col-2 th">{{ news['date'] }}</div>
				<div class="col-10 b text-left">
					{{ hash }}
					<div class="positive">{{ news['author'] }}</div>
					<br>
					{{ news['message'] }}
					<br>
					<a href="https://github.com/alexprowars/xnova-game/commit/{{ hash }}/" target="_blank">Просмотр изменений кода</a>
				</div>
			</div>
			{% endfor %}
		</div>
	</div>
</div>