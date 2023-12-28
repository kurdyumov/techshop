@extends('app')
@section('div')
<h2>Управление поставками</h2>
@if (false)
	@isset ($positions)
	<form class="form" action="{{ route('page.storage') }}" method="get">
		<div class="form-group row">
			<label class="col-sm-2">
				Название
			</label>
			<input type="text" name="title" class="col-sm-2">
		</div>
		<div class="form-group row">
			<label class="col-sm-2">
				Позиция
			</label>
			<select name="position" class="col-sm-2">
				<option value="" selected disabled hidden>Позиция</option>
				<option value=""></option>
				@foreach ($positions as $pos)
				<option value="{{ $pos->posid }}">{{ '['.$pos->posid.'] '.$pos->title }}</option>
				@endforeach
			</select>
		</div>
		<button type="submit">Фильтр</button>
	</form>
	@endisset
@endif
	@isset ($sg_invoices)
		<h3>Накладные</h3>
		<div class="accordion">
		@foreach ($sg_invoices as $suid=>$items)
		<div class="accordion-item">
			<legend class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">Накладная #{{ $suid }}</button>
			</legend>
			<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
					<ul>
					@foreach ($items as $item)
						<li>{{ $item->sku }} [{{ App\Services\OutputService::printPosTitle($item->position_posid) }}]</li>
					@endforeach
					</ul>
					@if (Route::has('action.submit_sg_invoice') &&
						is_null(App\Services\OutputService::getSGInvoice($suid)->employer_employerid)
					)
					<form action="{{ route('action.submit_sg_invoice', [$suid]) }}">
						<button type="submit">Принять</button>
					</form>
					@else
					<p class="success">Согласовано</p>
					@endif
				</div>
			</div>
		</div>
		@endforeach
		</div>
	@endisset
@endsection