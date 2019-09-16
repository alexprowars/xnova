@push('assets')
	<script src="{{ asset('assets/admin/global/js/datatable.js')}}"></script>
	<script src="{{ asset('assets/admin/global/plugins/datatables/datatables.min.js')}}"></script>
	<script src="{{ asset('assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}"></script>

	<link href="{{ asset('assets/admin/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet">
@endpush
@extends(backpack_view('blank'))
@section('content')
<div class="card">
	<header class="card-header">
		<div class="card-header-actions">
			@if ($user->can('edit users'))
				<a href="{{ route('admin.users.add', [], false) }}" class="btn btn-sm btn-primary">Добавить</a>
			@endif
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
			"ajax": '{{ route('admin.users.list', [], false) }}',
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
						actions += '<a href="{{ route('admin.users.edit', ['id' => ''], false) }}/'+full.id+'/" class="btn btn-sm btn-outline btn-warning"><i class="fa fa-edit"></i></a>&nbsp;';
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
					window.location.href = '{{ route('admin.users.delete', ['id' => ''], false) }}/'+$(this).data('delete')+'/';
			}
			else
				_this.$router.push($(this).attr('href'));
		});
	});
</script>
@endsection