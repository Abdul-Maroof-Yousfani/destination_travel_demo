{{-- Use if you want to show data without sidebars --}}

@extends('admin/layouts/master')

@include('admin/layouts/navbar')

<!-- Content -->
@yield('content')
<!-- Content -->

@include('admin/layouts/footer')
@endsection