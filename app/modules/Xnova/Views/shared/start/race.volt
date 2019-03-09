<div class="block start race">
	<div class="title">Выбор фракции</div>
	<div class="content">
		{% if message is defined and message != '' %}
			<div class="errormessage">{{ message }}</div>
		{% endif %}
		<router-form action="" id="tabs">
			<input type="hidden" name="save" value="Y">
			{% for i, name in _text('xnova', 'race') if name != '' %}
				<input type="radio" name="race" value="{{ i }}" id="f_{{ i }}" {{ request.getPost('race') == i ? 'checked' : '' }}>
				<label for="f_{{ i }}" class="avatar">
					<img src="{{ url.getBaseUri() }}images/skin/race{{ i }}.gif" alt=""><br>
					<h3>{{ name }}</h3>
					<span>
						{{ _text('xnova', 'info', 700 + i) }}
					</span>
				</label>
			{% endfor %}
			<br>
			<input type="submit" name="save" value="Продолжить">
			<br><br>
		</router-form>
	</div>
</div>