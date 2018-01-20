<form name="form2" enctype="multipart/form-data" method="post" action="{{ url('avatar/upload/') }}">
	<div class="table">
		<div class="row">
			<div class="col-12 c">Загрузка аватара</div>
		</div>
		<div class="row">
			<div class="col-12 th">
				Картинки уменьшаются до размера 200 на 200 пикселей
				<br><br>
				<input type="file" name="image" value=""/>
				<input type="submit" name="Submit" value="Загрузить"/>
			</div>
		</div>
	</div>
</form>