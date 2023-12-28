@if (Route::has('page.profile') && isset($closed))
<h2>Оформление заказа</h2>
<p>Бла-бла-бла, статус оформления: {{ $closed }}, бла-бла-бла...</p>
<a href="{{ route('page.profile') }}">Обратно на сайт</a>
@endif