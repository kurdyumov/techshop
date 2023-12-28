@extends('app')
@section('div')
<h2>Создать накладную</h2>
	@if (Route::has('action.init_supply_invoice'))
	<form id="invoice-form" action="{{ route('action.init_supply_invoice') }}" method="post">
		@csrf
		<div id="inputs">
		</div>
		<button type="button" onclick="addInput()">[+]</button>
		<button type="submit" disabled>Создать</button>
	</form>
	@endif
<script>
	addInput();

	function isDataReady() {
		requestAnimationFrame(function() {
			const form = document.getElementById("invoice-form");
			const submit = form.querySelector("button[type='submit']");
			submit.disabled = false;

			const inputs = form.querySelectorAll('input[name="input[]"]');
			inputs.forEach(function (e) {
				const value = e.value;
				const regExp = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{10}$/;
				if (!regExp.test(value))
					submit.disabled = true;
			});
		});
	}

	function addInput() {
		isDataReady();

		const container = document.getElementById('inputs');
		const block = document.createElement('div');

		const input = document.createElement('input');
		input.type = 'text';
		input.name = 'input[]';
		input.required = true;
		input.onbeforeinput = function (e) {
			requestAnimationFrame(function() {
				isDataReady();
			});
		}

		const button = document.createElement('button');
		button.type = 'button';
		button.textContent = '[X]';
		button.onclick = function(e) {
			removeInput(this);
		};

		const select = document.createElement('select');
		select.name = 'posid[]';
		const optionsData = @json($positions->get());

		optionsData.forEach(function (option) {
			const optionEl = document.createElement('option');
			optionEl.value = option.posid;
			optionEl.text = "[" + option.posid + "] " + option.title;
			select.appendChild(optionEl);
		});

		block.appendChild(input);
		block.appendChild(select);
		if (container.children.length > 0)
		block.appendChild(button);
		container.appendChild(block);
	}

	function removeInput(button) {
		const block = button.parentNode;
		block.parentNode.removeChild(block);
		isDataReady();
	}
</script>
@endsection