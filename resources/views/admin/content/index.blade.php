<div class="card">
	<table class="table table-striped table-hover table-responsive table-sm">
		<thead>
			<tr>
				<th>ID</th>
				<th>Алиас</th>
				<th>Название</th>
			</tr>
		</thead>
		{% for data in parse['rows'] %}
			<tr>
				<td><a href="/admin/content/edit/{{ data['id'] }}/">{{ data['id'] }}</a></td>
				<td>{{ data['alias'] }}</td>
				<td>{{ data['title'] }}</td>
			</tr>
		{% endfor %}
	</table>
	<footer class="card-footer text-center">
		<b>{{ parse['total'] }}</b> страниц
	</footer>
</div>