<? if (isset($message)): ?>
	<div class="error"><?=$message ?></div>
<? endif; ?>
<form action="<?=$this->url->get('index/remind/') ?>" method="post" id="lostForm" class="form">
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
				$(form).ajaxSubmit({
					data: {ajax: 'Y'},
					dataType: 'json',
					success: function (data)
					{
						if (data.status == 1 && data.data.redirect !== undefined)
							window.location.href = data.data.redirect;
						else
							$('#windowDialog').html(data.html);
					}
				});
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