<div class="block">
	<div class="title">История изменений</div>
	<div class="content border-0">
		<div class="table">
			<div class="row">
				<div class="col th">
					<div class="alert alert-success">Последний коммит: <a href="{{ url('git/') }}">{{ lastCommit }}</a></div>
				</div>
			</div>
			<div class="row">
				<div class="col-2 c">Версия</div>
				<div class="col-10 c">Описание</div>
			</div>
			{% for news in parse %}
				<div class="row">
					<div class="col-2 th">{{ news[0] }}</div>
					<div class="col-10 text-left b">{{ news[1] }}</div>
				</div>
			{% endfor %}
		</div>
	</div>
</div>