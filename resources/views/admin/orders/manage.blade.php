@extends('admin/layouts/master')

@section('title', 'Order Manage')
@section('style')
  <style>
    .box {
      border: 1px solid #dee2e6;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 1rem;
      overflow-x: auto; 
    }
    .section-title {
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 0.5rem;
    }
    .accordion-button {
        font-weight: bold;
    }
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }
    .card-header {
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .alert {
        margin-bottom: 1rem;
    }
  </style>
@endsection
@section('content')
@php
    $airline = $booking->airline ? strtolower($booking->airline) : 'N/A';
@endphp
<div class="row">
    <!-- Left Side -->
    <div class="col-md-3 left-side">
        {{-- Ticket And Receipt Email --}}
        <div class="card box">
            <div class="section-title">Ticket And Receipt Email</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="paymentReceipt">
                <label class="form-check-label" for="paymentReceipt">Payment Receipt</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="eTicket">
                <label class="form-check-label" for="eTicket">E-ticket</label>
            </div>
            <button class="btn btn-sm btn_secondary">Send Email</button>
        </div>

        {{-- update.client --}}
        <form method="POST" action="{{ route('update.client', $booking->client->id) }}" class="card box">
            @csrf
            <div class="section-title">Contact Details (User)</div>
            <div class="mb-2">
                <input type="text" name="name" class="form-control mb-1" placeholder="name" value="{{ $booking->client->name }}">
                <input type="email" name="email" class="form-control mb-1" placeholder="email" value="{{ $booking->client->email }}">
                <div class="input-group mb-3">
                    <span class="input-group-text">+{{ $booking->client->phone_code }}</span>
                    <input type="number" placeholder="phone" name="phone" class="form-control m-0" value="{{ $booking->client->phone }}">
                </div>
                <button class="btn btn-sm btn_primary" type="submit">Update</button>
            </div>
        </form>

        {{-- Agent Selection --}}
        <div class="card box">
            <label class="form-label">Select Agent</label>
            <select class="form-select mb-2" id="agentSelect" @if (!empty($booking->agent_id)) disabled @endif>
                <option selected disabled value="">-- Select Agent --</option>
                @forelse ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected(isset($booking->agent_id) && $booking->agent_id == $agent->id)>{{ $agent->email }}</option>
                @empty
                    <option selected>-- No Agents Found --</option>
                @endforelse
            </select>
        </div>
        {{-- Notes --}}
        <div class="box notes-box">
            <div class="section-title d-flex justify-content-between">
                <span>Notes</span>
                <label for="noteImage" class="btn btn-sm btn_secondary">Add Image</label>
                <input type="file" class="d-none" name="noteImage" id="noteImage">
            </div>
            <textarea id="note-editor" class="form-control"></textarea> {{-- Summernote --}}
            <div class="d-flex justify-content-between mt-2">
                <button type="button" id="addNoteBtn" class="btn btn-sm btn_secondary">Add Notes</button>
                <button type="button" class="btn btn-sm btn_secondary_outline" id="showLogHistoryBtn">History</button>
            </div>
        </div>
        <!-- Notes Modal -->
        <x-modal id="logHistoryModal" title="Notes History" size="modal-lg">
            <div class="modal-body" id="logHistoryContent">
                <div class="text-center py-4">Loading...</div>
            </div>
        </x-modal>

        <div class="box notes-box">
            <div class="section-title">Add Discount</div>
            <input type="text" class="form-control mb-1" placeholder="Voucher Promocode">
            <button class="btn btn-sm btn_secondary mt-2">Add Discount</button>
        </div>

        {{-- Cancelation Charges --}}
        <div class="card box">
            <div class="section-title">Order Cancelation</div>
            <div class="d-flex justify-content-start gap-3 mt-2">
                @if ($booking->canceled_at)
                    <div class="py-2"><strong>Order Canceled At:</strong> {{ \Carbon\Carbon::parse($booking->canceled_at)->format('d M Y h:i A') }}</div>
                @else
                    @can('cancel booking')
                        <button class="btn btn-sm btn_secondary" data-bs-toggle="modal" data-bs-target="#cacelOrderDetailsModal">Cancel Order</button>
                    @endcan
                @endif
                {{-- <button class="btn btn-sm btn_secondary_outline">Refund Form</button> --}}
            </div>
            <!-- Approve Flight Modal -->
            <x-modal id="cacelOrderDetailsModal" title="Cancel / Change / Refund Fees" size="modal-lg">
                <div class="modal-body">
                    @if ($airline === 'flyjinnah' && $booking->status !== 'issued')
                        <p>Cancellation policies are not available for FlyJinnah bookings at this time.</p>
                    @else
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body p-0">
                                @if ($airline === 'emirates')
                                    @forelse ($booking->bookingItems as $item)
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Passenger(s): {{ $item->passenger_code }}</span>
                                        </div>
                                        @forelse ($item->penalties as $penalty)
                                            @php
                                                $cancel = is_array($penalty->cancel_fee) ? $penalty->cancel_fee : json_decode($penalty->cancel_fee, true);
                                                $change = is_array($penalty->change_fee) ? $penalty->change_fee : json_decode($penalty->change_fee, true);
                                                $refund = is_array($penalty->refund_fee) ? $penalty->refund_fee : json_decode($penalty->refund_fee, true);
                                            @endphp
                                            <div class="p-3 border-bottom">
                                                <h6 class="mb-3 text-muted">{{ $penalty->destination }} → {{ $penalty->arrival }}</h6>
                                                @php
                                                    $sections = [
                                                        'Cancel Fees' => $cancel,
                                                        'Change Fees' => $change,
                                                        'Refund Policy / Fees' => $refund
                                                    ];
                                                @endphp
                                                @foreach ($sections as $title => $data)
                                                    @if (!empty($data))
                                                        <h6 class="mb-2">{{ $title }}</h6>
                                                        @if ($title === 'Refund Policy / Fees' && isset($data['Status']))
                                                            <p class="mb-0"><strong>Status:</strong> {{ $data['Status'] }}</p>
                                                        @else
                                                            <table class="table table-sm align-middle mb-3">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 40%">When</th>
                                                                        <th style="width: 30%">Amount</th>
                                                                        <th style="width: 30%">Application</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($data as $when => $fee)
                                                                        <tr>
                                                                            <td>{{ $when }}</td>
                                                                            <td>{{ isset($fee['price']) ? ($fee['price']['amount'] ?? '') . ' ' . ($fee['price']['code'] ?? '') : '' }}</td>
                                                                            <td>{{ $fee['amountApplication'] ?? '' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if (empty($cancel) && empty($change) && empty($refund))
                                                    <p class="text-muted mb-0">No penalty info available.</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="p-3">
                                                <p class="text-muted mb-0">No penalties found for this booking item.</p>
                                            </div>
                                        @endforelse
                                    @empty
                                        <p class="text-muted mb-0">No booking items found.</p>
                                    @endforelse
                                @elseif ($airline === 'flyjinnah')
                                    @php
                                        $xmlBody = $booking->bookingRequest && isset($booking->bookingRequest->xml_body) ? json_decode($booking->bookingRequest->xml_body, true) : null;
                                        $airReservation = $xmlBody['response']['Body']['OTA_AirBookRS']['AirReservation'] ?? $xmlBody['Body']['OTA_AirBookRS']['AirReservation'] ?? null;
                                        $options = $airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? [];
                                        $options = is_array($options) && isset($options[0]) ? $options : [$options];
                                        $hasPenalties = false;
                                    @endphp
                                    @foreach ($options as $option)
                                        @if (!empty($option['FlightSegment']) && is_array($option['FlightSegment']))
                                            @foreach ($option['FlightSegment'] as $segment)
                                                @if (!empty($segment['AvailableFlexiOperations']['FlexiOperations']) && is_array($segment['AvailableFlexiOperations']['FlexiOperations']))
                                                    @php $hasPenalties = true; @endphp
                                                    <div class="p-3 border-bottom">
                                                        <h6 class="mb-3 text-muted">
                                                            {{ $segment['DepartureAirport']['@attributes']['LocationCode'] ?? 'N/A' }} → {{ $segment['ArrivalAirport']['@attributes']['LocationCode'] ?? 'N/A' }}
                                                        </h6>
                                                        <h6>Flexi Operations</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($segment['AvailableFlexiOperations']['FlexiOperations'] as $operation)
                                                                @if (is_array($operation) && !empty($operation['@attributes']))
                                                                    <li class="list-group-item">
                                                                        <strong>{{ $operation['@attributes']['AllowedOperationName'] ?? 'N/A' }}:</strong>
                                                                        Allowed {{ $operation['@attributes']['NumberOfAllowedOperations'] ?? 'N/A' }} time(s),
                                                                        Cutoff: {{ $operation['@attributes']['FlexiOperationCutoverTimeInMinutes'] ?? 'N/A' }} minutes
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                    @if (!$hasPenalties)
                                        <div class="p-3">
                                            <p class="text-muted mb-0">No penalty information available.</p>
                                        </div>
                                    @endif
                                @elseif ($airline === 'pia')
                                    @forelse ($booking->bookingItems as $item)
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Passenger(s): {{ $item->passenger_code }}</span>
                                        </div>
                                        @forelse ($item->penalties as $penalty)
                                            @php
                                                $cancel = is_array($penalty->cancel_fee) ? $penalty->cancel_fee : json_decode($penalty->cancel_fee, true);
                                                $change = is_array($penalty->change_fee) ? $penalty->change_fee : json_decode($penalty->change_fee, true);
                                                $refund = is_array($penalty->refund_fee) ? $penalty->refund_fee : json_decode($penalty->refund_fee, true);
                                            @endphp
                                            <div class="p-3 border-bottom">
                                                @if (!empty($penalty->destination) || !empty($penalty->arrival))
                                                    <h6 class="mb-3 text-muted">
                                                        {{ $penalty->destination }} → {{ $penalty->arrival }}
                                                    </h6>
                                                @endif
                                                
                                                @php
                                                    $sections = [
                                                        'Cancel Fees' => $cancel,
                                                        'Change Fees' => $change,
                                                        'Refund Policy / Fees' => $refund
                                                    ];
                                                @endphp
                                                
                                                @foreach ($sections as $title => $data)
                                                    @if (!empty($data) && is_array($data))
                                                        <h6 class="mb-2">{{ $title }}</h6>
                                                        <table class="table table-sm align-middle mb-3">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 40%">Type</th>
                                                                    <th style="width: 30%">Amount</th>
                                                                    <th style="width: 30%">Currency</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($data as $fee)
                                                                    <tr>
                                                                        <td>{{ $fee['type_code'] ?? 'N/A' }}</td>
                                                                        <td>{{ $fee['amount'] ?? '' }}</td>
                                                                        <td>{{ $fee['currency'] ?? '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                @endforeach
                                                
                                                @if (empty($cancel) && empty($change) && empty($refund))
                                                    <p class="text-muted mb-0">No penalty info available.</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="p-3">
                                                <p class="text-muted mb-0">No penalties found for this booking item.</p>
                                            </div>
                                        @endforelse
                                    @empty
                                        <p class="text-muted mb-0">No booking items found.</p>
                                    @endforelse
                                @elseif ($airline === 'airblue')
                                    @forelse ($booking->bookingItems as $item)
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Passenger(s): {{ $item->passenger_code }}</span>
                                        </div>
                                        @forelse ($item->penalties as $penalty)
                                            @php
                                                $cancel = is_array($penalty->cancel_fee) ? $penalty->cancel_fee : json_decode($penalty->cancel_fee, true);
                                                $change = is_array($penalty->change_fee) ? $penalty->change_fee : json_decode($penalty->change_fee, true);
                                                $refund = is_array($penalty->refund_fee) ? $penalty->refund_fee : json_decode($penalty->refund_fee, true);
                                            @endphp
                                            <div class="p-3 border-bottom">
                                                @if (!empty($penalty->destination) || !empty($penalty->arrival))
                                                    <h6 class="mb-3 text-muted">
                                                        {{ $penalty->destination }} → {{ $penalty->arrival }}
                                                    </h6>
                                                @endif

                                                @php
                                                    $sections = [
                                                        'Cancel Fees' => $cancel,
                                                        'Change Fees' => $change,
                                                        'Refund Policy / Fees' => $refund
                                                    ];
                                                @endphp

                                                @foreach ($sections as $title => $data)
                                                    @if (!empty($data) && is_array($data))
                                                        <h6 class="mb-2">{{ $title }}</h6>
                                                        <table class="table table-sm align-middle mb-3">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 40%">Hours Before Dep.</th>
                                                                    <th style="width: 30%">Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($data as $fee)
                                                                    @php
                                                                        $attributes = $fee['@attributes'] ?? [];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $attributes['HoursBeforeDeparture'] ?? 'N/A' }}</td>
                                                                        <td>{{ $attributes['CurrencyCode'] ?? '' }} {{ $attributes['Amount'] ?? '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                @endforeach

                                                @if (empty($cancel) && empty($change) && empty($refund))
                                                    <p class="text-muted mb-0">No penalty info available.</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="p-3">
                                                <p class="text-muted mb-0">No penalties found for this booking item.</p>
                                            </div>
                                        @endforelse
                                    @empty
                                        <p class="text-muted mb-0">No booking items found.</p>
                                    @endforelse
                                @else
                                    <div class="p-3">
                                        <p class="text-muted mb-0">Unsupported airline for penalty information.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <x-slot name="footer">
                    <button type="button" class="btn btn-danger cancelOrderBtn" data-booking-id="{{ $booking->id }}" data-client-id="{{ $booking->client_id }}">Cancel Booking</button>
                </x-slot>
            </x-modal>
        </div>
    </div>

    <!-- Right Side -->
    <div class="col-md-9 right-side">
        {{-- Top Btns --}}
        <div class="d-block d-md-flex justify-content-between">
            <a href="{{ route('admin.orders.index') }}" class="btn btn_secondary_outline d-flex align-items-center mb-3"><i class='bx bx-chevron-left'></i> Back to Order Management</a>
            <div class="btn-group d-block">
                @can('booking actions')
                    <button class="btn btn_secondary_outline" data-bs-toggle="modal" data-bs-target="#adminActions">Admin Actions</button>
                @endcan
                <button class="btn btn_secondary_outline" data-bs-toggle="modal" data-bs-target="#fareRulesModal">Show Fare Rules</button>
                <button class="btn btn_{{ $booking->client->status ? 'success' : 'danger' }}_outline">{{ $booking->client->status ? 'Regular User' : 'Guest User' }}</button>
                <button class="btn btn_{{ $booking->status === 'issued' ? 'success' : 'danger' }}">Ticket {{ strtoupper($booking->status) }}</button>
                <button class="btn btn_secondary_outline" data-bs-toggle="modal" data-bs-target="#detailedOverviewModal">Detailed Overview</button>
                @can('issue tickets')
                    @if ($booking->tickets->isEmpty())
                        <button class="btn btn-outline-dark updatePriceBtn" data-payment-exist="{{ $booking->payments->isEmpty() ? 0 : 1 }}" data-booking-id="{{ $booking->id }}" data-client-id="{{ $booking->client_id }}">Issue Tickets</button>
                    @endif
                @endcan
                @if ($booking->errorLogs->isNotEmpty())
                    <button class="btn btn_danger_outline" data-bs-toggle="modal" data-bs-target="#errorLogsModal">Error Logs</button>
                    <x-modal id="errorLogsModal" title="Error Logs" size="modal-lg">
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Error Type</th>
                                            <th>Message</th>
                                            {{-- <th>Details</th> --}}
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($booking->errorLogs as $index => $log)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ ucfirst($log->error_type) }}</td>
                                                <td>
                                                    @php
                                                        $errorMessage = json_decode($log->error_message, true);
                                                    @endphp
                                                    {{ is_array($errorMessage) ? implode(', ', $errorMessage) : $log->error_message }}
                                                </td>
                                                {{-- <td>
                                                    @php
                                                        $details = json_decode($log->details, true);
                                                    @endphp
                                                    @if (is_array($details))
                                                        <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($details, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $log->details }}
                                                    @endif
                                                </td> --}}
                                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <x-slot name="footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </x-slot>
                    </x-modal>
                @endif

            </div>
            <!-- Manage Admin Details -->
            @can('booking actions')
                <x-modal id="adminActions" title="Update Booking Fields" size="modal-lg">
                    <form id="update-booking-{{ $booking->id }}" method="POST" action="{{ route('admin.orders.booking.update', $booking) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row p-4 g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Update Status</label>
                                    <select class="form-select" name="status">
                                        @foreach ($booking->getStatuses() as $status)
                                            <option value="{{ $status }}" {{ $status === $booking->status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Update Booking PNR</label>
                                    <input name="order_id" type="text" class="form-control" value="{{ $booking->order_id }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer w-100 d-flex justify-content-between">
                            @can('delete bookings')
                                <button type="button" class="btn btn-danger delete-booking-btn" data-id="{{ $booking->id }}">
                                    Delete Booking
                                </button>
                            @endcan
                            <button type="submit" class="btn btn_primary">Update</button>
                        </div>
                    </form>
                    @can('delete bookings')
                        <form id="delete-booking-{{ $booking->id }}" method="POST" action="{{ route('admin.orders.booking.destroy', $booking) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                </x-modal>
            @endcan
            <!-- Detailed Overview Modal -->
            <x-modal id="detailedOverviewModal" title="Detailed Overview" size="modal-lg">
                <x-admin.show-xml-data :booking="$booking" />
                <x-slot name="footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </x-slot>
            </x-modal>
            <x-modal id="fareRulesModal" title="Fare Rules" size="modal-lg">
                <div class="modal-body">
                    @forelse ($booking->bookingItems as $item)
                        <div class="border p-2 mb-3 rounded">
                            <h4><strong>Passenger:</strong> {{ $item->passenger_code }}<br></h4>
                            @php
                                $services = json_decode($item->services, true);
                                $taxes = json_decode($item->taxes, true);
                                $totalTax = null;
                                if ($airline === 'flyjinnah' && is_array($taxes)) {
                                    $totalTax = array_sum(
                                        array_map(function ($tax) {
                                            return isset($tax['@attributes']['Amount']) ? (float) $tax['@attributes']['Amount'] : 0;
                                        }, $taxes),
                                    );
                                } elseif ($airline === 'pia' && is_array($taxes)) {
                                    $totalTax = array_sum(
                                        array_map(function ($tax) {
                                            return isset($tax['amount']) ? (float) $tax['amount'] : 0;
                                        }, $taxes),
                                    );
                                } elseif ($airline === 'airblue' && is_array($taxes)) {
                                    $totalTax = array_sum(
                                        array_map(function ($tax) {
                                            return isset($tax['Amount']) ? (float) $tax['Amount'] : 0;
                                        }, $taxes),
                                    );
                                }
                            @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Taxes</h6>
                                    @if ($taxes && is_array($taxes))
                                        @if ($airline === 'emirates' && isset($taxes['tax']) && is_array($taxes['tax']))
                                            <ul class="mb-0">
                                                @foreach ($taxes['tax'] as $tax)
                                                    <li>
                                                        <strong>{{ $tax['taxCode'] }}</strong>:
                                                        {{ $tax['description'] ?: 'N/A' }}
                                                        ({{ $tax['price']['amount'] }} {{ $tax['price']['code'] }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="mt-2">
                                                <strong>Base Amount:</strong> {{ $taxes['baseAmount']['amount'] }}
                                                {{ $taxes['baseAmount']['code'] }}<br>
                                                <strong>Total Tax:</strong> {{ $taxes['total']['amount'] }}
                                                {{ $taxes['total']['code'] }}
                                            </div>
                                        @elseif ($airline === 'flyjinnah')
                                            <ul class="mb-0">
                                                @foreach ($taxes as $tax)
                                                    <li>
                                                        <strong>{{ $tax['@attributes']['TaxCode'] }}</strong>:
                                                        {{ $tax['@attributes']['TaxName'] }}
                                                        ({{ $tax['@attributes']['Amount'] }}
                                                        {{ $tax['@attributes']['CurrencyCode'] }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($totalTax !== null)
                                                <div class="mt-2">
                                                    <strong>Total Tax:</strong> {{ number_format($totalTax, 2) }}
                                                    {{ $item->price_code }}
                                                </div>
                                            @endif
                                        @elseif ($airline === 'pia')
                                            <ul class="mb-0">
                                                @foreach ($taxes as $tax)
                                                    <li>
                                                        <strong>{{ $tax['tax_code'] }}</strong>:
                                                        {{ $tax['tax_code'] }} Tax
                                                        ({{ $tax['amount'] }} {{ $item->price_code }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($totalTax !== null)
                                                <div class="mt-2">
                                                    <strong>Total Tax:</strong> {{ number_format($totalTax, 2) }}
                                                    {{ $item->price_code }}
                                                </div>
                                            @endif
                                        @elseif ($airline === 'airblue')
                                            <ul class="mb-0">
                                                @foreach ($taxes as $tax)
                                                    <li>
                                                        <strong>{{ $tax['TaxCode'] ?? 'N/A' }}</strong>:
                                                        {{ $tax['TaxCode'] ?? 'N/A' }} Tax
                                                        ({{ $tax['Amount'] ?? '0' }}
                                                        {{ $tax['CurrencyCode'] ?? $item->price_code }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($totalTax !== null)
                                                <div class="mt-2">
                                                    <strong>Total Tax:</strong> {{ number_format($totalTax, 2) }}
                                                    {{ $item->price_code }}
                                                </div>
                                            @endif
                                        @else
                                            <p>Unsupported tax format for airline.</p>
                                        @endif
                                    @else
                                        <p>No taxes available.</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6>Services</h6>
                                    @if ($services && is_array($services))
                                        <ul class="mb-0">
                                            @foreach ($services as $service)
                                                @if ($airline === 'emirates' && !empty($service['details']['details']))
                                                    <li>{{ $service['details']['details'] }}</li>
                                                @elseif ($airline === 'flyjinnah')
                                                    <li>{{ $service }}</li>
                                                @elseif ($airline === 'pia')
                                                    <li>
                                                        {{ $service['service_definition_id'] ?: 'Flight Service' }}
                                                        (Segments: {{ implode(', ', $service['segment_refs']) }})
                                                    </li>
                                                @elseif ($airline === 'airblue')
                                                    <li>
                                                        <strong>{{ $service['title'] ?? 'N/A' }}</strong>
                                                        @if (!empty($service['description']))
                                                            <br><small
                                                                class="text-muted">{{ $service['description'] }}</small>
                                                        @endif
                                                        <br>
                                                        <small>
                                                            SSR Code: {{ $service['ssr_code'] ?? 'N/A' }} |
                                                            Item Code: {{ $service['item_code'] ?? 'N/A' }} |
                                                            Flight RPH: {{ $service['flight_rph'] ?? 'N/A' }}
                                                        </small>
                                                        <br>
                                                        <small>
                                                            Price:
                                                            {{ !empty($service['price']) && !empty($service['currency']) ? $service['currency'] . ' ' . number_format($service['price'], 2) : 'Free' }}
                                                            |
                                                            Status: {{ $service['status'] ?? 'N/A' }} |
                                                            Refundable:
                                                            {{ !empty($service['refundable']) ? ($service['refundable'] === 'true' ? 'Yes' : 'No') : 'N/A' }}
                                                        </small>
                                                        @if (!empty($service['expires']))
                                                            <br><small class="text-warning">Expires:
                                                                {{ \Carbon\Carbon::parse($service['expires'])->format('d M Y, H:i') }}</small>
                                                        @endif
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>No services available.</p>
                                    @endif
                                </div>
                            </div>
                            @if ($airline === 'flyjinnah' || $airline === 'airblue')
                                <div class="mt-2">
                                    <strong>Total Price:</strong> {{ number_format($item->price, 2) }}
                                    {{ $item->price_code }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p>No booking items found.</p>
                    @endforelse
                </div>
                <x-slot name="footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </x-slot>
            </x-modal>
            <!-- Approve Flight Modal -->
            <x-modal id="issueTicketsModal" title="Issue Tickets" size="modal-lg">
                <div class="modal-body issueTicketsBody">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn_primary issueTicketBtn" data-booking-id="{{ $booking->id }}" data-client-id="{{ $booking->client_id }}">Approve & Issue Tickets</button>
                </div>
            </x-modal>
        </div>
        {{-- Order Ref --}}
        <div class="card box">
            <div class="row">
                <div class="col-md-4 py-2"><strong>Order Ref:</strong> {{ $booking->id }}</div>
                <div class="col-md-4 py-2"><strong>Web Ref:</strong> {{ $booking->transaction_id }}</div>
                <div class="col-md-4 py-2"><strong>Order Status:</strong> <span class="badge bg-{{ $booking->status === 'issued' ? 'success' : 'danger' }}">TICKET{{ strtoupper($booking->status) }}</span></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4 py-2"><strong>Order Total:</strong> {{ $booking->total_price }}</div>
                <div class="col-md-4 py-2"><strong>Source/Affiliate:</strong> EDestinations Web</div>
                <div class="col-md-4 py-2"><strong>IP of the User:</strong> {{ $booking->client->ip ?? '' }}</div>
            </div>
        </div>
        {{-- Order Details --}}
        <div class="card box">
            <div class="section-title">Order Details</div>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Airline Ref</th>
                    {{-- <th>RBD Code</th>
                    <th>Cancellation Fee</th> --}}
                    <th>Flight No</th>
                    <th>Origin/Destination</th>
                    <th>Stops</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Duration</th>
                    <th>Class</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($booking->flights as $flight)
                        @foreach ($flight->segments as $segment)
                            <tr>
                                <td>{{ $flight->airline }}</td>
                                {{-- <td>NA</td> --}}
                                {{-- <td>LB</td> --}}
                                <td>{{ $segment->flight_number }}</td>
                                <td>{{ $segment->departure_code }} - {{ $segment->arrival_code }}</td>
                                <td>{{ $flight->is_connected ? '1' : '0' }}</td>
                                <td>{{ \Carbon\Carbon::parse($segment->departure_date)->format('d M Y H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($segment->arrival_date)->format('d M Y H:i') }}</td>
                                <td>{{ $segment->formatted_duration }}</td>
                                <td>{{ $flight->cabin_name_with_code }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Ticket Details --}}
        <div class="card box">
            <div class="section-title">
                Ticket Details
                {{-- <button class="btn btn_secondary_outline m-1 float-end" data-bs-toggle="modal" data-bs-target="#addTickets" type="button">Add Tickets</button> --}}
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Passenger Name</th>
                        <th>Date Of Birth</th>
                        <th>Type</th>
                        <th>Nationality</th>
                        <th>Passport Number / NIC</th>
                        <th>Expiry</th>
                        <th>Airline PNR</th>
                        <th>GDS PNR</th>
                        <th>Ticket Number</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                @php
                    $passengers = is_string($booking->passenger_details) ? (json_decode($booking->passenger_details, true) ?? []) : $booking->passenger_details;
                    $tickets = $booking->tickets->keyBy('passenger_reference');
                @endphp
                <tbody>
                    @forelse ($passengers as $passenger)
                        @php
                            $ticket = $tickets[$passenger['passenger_reference'] ?? ''] ?? null;
                        @endphp
                        <tr>
                            <td>{{ strtoupper($passenger['title']) }} {{ $passenger['given_name'] ?? '' }} {{ $passenger['surname'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($passenger['dob'] ?? '')->format('d/m/Y') }}</td>
                            <td>{{ $passenger['type'] ?? '' }}</td>
                            <td>{{ $passenger['nationality'] ?? 'N/A' }}</td>
                            <td>{{ $passenger['passport_no'] ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($passenger['passport_exp'] ?? '')->format('d/m/Y') }}</td>
                            <td>{{ $booking->airline_id ?? $booking->order_id }}</td>
                            <td>{{ $booking->flight_booking_id }}-{{ $loop->iteration }}</td>

                            @if ($ticket)
                                <td>{{ $ticket->ticket_no ?? '' }}</td>
                                <td>{{ number_format($ticket->price) }} {{ $ticket->price_code }}</td>
                                <td>
                                    <span class="badge p-1 bg-{{ $ticket->status === 'success' ? 'success' : 'danger' }}">
                                        {{ strtoupper($ticket->status ?? '') }}
                                    </span>
                                </td>
                            @else
                                @if ($booking->status === 'issued')
                                <form action="{{ route('admin.orders.ticket.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                    <input type="hidden" name="passenger_reference" value="{{ $passenger['passenger_reference'] ?? '' }}">
                                    <input type="hidden" name="type" value="{{ $passenger['type'] ?? '' }}">
                                    <td><input name="ticket_number" type="text" class="form-control m-0 p-0 px-2" required></td>
                                    <td colspan="2"><input type="submit" class="form-control m-0 p-0 px-2" value="Add ticket"></td>
                                </form>
                                @else
                                    <td colspan="3" class="text-center"><span class="text-danger">Not issued</span></td>
                                @endif
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted">No passengers found.</td></tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        {{-- <x-modal id="addTickets" title="Add Tickets" size="modal-lg">
            <form method="POST" action="{{ route('admin.orders.payment.store') }}">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <input type="hidden" name="client_id" value="{{ $booking->client_id }}">
                <input type="hidden" name="airline" value="{{ $booking->airline }}">
                <div class="modal-body">
                    <div class="row p-4 g-2">
                        <div class="col-md-4">
                            <label class="form-label">Transaction ID</label>
                            <input name="transaction_id" type="text" class="form-control" value="{{ old('transaction_id', $payment->transaction_id ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transaction ID</label>
                            <input name="transaction_id" type="text" class="form-control" value="{{ old('transaction_id', $payment->transaction_id ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn_primary">Add Payment</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </x-modal> --}}
        {{-- Product Overview --}}
        <div class="card box">
            <div class="section-title">Product Overview</div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Provider</th>
                        <th>Supplier</th>
                        <th>Base Fare</th>
                        <th>Tax</th>
                        <th>POS Rule</th>
                        <th>Displayed Price</th>
                        <th>PNR Expires At</th>
                        <th>Booking Expires At</th>
                        {{-- <th></th> --}}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $booking->is_oneway ? 'Oneway' : 'Return' }} Flight</td>
                        <td>{{ $booking->airline ?? '' }}Api</td>
                        <td>{{ $booking->airline ?? '' }}</td>
                        <td>{{ $booking->total_price ?? 0 }}</td>
                        <td>{{ $booking->tax ?? 0 }}</td>
                        <td>Affiliation POS Rule</td>
                        <td>{{ $booking->total_tax_price ?? 0 }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->payment_limit)->format('d M Y h:i A') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->ticket_limit)->format('d M Y h:i A') }}</td>
                        {{-- <td>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control m-0" placeholder="Update Booking Reference" aria-describedby="bookingRefBtn" aria-label="Booking Reference">
                                <button class="btn btn_secondary_outline" type="button" id="bookingRefBtn">Update</button>
                            </div>
                        </td> --}}
                    </tr>
                </tbody>
            </table>
        </div>
        {{-- Payments --}}
        <div class="card box">
            <div class="section-title">
                Payments
                @can('manage payment')
                    <button class="btn btn_secondary_outline m-1 float-end" data-bs-toggle="modal" data-bs-target="#addPayment" type="button">Add Payment</button>
                @endcan
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Created at</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Displayed Price</th>
                        <th>Merchant Fee</th>
                        <th>Service Fee</th>
                        <th>Status</th>
                        <th>Refund Status</th>
                        <th>Selling Fare</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($booking->payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('d M Y h:i a') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->transaction_id }}</td>
                            <td>{{ $payment->base_price_code }} {{ number_format($payment->base_price + $payment->tax, 2) }}</td>
                            <td>{{ $payment->merchant_fee }}%</td>
                            <td>{{ $payment->service_fee }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                            <td>{{ $payment->refund_status ?? 'N/A' }}</td>
                            <td>{{ $payment->base_price_code }} {{ number_format($payment->base_price, 2) }}</td>
                            <td>
                                @can('manage payment')
                                    <button class="btn btn_secondary_outline" data-bs-toggle="modal" data-bs-target="#editPayment{{ $payment->id }}">Adjust</button>
                                @endcan
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <x-modal id="editPayment{{ $payment->id }}" title="Edit Payment" size="modal-lg">
                            {{-- UPDATE form --}}
                            <form id="update-payment-{{ $payment->id }}" method="POST" action="{{ route('admin.orders.payment.update', $payment) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <x-admin.payment-form :payment="$payment" />
                                </div>
                                <div class="modal-footer w-100 d-flex justify-content-between">
                                    {{-- DELETE button triggers a separate (hidden) form via JS --}}
                                    <button type="button" class="btn btn-danger delete-payment-btn" data-id="{{ $payment->id }}">
                                        Delete
                                    </button>
                                    <button type="submit" class="btn btn_primary">Update</button>
                                </div>
                            </form>

                            {{-- DELETE form (separate, hidden) --}}
                            <form id="delete-payment-{{ $payment->id }}" method="POST" action="{{ route('admin.orders.payment.destroy', $payment) }}" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </x-modal>

                    @empty
                        <tr><td colspan="10" class="text-center">No Payment Found</td></tr>
                    @endforelse

                </tbody>
            </table>

            {{-- Add Payment Modal --}}
            <x-modal id="addPayment" title="Add Payment" size="modal-lg">
                <form method="POST" action="{{ route('admin.orders.payment.store') }}">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="client_id" value="{{ $booking->client_id }}">
                    <input type="hidden" name="airline" value="{{ $booking->airline }}">
                    <div class="modal-body">
                        <x-admin.payment-form />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn_primary">Add Payment</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </x-modal>
        </div>
        {{-- <div class="card box">
            <div class="section-title">Add Payment</div>
            <div class="row">
                <div class="col-3">
                    <input type="text" class="form-control" placeholder="payment amount">
                </div>
                <div class="col-3">
                    <select class="form-select">
                        <option selected>Payment Method</option>
                        <option value="creditcard">Credit Card</option>
                        <option value="banktransfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="col-3">
                    <input type="text" class="form-control" placeholder="payment description">
                </div>
                <div class="col-3">
                    <button class="btn btn_secondary_outline m-1" type="button">Add Payment</button>
                </div>
            </div>
        </div> --}}
    </div>
</div>
@endsection
@section('script')

<!-- Summernote CSS & JS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        let jsessionId, transactionId;
        let airline = "{{ $booking->airline }}".toLowerCase();
        $(document).on({
            ajaxStart: () => _loader('show'),
            ajaxStop: () => _loader('hide')
        });
        // Notes
        $('#note-editor').summernote({
            height: 150,
            placeholder: 'Write your note here...',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
        $('#addNoteBtn').on('click', function () {
            const agentId = $('#agentSelect').val();
            const note = $('#note-editor').val();
            const bookingId = {{ $booking->id }};
            const image = $('#noteImage')[0].files[0];

            if (!agentId) return _alert("Please select an agent first.", "warning");

            if (!note.trim()) return _alert("Note content cannot be empty.", "warning");

            let formData = new FormData();
            formData.append('agent_id', agentId);
            formData.append('booking_id', bookingId);
            formData.append('notes', note);
            if (image) formData.append('image', image);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route("admin.orders.log.add") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    _alert("Note added successfully.");
                    $('#note-editor').val('');
                    $('#noteImage').val('');
                },
                error: function (xhr) {
                    _alert("Something went wrong. Try again.", "error");
                }
            });
        });
        $('#showLogHistoryBtn').on('click', function () {
            let bookingId = {{ $booking->id }};

            $('#logHistoryContent').html('<div class="text-center py-4">Loading...</div>');
            $('#logHistoryModal').modal('show');

            $.ajax({
                url: "{{ route('admin.orders.booking.logs', ':id') }}".replace(':id', bookingId),
                method: 'GET',
                success: function (response) {
                    let logs = response.logs;
                    if (logs.length === 0) return $('#logHistoryContent').html('<div class="alert alert-info">No notes found.</div>');

                    let html = '<ul class="list-group">';
                    logs.forEach(log => {
                        html += `
                            <li class="list-group-item">
                                <strong>${log.user?.email || 'agent@edestination.com'}</strong>
                                <span class="text-muted float-end">${new Date(log.created_at).toLocaleString()}</span>
                                <p class="mb-1">${log.notes}</p>
                                ${log.image ? `<img src="/storage/${log.image}" alt="note image" class="img-thumbnail" style="max-width: 200px;">` : ''}
                            </li>
                        `;
                    });
                    html += '</ul>';

                    $('#logHistoryContent').html(html);
                },
                error: function () {
                    $('#logHistoryContent').html('<div class="alert alert-danger">Failed to load notes.</div>');
                }
            });
        });
        // Notes
        // payment
        $('.delete-payment-btn').on('click', async function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const ok = await _confirm('Are you sure you want to delete this payment?', true, 'warning', 'Yes, delete');
            if (ok) {
                document.getElementById('delete-payment-' + id).submit();
            }
        });
        // Issue Tickets (Fetch Details)
        $('.updatePriceBtn').on('click', async function (e) {
            let bookingId = $(this).data('booking-id');
            let clientId = $(this).data('client-id');
            let paymentExist = $(this).data('payment-exist');
            if (paymentExist == 0) return _alert('Please add some payments before issue tickets', 'warning')
            const ok = await _confirm('Are you sure you want to issue tickets for this flight?', true, 'warning', 'Yes');
            if (ok) {
                $.ajax({
                    url: "{{ route('fetch.flight.details') }}",
                    type: "POST",
                    data: { bookingId, clientId },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            // Build price comparison HTML
                            let comp = res.comparison;
                            if (airline === 'flyjinnah') {
                                jsessionId = res.data.jsessionId;
                                transactionId = res.data.transactionId;
                            }
                            let html = `
                                <div class="p-3">
                                    <h5>Price Comparison</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Old Price</th>
                                            <td>${comp.old_price} ${comp.old_price_code}</td>
                                        </tr>
                                        <tr>
                                            <th>New Price</th>
                                            <td>${comp.new_price} ${comp.new_price_code ?? ''}</td>
                                        </tr>
                                        <tr>
                                            <th>Difference</th>
                                            <td>
                                                ${comp.difference} 
                                                (${comp.difference_label})
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Percent Change</th>
                                            <td>${comp.percent_change}%</td>
                                        </tr>
                                    </table>
                                    <p class="text-muted">Approve the booking if you agree with the updated price.</p>
                                </div>
                            `;
                            $(".issueTicketsBody").html(html);
                            // Show modal
                            $("#issueTicketsModal").modal('show');
                        } else {
                            _alert(res.message || 'Error fetching booking details', 'error');
                        }
                    },
                    error: function (xhr) {
                        _alert(xhr.responseJSON.message || 'Error fetching booking details', 'error');
                    }
                });
            }
        });
        // Issue Tickets
        $('.issueTicketBtn').on('click', async function (e) {
            let bookingId = $(this).data('booking-id');
            let clientId = $(this).data('client-id');
            $.ajax({
                url: "{{ route('confirm.booking') }}",
                type: "POST",
                data: {bookingId, clientId, jsessionId, transactionId},
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                success: function (res) {
                    if (res.status === 'success') {
                        _alert('Booking approved and tickets issued successfully!', 'success');
                        $("#issueTicketsModal").modal('hide');
                        setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                    } else {
                        _alert(res.message || 'Error confirming booking', 'error');
                    }
                },
                error: function (xhr) {
                    _alert(xhr.responseJSON.message || 'Error confirming booking', 'error');
                    // Show Error Details Here in sweet alert
                }
            });
        });
        $('.cancelOrderBtn').on('click', async function (e) {
            let bookingId = $(this).data('booking-id');
            let clientId = $(this).data('client-id');
            let paymentExist = $(this).data('payment-exist');
            if (paymentExist == 0) return _alert('Please add some payments before issue tickets', 'warning')
            const ok = await _confirm('Are you sure you want to cancel this booking?', true, 'warning', 'Yes');
            if (ok) {
                $.ajax({
                    url: "{{ route('cancel.booking') }}",
                    type: "POST",
                    data: { bookingId, clientId },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            _alert('Booking canceled successfully!', 'success');
                            $("#cacelOrderDetailsModal").modal('hide');
                            setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                        } else {
                            _alert(res.message || 'Error in cancel booking', 'error');
                        }
                    },
                    error: function (xhr) {
                        let errorDetails = xhr.responseJSON.message || 'Error in cancel booking';
                        _alert(errorDetails, 'error');
                    }
                });
            } else {
                $("#cacelOrderDetailsModal").modal('hide');
            }
        });
        $('.delete-booking-btn').on('click', async function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const ok = await _confirm('Are you sure you want to delete this booking?', true, 'warning', 'Yes, delete');
            if (ok) {
                document.getElementById('delete-booking-' + id).submit();
            }
        });
    });
</script>

@endsection