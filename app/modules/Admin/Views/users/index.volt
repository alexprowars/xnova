<div class="portlet light bordered">
	<div class="portlet-body">
		<div class="table-toolbar">
			<div class="row">
				<div class="col-md-6">
					<div class="btn-group">
						{% if access.canWriteController('users', 'admin') %}
							<a href="{{ url('users/add/') }}" class="btn sbold green">Добавить <i class="fa fa-plus"></i></a>
						{% endif %}
					</div>
				</div>
			</div>
		</div>
		<div class="table-container">
			<table class="table table-striped table-bordered table-hover table-checkable" id="userlist">
				<thead>
					<tr role="row" class="heading">
						<th width="5%">ID</th>
						<th width="15%">Email</th>
						<th width="35%">ФИО</th>
						<th width="30%">Регистрация</th>
						<th width="25%">Действия</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function()
	{
		var grid = new Datatable();

		grid.init({
			src: $("#userlist"),
			onSuccess: function (grid, response) {},
			onError: function (grid) {},
			onDataLoad: function(grid) {},
			loadingMessage: 'Загрузка...',
			dataTable: {
				"bStateSave": false,
				"lengthMenu": [
					[10, 20, 50, -1],
					[10, 20, 50, "Все"]
				],
				"pageLength": 10,
				"ajax": {
					"url": "{{ url('users/list/') }}"
				},
				"order": [
					[0, "asc"]
				],
				"columns": [
					{"data": "id"},
					{"data": "email"},
					{"data": "name"},
					{"data": "date"},
					{"data": "actions", "orderable": false}
				]
			}
		});
	});
</script>