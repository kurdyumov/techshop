@extends('app')
@section('div')
<h2>Товары</h2>
@if (Route::has('action.search'))
<form class="form" action="{{ route('action.search') }}" method="get">
	<div class="form-group row">
		<label class="col-sm-2">
			Наименование
		</label>
		<input class="col-sm-2" type="text" name="entry" min="0">
	</div>
	<div class="form-group row">
		<label class="col-sm-2">Категория</label>
		<select class="col-sm-2" name="category">
			<option value="" selected disabled hidden>Категория</option>
			<option value=""></option>
	        <!-- <option value=""></option> -->
			@foreach ((new App\Services\SqlService())->retrieveCategories()->get() as $category)
				<option value="{{ $category->categoryid }}">{{ $category->title }}</option>
			@endforeach
		</select>
	</div>
	<button type="submit" name="search">Поиск</button>
</form>
@endif
@if (isset($positions) && Route::has('page.position'))
<ul class="card">
	@foreach ($positions as $pos)
	<li><a href="{{ route('page.position', ['id'=>$pos->posid]) }}">{{ $pos->title }}</a></li>
	@endforeach
</ul>
@endif
@endsection