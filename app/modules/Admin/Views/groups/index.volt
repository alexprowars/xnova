<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
		<thead>
			<tr>
				<th>ID</th>
				<th>Имя группы</th>
			</tr>
		</thead>
		{% for l in list %}
			<tr>
				<td><a href="/admin/groups/edit/{{ l['id'] }}/">{{ l['id'] }}</a></td>
				<td>{{ l['name'] }}</td>
			</tr>
		{% endfor %}
	</table>
</div>