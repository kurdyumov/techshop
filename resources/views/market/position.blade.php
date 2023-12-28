@extends('app')
@section('div')
@if (isset($position) && isset($metadata))
<h2>{{ $position->title }}</h2>
<table>
	<thead>
		<tr>
			<th>Характеристика</th>
			<th>Значение</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($metadata as $property)
		<tr>
			<th>{{ $property->title }}</th>
			<td>{{ $property->value }}</td>
		</tr>
		@endforeach
		<tr>
			<th>Стоимость</th>
			<td>{{ $position->price }} р.</td>
		</tr>
	</tbody>
</table>
@if (Route::has('action.addtobasket') && isset($instock))
	<p>В наличии: {{ $instock->get()->count() }}</p>
	@if ($instock->get()->count() > 0)
	<form action="{{ route('action.addtobasket', [$position->posid]) }}" method="post">
		@csrf
		<input type="number" name="amount" min="1" max="{{ $instock->get()->count() }}">
		<button type="submit">В корзину</button>
	</form>
	@endif
@endif
@endif
@endsection