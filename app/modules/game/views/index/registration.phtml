<? if (isset($message)): ?>
	<div class="error"><?=$message ?></div>
<? endif; ?>
<script src='//www.google.com/recaptcha/api.js'></script>
<form action="<?=$this->url->get('index/registration/') ?>" method="post" id="regForm" class="form">
	<table class="table">
		<tbody>
		<tr>
			<th width="40%">E-Mail<br>(используется для входа)</th>
			<th><input name="email" size="20" maxlength="40" type="text" value="<?=$this->request->getPost('email') ?>" title=""></th>
		</tr>
		<tr>
			<th>Пароль</th>
			<th><input name="password" id="password" size="20" maxlength="20" type="password" title=""></th>
		</tr>
		<tr>
			<th>Подтверждение пароля</th>
			<th><input name="rpassword" size="20" maxlength="20" type="password" title=""></th>
		</tr>
		<tr>
			<th colspan="2" align="center" class="text-xs-center">
				<div class="g-recaptcha" data-sitekey="<?=$this->config->recaptcha->public_key ?>"></div>
			</th>
		</tr>
		<tr>
			<th colspan="2" class="text-xs-left">
				<input name="sogl" id="sogl" type="checkbox" <?=($this->request->getPost('sogl') != '' ? 'checked' : '') ?>>
				<label for="sogl">Я принимаю</label> <a href="<?=$this->url->get('content/agreement/') ?>" target="_blank">Пользовательское соглашение</a>
			</th>
		</tr>
		<tr>
			<th colspan="2" class="text-xs-left">
				<input name="rgt" id="rgt" type="checkbox" <?=($this->request->getPost('rgt') != '' ? 'checked' : '') ?>>
				<label for="rgt">Я принимаю</label> <a href="<?=$this->url->get('content/agb/') ?>" target="_blank">Законы игры</a>
			</th>
		</tr>
		<tr>
			<th colspan="2"><input name="submit" type="submit" value="Регистрация"></th>
		</tr>
	</table>
</form>
<script>
	$(document).ready(function()
	{
		$('#regForm').validate({
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
				'password': 'required',
				'rpassword': {required: true, 'equalTo': '#password'},
				'email': {required: true, email: true}
			},
			messages:
			{
				'password': 'Введите пароль от игры',
				'rpassword': {required: 'Введите подтверждение пароля', equalTo: 'Пароли не совпадают'},
				'email': {required: 'Введите Email адрес', email: 'Введите корректный Email адрес'}
			}
		});
	});
</script>