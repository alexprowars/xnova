<div class="portlet light bordered">
	<div class="portlet-title">
		<div class="caption">
			<i class="icon-settings font-dark"></i>
			<span class="caption-subject font-dark sbold uppercase">{{ _text('page_title', route_controller~'_'~route_action) }}</span>
		</div>
		<div class="actions"></div>
	</div>
	<div class="portlet-body">
		{{ form.build() }}
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		$('#genpassword').on('click', function ()
		{
			var passw = App.generatePassword(10);

			$('#password, #password_confirm').attr('type', 'text').val(passw);
		});
	});
</script>