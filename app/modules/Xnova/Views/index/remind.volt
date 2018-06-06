{% if message is defined %}
	<div class="error">{{ message }}</div>
{% endif %}
<form action="{{ url('remind/') }}" method="post" id="lostForm" class="form">
	<table class="table">
		<tr>
			<th>Введите ваш Email, который вы указали при регистрации. При нажатии на кнопку "Получить пароль" на ваш e-mail будет выслана ссылка на новый пароль.</th>
		</tr>
		<tr>
			<th>Ваш Email: <input type="text" name="email" title=""></th>
		</tr>
		<tr>
			<th><input name="submit" type="submit" value="Выслать пароль"/></th>
		</tr>
	</table>
</form>

<script>
	$(document).ready(function()
	{
		$('#lostForm').validate({
			submitHandler: function(form)
			{
				var formData = new FormData(form);
				formData.append('ajax', 'Y');

				$.ajax({
				    url: form.attr('action'),
				    data: formData,
				    type: 'post',
					dataType: 'json',
				    contentType: false,
				    processData: false
				})
				.then(function (result)
				{
					if (result.status && result.data.redirect !== undefined)
						window.location.href = result.data.redirect;
					else
						$('#windowDialog').html(result.html);
				},
				function() {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
			},
			focusInvalid: false,
			focusCleanup: true,
			rules:
			{
				'email': {required: true, email: true}
			},
			messages:
			{
				'email': {required: 'Введите Email адрес', email: 'Введите корректный Email адрес'}
			}
		});
	});
</script>