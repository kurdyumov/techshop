<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ __(config('app.name')) }}</title>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	@vite(['resources/js/jquery-3.7.1.min.js', 'resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
	<nav class="nav container">
		<a class="btn" href="{{ route('index') }}">{{ __('Главная') }}</a>
		@if (Auth::check() && !is_null(Auth::user()->getTypes()))
		<ul class="nav-item list-unstyled d-flex me-auto">
			@if (Route::has('action.search') && Auth::user()->getTypes()->is_client)
				<li><a class="btn" href="{{ route('action.search') }}">{{ __('Товары') }}</a></li>
			@endif
			@auth
				@if (Route::has('page.claims') && Auth::user()->getTypes()->is_client)
					<li><a class="btn" href="{{ route('page.claims') }}">Обслуживание</a></li>
				@endif
				@if (Route::has('page.storage') && Auth::user()->getTypes()->is_employer)
					@role([0,1])
					<li><a class="btn" href="{{ route('page.storage') }}">Поставки</a></li>
					@endrole
				@endif
				@if (Route::has('page.supply') && Auth::user()->getTypes()->is_partner)
					<li><a class="btn" href="{{ route('page.supply') }}">Накладные</a></li>
				@endif
				@if (Route::has('page.regpos') && Auth::user()->getTypes()->is_employer)
					@role([0])
					<li><a class="btn" href="{{ route('page.regpos') }}">Позиции</a></li>
					@endrole
				@endif
				@if (Route::has('page.edituser') && Auth::user()->getTypes()->is_employer)
					@role([0])
					<li><a class="btn" href="{{ route('page.edituser') }}">Пользователи</a></li>
					@endrole
				@endif
				@if (Route::has('page.claims') && Auth::user()->getTypes()->is_employer)
					@role([2,3])
					<li><a class="btn" href="{{ route('page.claims') }}">Тех.обслуживание</a></li>
					@endrole
				@endif
			@endauth
		</ul>
		@endif
		<ul class="nav-item list-unstyled d-flex ms-auto">
			@guest
				@if (Route::has('page.login'))
					<li><a class="btn" href="{{ route('page.login') }}">{{ __('Войти') }}</a></li>
				@endif
				@if (Route::has('page.signup'))
					<li><a class="btn" href="{{ route('page.signup') }}">{{ __('Регистрация') }}</a></li>
				@endif
			@endguest
			@auth
				@if (Route::has('page.profile'))
					<li><a class="btn" href="{{ route('page.profile') }}">{{ __('Профиль') }}</a></li>
				@endif
				@if (Route::has('action.logout'))
					<li><a class="btn" href="{{ route('action.logout') }}">{{ __('Выход') }}</a></li>
				@endif
			@endauth
		</ul>
	</nav>
	<div class="container">
		@yield('div')
	</div>
</body>
</html>