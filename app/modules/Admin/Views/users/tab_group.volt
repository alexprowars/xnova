<div class="form-body">
	<div class="form-group">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>Группа</th>
				</tr>
			</thead>
			<tbody>
				{% for group in groups %}
					<tr>
						<td>
							<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
								<input type="checkbox" class="group-checkable" name="groups_id[]" value="{{ group.id }}" id="group_{{ group.id }}" {% if in_array(group.id, groups_values) %}checked{% endif %} title="">
								<span></span>
							</label>
						</td>
						<td>
							<label for="group_{{ group.id }}">{{ group.title }}</label>
						</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>
	</div>
</div>