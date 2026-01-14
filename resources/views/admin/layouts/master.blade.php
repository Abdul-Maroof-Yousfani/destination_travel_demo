<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />

  <title>@yield('title') | travelandtours</title>
  <meta name="description" content="{{ config('variables.metaDescription') ?? '' }}" />
  <meta name="keywords" content="{{ config('variables.metaKeyword') ?? '' }}">

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ url('assets/images/favicon.png') }}" type="image/x-icon">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="{{ url('assets/js/jquery.js') }}"></script>
  <link rel="stylesheet" href="{{ url('assets/admin/style.css') }}">
  @yield('style')
  <style>
    input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button {-webkit-appearance: none;margin: 0;}
    input[type=number] {-moz-appearance: textfield;}
    #loader{width:100%;height:100%;display:flex;justify-content:center;align-items:center;background-color:rgba(0,0,0,0.5) !important;z-index:9999;position:fixed;top:0;left:0;overflow:hidden;transition:opacity 0.5s ease;}
    #loaderChild{width:30px;height:30px;border:5px solid #fff;border-top-color:#007bff;border-radius:50%;animation:spin 2s linear infinite;}
    .content {
      padding: 20px;
      height: calc(100vh - 60px);
    }
    header {
      position: sticky !important;
      top: 0;
      z-index: 1000;
      background-color: #f8f9fa;
      padding: 10px 20px;
      border-bottom: 1px solid #dee2e6;
    }
    #preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: #fff;
      z-index: 9999;
    }
    #preloader .dual-ring {
      width: 40px;
      height: 40px;
      border: 4px solid #3498db;
      border-radius: 50%;
      border-top-color: transparent;
      animation: dual-ring 1.2s linear infinite;
    }
    @keyframes dual-ring {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    header {

    }
  </style>
</head>

<body>
  <!-- Preloader -->
  <div id="preloader" class="d-none">
    <div data-loader="dual-ring"></div>
  </div>
  <!--/ Preloader -->
  @if (session('message'))
    <script>
      $(document).ready(function() {
        _alert("{{ session('message') }}", "{{ session('status') }}");
      });
    </script>
  @endif
  @if (session('error'))
    <script>
      $(document).ready(function() {
        _alert("{{ session('error') }}", "error");
      });
    </script>
  @endif
  @if ($errors->any())
    <script>
      $(document).ready(function () {
        _alert("{{ $errors->first() }}", "error");
      });
    </script>
  @endif
  @include('admin/layouts/navbar')
  <div class="content container-fluid">
  
  @if (session('message'))
    <script>
      $(document).ready(function () {
        _alert("{{ session('message') }}", "{{ session('status') }}");
      });
    </script>
  @endif
    <!-- Layout Content -->
    @yield('content')
    <!--/ Layout Content -->
  </div>

  <!-- Include Scripts -->
  {{-- @include('admin/layouts/footer') --}}
  @include('combine/scripts')
  <script src="{{ url('assets/admin/script.js') }}"></script>
  @yield('script')

</body>

</html>