<!DOCTYPE html>
<html lang="id" class="guest-html">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Login')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('images/logospm4.png') }}">
@vite(['resources/css/login.css'])
</head>
<body class="guest-body">
@yield('content')
@stack('scripts')
</body>
</html>
