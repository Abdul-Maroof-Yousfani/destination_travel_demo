@extends('home/layouts/master')

@section('title', 'Search for your bookings')
@section('style')
  <link rel="stylesheet" href="{{ url('assets/css/login.css') }}">
@endsection
@section('content')
  <section class="search-bookings">
    <div class="container d-flex justify-content-center">
      <div class="booking-box">
        <h2>Search for your bookings</h2>
        <form method="POST" action="{{ route('search.booking.submit') }}">
          @csrf
          <div class="form-group">
            <label>Order ID</label>
            <input type="text" placeholder="4546385" id="order_id" name="order_id" required value="{{ old('order_id') }}">
            <div class="form-note">Your Order ID is emailed with booking confirmation.</div>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" id="email" placeholder="e.g. name@gmail.com" required value="{{ old('email') }}">
            <div class="form-note">The email address entered during booking.</div>
          </div>
          <button type="submit" class="loginBtn" disabled>Search</button>
        </form>
      </div>
    </div>
  </section>
@endsection
@section('script')
  <script>
    $(document).ready(function () {
      function validateForm() {
        console.log('validateForm')
        let email = $('#email').val().trim();
        let order_id = $('#order_id').val().trim();
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailRegex.test(email) && order_id.length > 0) {
            $('.loginBtn').addClass('active').prop('disabled', false);
        } else {
            $('.loginBtn').removeClass('active').prop('disabled', true);
        }
      }

      $('#email, #order_id').on('input', validateForm);
    });
  </script>
@endsection
