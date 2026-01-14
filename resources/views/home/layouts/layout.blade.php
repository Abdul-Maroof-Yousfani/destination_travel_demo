{{-- Use if you want to show data without sidebars --}}
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />

  <title>@yield('title') | travelandtours</title>
  <meta name="description" content="{{ config('variables.metaDescription') ?? '' }}" />
  <meta name="keywords" content="{{ config('variables.metaKeyword') ?? '' }}">
  
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ url('assets/images/favicon.png') }}" type="image/x-icon">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ url('assets/js/jquery.js') }}"></script>
  @yield('style')
</head>

<body>

  <!-- Layout Content -->
  @yield('content')
  <!--/ Layout Content -->

  <!-- Include Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @include('home/partials/scripts')
  @include('combine/scripts')
  @yield('script')

</body>

</html>