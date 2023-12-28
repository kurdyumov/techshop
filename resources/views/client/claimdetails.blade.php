@extends('app')
@section('div')
@isset ($claim)
<?php 
	$data = $claim['claim']; 
	// dd($data);
	$totals = (new App\Services\SqlService())->countClaimPrice($data->claimid);
	$total = 0;
	foreach ($totals as $t)
		$total += $t;

	$statuses = [];
	foreach ($claim['status'] as $s)
		$statuses[] = $s->csid;
?>
<h2>Заявка #{{ $data->claimid }}</h2>
<div class="card">
	<div class="card-body">
		<legend class="card-title">Основные сведения</legend>
		<p class="card-text">
			<table>
				<tr>
					<th>Техника:</th>
					<td>{{ '['.$data->storagegoods_sku.'] '.$data->title }}</td>
				</tr>
				<tr>
					<th>Категория:</th>
					<td>{{ $data->ct_title }}</td>
				</tr>
				<tr>
					<th>Описание:</th>
					<td>{{ $data->description }}</td>
				</tr>
				<tr>
					<th>Мастер:</th>
					<td>
						@if (!is_null($data->employer_employerid))
							{{ $data->employer_employerid }}
						@elseif (Auth::user()->getTypes()->is_client)
							<span>Не назначен</span>
						@endif
					</td>
				</tr>
				@if (Route::has('action.define_master') && Auth::user()->getTypes()->is_employer && isset($masters))
				@role([3])
				<tr>
					<td></td>
					<td>
						<form action="{{ route('action.define_master', [$data->claimid]) }}">
							<select name="master">
								@foreach ($masters->get() as $m)
								<option value="{{ $m->employerid }}">{{ '['.$m->employerid.'] '.$m->lfp }}</option>
								@endforeach
							</select>
							<button type="submit">Назначить</button>
						</form>
					</td>
				</tr>
				@endrole
				@endif
				<tr>
					<th>Предв. итого:</th>
					<td>{{ $total }} р.</td>
				</tr>
			</table>
			@if (Route::has('action.pay_claim') && in_array(4, $statuses) && !in_array(5, $statuses) && Auth::user()->getTypes()->is_client)
				<a href="{{ route('action.pay_claim', [$data->claimid]) }}">Оплата</a>
			@endif
		</p>
	</div>
</div>
<?php 
	$data = $claim['status'];
	$relevaneStatus = !empty(array_intersect([0,1], $statuses)) && empty(array_intersect([2,3,4], $statuses));
	$editable = Auth::user()->getTypes()->is_employer?
		isset($claim['claim']->employer_employerid) && 
		!is_null($claim['claim']->employer_employerid) && 
		$claim['claim']->employer_employerid == (new App\Services\SqlService())->retrieveEmpIdFromUSer(Auth::user()->getUser()->contactid) &&
		$relevaneStatus : false;
		// dd($relevaneStatus);
?>
<div class="card">
	<div class="card-body">
		<legend class="card-title">Статус</legend>
		@if (Route::has('action.set_claim_status') && in_array(1, $statuses) && Auth::user()->getTypes()->is_employer)
		@role([3])
		<form action="{{ route('action.set_claim_status', [$claim['claim']->claimid]) }}">
			<select name="status">
				@foreach ((new App\Services\SqlService())->retrieveStatusList()->get() as $status)
				@if (!in_array($status->csid, $statuses))
				<option value="{{ $status->csid }}">{{ '[#'.$status->csid.'] '.$status->title }}</option>
				@endif
				@endforeach
			</select>
			<button type="submit">Сохранить</button>
		</form>
		@endrole
		@endif
		<p class="card-text">
			<table>
				<tr>
					<th>Статус</th>
					<th>Дата</th>
				</tr>
				@foreach ($data as $status)
				<tr>
					<td>{{ $status->title }}</td>
					<td>{{ $status->updatedt }}</td>
					@if (Route::has('action.expunge_from_claim') && 
						Auth::user()->getTypes()->is_employer && 
						!in_array($status->csid, [0,1])
					)
						@role([3])
						<td>
						<form action="{{ route('action.expunge_from_claim', [$claim['claim']->claimid, 3, $status->csid]) }}">
							<button type="submit">[X]</button>
						</form>
						</td>
						@endrole
					@endif
				</tr>
				@endforeach
			</table>
		</p>
	</div>
</div>
<?php
	 $data = $claim['services']; 
