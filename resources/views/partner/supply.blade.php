@extends('app')
@section('div')
<h2>Накладные</h2>
@if (Route::has('page.init_supply_invoice'))
<a class="btn btn-secondary" href="{{ route('page.init_supply_invoice') }}">Новая поставка</a>
	@isset ($invoices)
	<div class="accordion mt-3">
		@foreach ($invoices as $suid=>$items)
		<div class="accordion-item m-2">
			<legend class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Накладная #{{ $suid }}</button>
			</legend>
			<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
				<div class="accordion-body">
					@foreach ($items as $item)
						<li>{{ $item->sku }}</li>
					@endforeach
				</div>
				<p>
					{{ is_null(((new App\Services\SqlService())->retrieveSupplyInvoice($suid)->first()->employer_employerid))?'Ожидается':'Принято' }}
				</p>
		    </div>
			<ul>
			</ul>
		</div>
		@endforeach
	</div>
	@endisset
@endif
@endsection