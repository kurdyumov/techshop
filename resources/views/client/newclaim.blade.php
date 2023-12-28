@extends('app')
@section('div')
<h2>Новая заявка</h2>
	@if (Route::has('action.init_claim') && isset($orders) && isset($types))
	<form action="{{ route('action.init_claim') }}" method="post">
		@csrf
		<select name="order">
			@foreach ($orders->get() as $order)
			<option value="{{ $order->orderid }}">#{{ $order->orderid }}</option>
			@endforeach
		</select>
		<select name="sku"></select>
		<select name="type">
			@foreach ($types->get() as $type)
			<option value="{{ $type->ctid }}">{{ $type->title }}</option>
			@endforeach
		</select>
		<textarea name="description" maxlength="300" class="form-control" name="" rows="3" cols="5" placeholder="Описание" style="resize: none;"></textarea>
		<button type="submit">Создать</button>
	</form>
	@endif
	@isset ($choice)
	<p>{{ $choice }}</p>
	@endisset

<script>
$(document).ready(function() {
	let select = $('select[name="order"]').val();
	getSkus(select);
	$('select[name="order"]').on('change', function() {
		var value = $(this).val();
		getSkus(value);
	});
});

function buttonStatus(off) {
	const submit = $('button[type="submit"]');
	submit.prop('disabled', off);
}

function getSkus(value) {
	const token = $('input[name="_token"]');

	var currentUrl = window.location.href;
	console.log(currentUrl);
	$.ajax({
		type: 'get',
		url: currentUrl,
		data: {
			orderid: value
		},
		success: function(response) {
			const skuSelect = $('select[name="sku"]');
			skuSelect.empty();
			if (response.goods.length > 0)
			$.each(response.goods, function (i, item) {
				skuSelect.append($('<option>', {
					value: item.storagegoods_sku,
					text: "["+item.storagegoods_sku+"] "+item.title,
				}));
				skuSelect.prop('disabled', false);
				buttonStatus(false);
			});
			else {
				skuSelect.append($('<option>', {
					value: "",
					text: "Пусто"
				}));
				skuSelect.prop('disabled', true);
				buttonStatus(true);
			}
		},
		// success: function(response) {
		// 	console.log(response);
		// },
		error: function(response) {
			console.log(response);
		}
	});
}
</script>
@endsection