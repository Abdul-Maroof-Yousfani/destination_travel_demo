@extends('admin/layouts/master')

@section('title', 'Admin Dashboard')

@section('style')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
   .card-metric {
      border-left: 4px solid #0d6efd;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
   }
   .chart-container {
      position: relative;
      height: 300px;
   }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

   <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">Welcome, {{ auth()->user()->name }}</h2>

      <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex align-items-center">
         <input type="text" id="daterange" class="form-control me-2 my-0" name="daterange" value="{{ $fromDate->format('Y-m-d') }} - {{ $toDate->format('Y-m-d') }}" autocomplete="off">
         <input type="hidden" name="from_date" id="from_date" value="{{ $fromDate->format('Y-m-d') }}">
         <input type="hidden" name="to_date" id="to_date" value="{{ $toDate->format('Y-m-d') }}">
         @if(request()->has('from_date') || request()->has('to_date'))
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn_primary">Reset</a>
         @endif
      </form>
   </div>

   <div class="row text-center">
      <div class="col-md-2 mb-3">
         <div class="card card-metric border-primary">
            <div class="card-body">
               <h6>Total Orders</h6>
               <h4>{{ $totalOrdersWOError }}</h4>
            </div>
         </div>
      </div>
      <div class="col-md-2 mb-3">
         <div class="card card-metric border-warning">
            <div class="card-body">
               <h6>Initial Orders</h6>
               <h4>{{ $initialOrders }}</h4>
            </div>
         </div>
      </div>
      <div class="col-md-2 mb-3">
         <div class="card card-metric border-success">
            <div class="card-body">
               <h6>Issued Orders</h6>
               <h4>{{ $issuedOrders }}</h4>
            </div>
         </div>
      </div>
      <div class="col-md-2 mb-3">
         <div class="card card-metric border-danger">
            <div class="card-body">
               <h6>Cancel Orders</h6>
               <h4>{{ $cancelOrders }}</h4>
            </div>
         </div>
      </div>
      <div class="col-md-4 mb-3">
         <div class="card card-metric border-primary">
            <div class="card-body">
               <h6>Total Revenue</h6>
               <h4>PKR {{ number_format($totalRevenue, 2) }}</h4>
            </div>
         </div>
      </div>
      {{-- <div class="col-md-2 mb-3">
         <div class="card card-metric border-warning">
            <div class="card-body">
               <h6>Average Ticket</h6>
               <h4>PKR {{ number_format($averageTicket, 2) }}</h4>
            </div>
         </div>
      </div> --}}
   </div>

   {{-- Only show charts if user has permission --}}
   @if($canViewAll)
   <div class="row mt-4">
      <div class="col-md-4 mb-4">
         <div class="card">
            <div class="card-header">Orders by Status</div>
            <div class="card-body">
               <div class="chart-container">
                  <canvas id="ordersStatusChart"></canvas>
               </div>
            </div>
         </div>
      </div>

      <div class="col-md-4 mb-4">
         <div class="card">
            <div class="card-header">Bookings by Airline</div>
            <div class="card-body">
               <div class="chart-container">
                  <canvas id="bookingsAirlineChart"></canvas>
               </div>
            </div>
         </div>
      </div>

      <div class="col-md-4 mb-4">
         <div class="card">
            <div class="card-header">Revenue Trend</div>
            <div class="card-body">
               <div class="chart-container">
                  <canvas id="revenueChart"></canvas>
               </div>
            </div>
         </div>
      </div>
   </div>
   @endif

   <div class="row mt-4">
      <div class="col-md-12">
         <div class="card">
            <div class="card-header">Recent Orders</div>
            <div class="card-body table-responsive">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>Serial No.</th>
                        <th>Order ID</th>
                        <th>Reference</th>
                        <th>Airline</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Created At</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($recentOrders as $order)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->flight_booking_id ?? $order->order_id ?? '' }}</td>
                        <td>{{ $order->airline }}</td>
                        <td><span class="badge bg-{{ $order->status == 'issued' ? 'success' : ($order->status == 'error' ? 'danger' : 'secondary') }}">{{ strtoupper($order->status) }}</span></td>
                        <td>{{ $order->type ?? '-' }}</td>
                        <td>{{ $order->total_price ?? 0 }}</td>
                        <td>{{ $order->created_at->format('d M Y h:i a') }}</td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

</div>
@endsection

@section('script')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
   $(function() {
      const start = moment("{{ $fromDate->format('Y-m-d') }}");
      const end   = moment("{{ $toDate->format('Y-m-d') }}");

      function updateDisplay(start, end) {
         $('#daterange').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
         $('#from_date').val(start.format('YYYY-MM-DD'));
         $('#to_date').val(end.format('YYYY-MM-DD'));
      }

      $('#daterange').daterangepicker({
         startDate: start,
         endDate: end,
         ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
         },
         locale: {
            format: 'YYYY-MM-DD'
         }
      }, updateDisplay);

      updateDisplay(start, end);
   });
   $('#daterange').on('apply.daterangepicker', function(ev, picker) {
      $(this).closest('form').submit();
   });

   @if($canViewAll)
      new Chart(document.getElementById('ordersStatusChart'), {
         type: 'doughnut',
         data: {
            labels: {!! json_encode($statusData->keys()) !!},
            datasets: [{
               data: {!! json_encode($statusData->values()) !!},
               backgroundColor: ['#0d6efd','#198754','#dc3545','#ffc107']
            }]
         }
      });

      new Chart(document.getElementById('bookingsAirlineChart'), {
         type: 'bar',
         data: {
            labels: {!! json_encode($bookingsByAirline->pluck('airline')) !!},
            datasets: [{
               label: 'Total Bookings by Airline',
               data: {!! json_encode($bookingsByAirline->pluck('total')) !!},
               backgroundColor: '#0d6efd'
            }]
         },
         options: { responsive: true, plugins: { legend: { display: false } } }
      });

      // Revenue Trend chart
      new Chart(document.getElementById('revenueChart'), {
         type: 'line',
         data: {
            labels: {!! json_encode($revenueTrend->pluck('date')) !!},
            datasets: [{
               label: 'Revenue (PKR)',
               data: {!! json_encode($revenueTrend->pluck('total')) !!},
               fill: true,
               borderColor: '#0d6efd',
               tension: 0.3
            }]
         }
      });
   @endif
</script>
@endsection
