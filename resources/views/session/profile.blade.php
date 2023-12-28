@extends('app')
@section('div')
<h2>{{ App\Services\OutputService::printFIO() }}</h2>
@if (Auth::user()->getTypes()->is_employer)
<div class="card">
	<legend>Роли:</legend>
	<ul>
		@foreach (Auth::user()->getMetadata()['roles'] as $i=>$role)
		<li>{{ '['.$i.'] '.$role }}</li>
		@endforeach
	</ul>
</div>
@endif
@if (isset($basket) && isset($order) && !is_null($order) && !is_null(Auth::user()->getOrder()) && Auth::user()->getTypes()->is_client)
<div class="card inline">
	<div>
		<legend>Корзина</legend>
		<table>
		@foreach ($basket->get() as $item)
			<tr>
				<th>{{ $item->title }}</th>
				<td>{{ $item->price }} р.</td>
				<td>
					@if (Route::has('action.rmfrombasket'))
					<form action="{{ route('action.rmfrombasket', ['sku'=>$item->storagegoods_sku]) }}" method="get">
						<button type="submit">[X]</button>
					</form>
					@endif
				</td>
			</tr>
		@endforeach
		</table>
	</div>
	<div>
		<div>
			<legend>Детали заказа</legend>
			<table>
				<tr>
					<td>Заказ №</td>
					<th>{{ $order->orderid }}</th>
				</tr>
				<tr>
					<td>Создан</td>
					<th>{{ $order->createdt }}</th>
				</tr>
				<tr>
					<td>Итого</td>
					<th>{{ $total }}</th>
				</tr>
			</table>
			@if (Route::has('action.submit_order') && (new App\Services\SqlService())->retrieveCurrentBasket()->count() > 0)
			<form action="{{ route('action.submit_order') }}" method="get">
				<button type="submit">Оформить</button>
			</form>
			@endif
		</div>
	</div>
</div>
@endif
<p>//Сделай крч тут настройки аккаунта и не делай себе мозги =)</p>
@endsection