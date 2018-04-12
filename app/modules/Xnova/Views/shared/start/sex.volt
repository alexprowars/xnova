<div class="block start">
	<div class="title">Основная информация</div>
	<div class="content">
		{% if message is defined and message != '' %}
			<div class="errormessage">{{ message }}</div>
		{% endif %}
		<form action="" method="POST">
			<input type="hidden" name="save" value="Y">
			<table class="table table-noborder">
				<tr>
					<th>Введите ваш игровой ник</th>
					<th><input name="character" size="20" maxlength="20" type="text" value="{{ request.hasPost('character') ? request.getPost('character') : name }}" title=""></th>
				</tr>
				<tr>
					<td class="c" colspan="2">Выберите ваш игровой образ</td>
				</tr>
				<tr>
					<th colspan="2">
						<tabs>
							<tab name="Мужской">
								{% for i in 1..8 %}
									<input type="radio" name="face" value="1_{{ i }}" id="f1_{{ i }}" {{ request.getPost('face') == '1_'~i ? 'checked' : '' }} title="">
									<label for="f1_{{ i }}" class="avatar">
										<img src="{{ url.getBaseUri() }}assets/images/faces/1/{{ i }}s.png" alt="">
									</label>
								{% endfor %}
							</tab>
							<tab name="Женский">
								{% for i in 1..8 %}
									<input type="radio" name="face" value="2_{{ i }}" id="f2_{{ i }}" {{ request.getPost('face') == '2_'~i ? 'checked' : '' }} title="">
									<label for="f2_{{ i }}" class="avatar">
										<img src="{{ url.getBaseUri() }}assets/images/faces/2/{{ i }}s.png" alt="">
									</label>
								{% endfor %}
							</tab>
						</tabs>
					</th>
				</tr>
				<tr>
					<th colspan="2">
						<input type="submit" name="save" value="Продолжить">
					</th>
				</tr>
			</table>
		</form>
	</div>
</div>