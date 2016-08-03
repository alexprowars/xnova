<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
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

		<tr>
			<th class="b text-center" colspan="4">{{ parse['total'] }} страниц</th>
		</tr>
	</table>
</div>