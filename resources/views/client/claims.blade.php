@extends('app')
@section('div')
<h2>Сервисный центр магазина {{ __(config('app.name')) }}</h2>
	@if (Route::has('page.new_user_ticket') && Auth::user()->getTypes()->is_client)
		<a class="btn btn-success" href="{{ route('page.new_user_ticket') }}">Новая заявка</a>
	@endif
	@isset($claims)
		<div class="accordion mt-3">
		@foreach ($claims->get() as $claim)
			<div class="accordion-item m-3">
				<legend class="accordion-header">
					<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">Заявка #{{ $claim->claimid }}</button>
				</legend>
				<div class="accordion-collapse collapse show">
					<div class="accordion-body">
					<?php $claimData = (new App\Services\SqlService())->retrieveClaimDetails($claim->claimid)['claim']; ?>
					<table>
						<tr>
							<th>SKU товара</th>
							<td>{{ $claim->storagegoods_sku }}</td>
						</tr>
						<tr>
							<th>Вид повреждения</th>
							<td>{{ $claimData->ct_title }}</td>
						</tr>
						<tr>
							<th>Описание</th>
							<td>{{ $claimData->description }}</td>
						</tr>
						<tr>
							<th>Мастер</th>
							<td>{{ $claimData->employer_employerid }}</td>
						</tr>
					</table>
					@if (Route::has('page.claim_details'))
						<a class="btn btn-secondary" href="{{ route('page.claim_details', [$claim->claimid]) }}">Подробности</a>
					@endif
					</div>
				</div>			
			</div>
		@endforeach
		</div>
	@endisset
@endsection