<div class="card">
	<header class="card-header">
		<div class="card-header-actions">
			{% if access.canWriteController('users', 'admin') %}
				<a href="{{ url('users/add/') }}" class="btn btn-sm btn-primary">Добавить</a>
			{% endif %}
		</div>
	</header>
	<div class="card-body">
		<table class="table table-striped table-bordered table-sm" cellspacing="0" data-provide="datatables">
			<thead>
				<tr>
					<th width="5%">ID</th>
					<th width="15%">Email</th>
					<th width="35%">ФИО</th>
					<th width="30%">Регистрация</th>
					<th width="25%">Действия</th>
				</tr>
			</thead>
		</table>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function()
	{
		var _this = this;

		$('[data-provide="datatables"]').DataTable({
			"serverSide": true,
			"ajax": '{{ url.getBaseUri() }}users/list/',
			"lengthMenu": [
				[10, 20, 50, -1],
				[10, 20, 50, "Все"]
			],
			"order": [
				[0, "asc"]
			],
			"columns": [
				{"data": "id", "className": "align-middle", "width": "10%"},
				{"data": "email", "className": "align-middle"},
				{"data": "name", "className": "align-middle"},
				{"data": "date", "className": "align-middle"},
				{"data": "actions", "orderable": false, "className": "text-center", "width": "20%"}
			],
			"columnDefs": [{
				"aTargets": [4],
				"mData": "actions",
				"mRender": function (data, type, full)
				{
					var actions = '<div class="actions">';

					if (data)
					{
						actions += '<a href="{{ url.getBaseUri() }}users/edit/'+full.id+'/" class="btn btn-sm btn-outline btn-warning"><i class="fa fa-edit"></i></a>&nbsp;';
						actions += '<a data-delete="'+full.id+'" class="btn btn-outline btn-sm btn-danger"><i class="fa fa-trash-o"></i></a>';
					}

					actions += '</div>';

					return actions;
				}
			}]
		})
		.on('click', '.actions a', function(e)
		{
			e.preventDefault();

			if (typeof $(this).data('delete') !== 'undefined')
			{
				if (window.confirm('Удалить пользователя'))
					window.location.href = '{{ url.getBaseUri() }}users/delete/'+$(this).data('delete')+'/';
			}
			else
				_this.$router.push($(this).attr('href'));
		});
	});
</script>