?>
<div class="card">
	<div class="card-body">
		<legend class="card-title">Услуги</legend>
		<p class="card-text">
			<table>
				<tr>
					<th>Услуга</th>
					<th>Стоимость</th>
					<th>Количество</th>
				</tr>
				@foreach ($data as $service)
				<tr>
					<td>{{ $service->title }}</td>
					<td>{{ $service->price }} р.</td>
					<td>{{ $service->amount }}</td>
					@if (Route::has('action.expunge_from_claim') && Auth::user()->getTypes()->is_employer && $editable)
						@role([2,3])
						<td>
						<form action="{{ route('action.expunge_from_claim', [$claim['claim']->claimid, 0, $service->serviceid]) }}">
							<button type="submit">[X]</button>
						</form>
						</td>
						@endrole
					@endif
				</tr>
				@endforeach
			</table>
			@if (Route::has('action.supply_claim') && Auth::user()->getTypes()->is_employer && isset($masters) && $editable)
			@role([2,3])
				<?php
					$services = (new App\Services\SqlService())->retrieveServices($claim['claim']->claimid)->get();
				?>
			<form class="form" action="{{ route('action.supply_claim', [$claim['claim']->claimid, 0]) }}">
				<select name="data">
				@foreach ($services as $s)
					<option value="{{ $s->serviceid }}">{{ $s->title }}</option>
				@endforeach
				</select>
				<input name="amount" type="number" min="1">
				<button type="submit">Добавить</button>
			</form>
			@endrole
			@endif
			<p>
				Итого: {{ $totals['c_total'] }}
			</p>
		</p>
	</div>
</div>
<?php $data = $claim['components']; ?>
<div class="card">
	<div class="card-body">
		<legend class="card-title">Комплектующие</legend>
		<p class="card-text">
			<table>
				<tr>
					<th>Наименование</th>
					<th>Цена (шт.)</th>
					<th>Количество</th>
				</tr>
				@foreach ($data as $component)
				<tr>
					<td>{{ $component->title }}</td>
					<td>{{ $component->price }} р.</td>
					<td>{{ $component->amount }}</td>
					@if (Route::has('action.expunge_from_claim') && Auth::user()->getTypes()->is_employer && $editable)
						@role([2,3])
						<td>
						<form action="{{ route('action.expunge_from_claim', [$claim['claim']->claimid, 1, $component->posid]) }}">
							<button type="submit">[X]</button>
						</form>
						</td>
						@endrole
					@endif
				</tr>
				@endforeach
			</table>
			@if (Route::has('action.supply_claim') && Auth::user()->getTypes()->is_employer && isset($masters) && $editable)
			@role([2,3])
			<?php
				$components = (new App\Services\SqlService())->retrieveComponents($claim['claim']->claimid)->get();
				// dd($components);
			?>
			<form action="{{ route('action.supply_claim', [$claim['claim']->claimid, 1]) }}">
				<select name="data">
				@foreach ($components as $c)
					<option value="{{ $c->posid }}">{{ $c->title }}</option>
				@endforeach
				</select>
				<input name="amount" type="number" min="1">
				<button type="submit">Добавить</button>
			</form>
			@endrole
			@endif
			<p>
				Итого: {{ $totals['sr_total'] }}
			</p>
		</p>
	</div>
</div>
<?php $data = $claim['resources']; ?>
<div class="card">
	<div class="card-body">
		<legend class="card-title">Расходники</legend>
		<p class="card-text">
			<table>
				<tr>
					<th>Расходник</th>
					<th>Цена (шт.)</th>
					<th>Количество</th>
				</tr>
				@foreach ($data as $resource)
				<tr>
					<td>{{ $resource->title }}</td>
					<td>{{ $resource->price }} р.</td>
					<td>{{ $resource->amount }}</td>
					@if (Route::has('action.expunge_from_claim') && Auth::user()->getTypes()->is_employer && $editable)
						@role([2,3])
						<td>
						<form action="{{ route('action.expunge_from_claim', [$claim['claim']->claimid, 2, $resource->storageinner_siid]) }}">
							<button type="submit">[X]</button>
						</form>
						</td>
						@endrole
					@endif
				</tr>
				@endforeach
			</table>
			@if (Route::has('action.supply_claim') && Auth::user()->getTypes()->is_employer && isset($masters) && $editable)
			@role([2,3])
			<?php
				$supplies = (new App\Services\SqlService())->retrieveSupplies($claim['claim']->claimid)->get();
				// dd($supplies);
			?>
			<form action="{{ route('action.supply_claim', [$claim['claim']->claimid, 2]) }}">
				<select name="data">
					@foreach ($supplies as $s)
					<option value="{{ $s->siid }}">{{ $s->title }}</option>
					@endforeach
				</select>
				<input name="amount" type="number" min="1">
				<button type="submit">Добавить</button>
			</form>
			@endrole
			@endif
			<p>
				Итого: {{ $totals['siu_total'] }}
			</p>
		</p>
	</div>
</div>
@endisset
@endsection