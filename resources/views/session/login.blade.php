@extends('app')
@section('div')
<h2>Войти</h2>
@if (Route::has('action.login'))
<form action="{{ route('action.login') }}" method="post">
	@csrf
	<div class="form-group div">
		<label class="col-sm-1">
			Логин
		</label>
		<input class="col-sm-2" type="text" name="login">
	</div>
	<div class="form-group div">
		<label class="col-sm-1">
			Пароль
		</label>
		<input class="col-sm-2" type="password" name="password">
	</div>
	<button type="submit">Войти</button>
</form>
@endif
@endsection