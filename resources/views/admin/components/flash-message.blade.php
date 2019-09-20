@if ($message = session('success'))
<div class="alert alert-success alert-block">
     {{ $message }}
</div>
@endif


@if ($message = session('error'))
<div class="alert alert-danger alert-block">
	{{ $message }}
</div>
@endif


@if ($message = session('warning'))
<div class="alert alert-warning alert-block">
	{{ $message }}
</div>
@endif


@if ($message = session('info'))
<div class="alert alert-info alert-block">
	{{ $message }}
</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger pb-0">
        <ul class="list-unstyled">
            @foreach($errors->all() as $error)
                <li><i class="fa fa-info-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif