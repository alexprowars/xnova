@extends('layouts.admin')
@section('content')
<div class="kt-portlet">
	<div class="kt-portlet__body">
		<table class="table table-sm table-striped">
			@foreach ($_SERVER as $key => $value)
				<tr>
					<th class="text-left">{{ $key }}</th>
					<td class="c text-left">{{ $value }}</td>
				</tr>
			@endforeach
		</table>
	</div>
</div>
@endsection