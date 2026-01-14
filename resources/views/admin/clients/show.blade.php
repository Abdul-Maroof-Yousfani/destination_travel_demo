@extends('admin.layouts.master')

@section('title', 'Client Details')

@section('content')
<div class="d-flex flex-column justify-content-between h-100">
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">{{ $client->name }}</h2>
            <a href="{{ route('admin.clients.index') }}" class="btn btn_primary mb-3">← Back to List</a>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $client->profile_path ? asset('storage/' . $client->profile_path) : asset('assets/images/default-avatar.jpg') }}" 
                                    alt="Profile" class="rounded-circle me-3" width="70" height="70">
                                <div>
                                    <h4 class="mb-0">{{ $client->title ?? 'Mrs' }} {{ $client->name }}</h4>
                                    <small class="text-muted">{{ $client->email }}</small><br>
                                    <span class="badge bg-{{ $client->is_active ? 'success' : 'secondary' }}">
                                        {{ $client->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div><button class="btn_danger deleteClientBtn"><i class='bx bx-trash me-2'></i>Delete</button></div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <h6>Phone</h6>
                                <p>{{ $client->full_phone }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Email</h6>
                                <p>{{ $client->email ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>IP Address</h6>
                                <p>{{ $client->ip ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Login Provider</h6>
                                <p>{{ $client->login_provider ?? 'Manual' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Country</h6>
                                <p>{{ $client->country_name ?? 'N/A' }} ({{ $client->country_code ?? 'N/A' }})</p>
                            </div>
                            <div class="col-md-4">
                                <h6>City</h6>
                                <p>{{ $client->city ?? 'N/A' }} ({{ $client->city_code ?? 'N/A' }})</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm mb-3">
                    <div class="card-body">
                        <h6>Total Bookings</h6>
                        <h3>{{ $totalBookings }}</h3>
                    </div>
                </div>
                <div class="card text-center shadow-sm mb-3">
                    <div class="card-body">
                        <h6>Total Passengers</h6>
                        <h3>{{ $totalPassengers }}</h3>
                    </div>
                </div>
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h6>Total Revenue</h6>
                        <h3>PKR {{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Recent Bookings</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Airline</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Created</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($client->bookings->take(10) as $booking)
                            <tr class="clickable-row pointer"
                                data-href="{{ route('admin.orders.details', $booking->id) }}"
                                style="cursor: pointer;">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $booking->order_id }}</td>
                                <td>{{ $booking->airline }}</td>
                                <td>{{ $booking->type }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'issued' ? 'success' : ($booking->status === 'cancel' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($booking->status) }}
                                    </span>
                                </td>
                                <td>{{ $booking->total_tax_price }}</td>
                                <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                <td>{{ optional($booking->agent)->name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Passengers -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <strong>Passengers</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Nationality</th>
                            <th>Passport / Cnic</th>
                            <th>Expiry</th>
                            <th>Date of Birth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($client->passengers as $passenger)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $passenger->title }} {{ $passenger->given_name }} {{ $passenger->surname }}</td>
                                <td>{{ strtoupper($passenger->type) }}</td>
                                <td>{{ $passenger->nationality }}</td>
                                <td>{{ $passenger->passport_no }}</td>
                                <td>{{ $passenger->passport_exp?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>{{ $passenger->dob?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No passengers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $('.deleteClientBtn').on('click', function() {
        if (!confirm('Are you sure?')) return;
        let id = "{{ $client->id }}";
        let url = "{{ route('admin.clients.update', ':id') }}".replace(':id', id);
        let backUrl = "{{ route('admin.clients.index') }}";
        $.ajax({
            url: url,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function() {
                window.location.href = backUrl;
            }
        });
    });
</script>
@endsection