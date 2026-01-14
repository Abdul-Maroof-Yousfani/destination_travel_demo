@extends('admin.layouts.master')

@section('title', 'Settings')

@section('style')
<style>
.available-date a {
    background-color: var(--primary-color) !important;
    color: #fff !important;
}
.unavailable-date a {
    color: #ccc !important;
}
</style>
@endsection

@section('content')
<div class="row">
    <h2 class="fw-bold mb-4">Settings</h2>
</div>
<div class="row justify-content-between h-100">

    @can('download logs')
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg">
                <div class="card-header bg_primary text-white">
                    <h4 class="mb-0">Download Airline Logs</h4>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Select Airline</label>
                            <select id="airlineSelect" class="form-select" required>
                                <option value="">-- Select Airline --</option>
                                @foreach($airlines as $airline)
                                    <option value="{{ $airline }}">{{ ucfirst($airline) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Available Log Dates</label>
                            <input type="text" id="logCalendar" class="form-control" placeholder="Select date" readonly>
                        </div>
                    </div>

                    <div id="logInfo" class="mt-4" style="display:none;">
                        <h5>Files for <span id="selectedDate"></span></h5>
                        <div id="fileButtons" class="mt-3 d-flex gap-3"></div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
    @can('manage airports')
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg">
                <div class="card-header bg_primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manage Airports</h4>
                    <button class="btn btn-light btn-sm" id="addAirportBtn">+ Add Airport</button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Search Airport</label>
                        <select id="airportSearch" class="form-select" data-placeholder="Search airport name or code"></select>
                    </div>

                    <table class="table table-striped table-hover" id="airportTable">
                        <thead class="table-light">
                            <tr>
                                <th>Order By</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Country</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody><tr><td colspan="5" class="text-center text-muted">Loading...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    @endcan

    <!-- Modal -->
    <div class="modal fade" id="airportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg_primary text-white">
                    <h5 class="modal-title" id="modalTitle">Add Airport</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="airportForm">
                        @csrf
                        <input type="hidden" id="airport_id">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Order By</label>
                                <input type="number" id="order_by" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" id="code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input type="text" id="country" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Time Zone</label>
                                <input type="text" id="time_zone" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City Code</label>
                                <input type="text" id="city_code" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Airport Type</label>
                                <select name="is_local" id="is_local" class="form-control">
                                    <option selected value="0">International</option>
                                    <option value="1">Domestic</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" id="city" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">State</label>
                                <input type="text" id="state" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">County</label>
                                <input type="text" id="county" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success" id="saveAirportBtn">Save</button>
                </div>
            </div>
        </div>
    </div>




</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function(){
    // ------------------------ Airline Management ------------------------
    let availableDates = {};

    $("#logCalendar").datepicker({
        dateFormat: "yy-mm-dd",
        beforeShowDay: function(date) {
            const d = $.datepicker.formatDate('yy-mm-dd', date);
            const available = availableDates.hasOwnProperty(d);
            return [available, available ? "available-date" : "unavailable-date"];
        },
        onSelect: function(dateText) {
            $('#selectedDate').text(dateText);
            const files = availableDates[dateText] || {};
            const airline = $('#airlineSelect').val();
            let html = '';

            // Airline log
            if (files.log) {
                html += `
                    <a href="{{ route('admin.logs.download') }}?airline=${encodeURIComponent(airline)}&file=${encodeURIComponent(files.log)}"
                        class="btn btn-success">🧾 Download Airline Log</a>`;
            } else {
                html += `<button class="btn btn-secondary" disabled>🧾 Airline Log Not Found</button>`;
            }

            // Booking log
            if (files.booking) {
                html += `
                    <a href="{{ route('admin.logs.download') }}?airline=${encodeURIComponent(airline)}&file=${encodeURIComponent(files.booking)}"
                        class="btn btn-info">📘 Download Booking Log</a>`;
            } else {
                html += `<button class="btn btn-secondary" disabled>📘 Booking Log Not Found</button>`;
            }

            $('#fileButtons').html(html);
            $('#logInfo').show();
        }
    });

    $('#airlineSelect').on('change', function(){
        const airline = $(this).val();
        $('#logInfo').hide();

        if (!airline) {
            availableDates = {};
            $("#logCalendar").datepicker("refresh");
            return;
        }

        $.ajax({
            url: "{{ route('admin.logs.dates') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", airline: airline },
            success: function(response){
                availableDates = response.availableDates;
                $("#logCalendar").datepicker("refresh");
            },
            error: function(){
                _alert('Error loading available dates.', 'error');
            }
        });
    });




    // ------------------------ Airport Management ------------------------
    const modal = new bootstrap.Modal(document.getElementById('airportModal'));
    const tableBody = $('#airportTable tbody');
    let editMode = false;
    let selectedId = null;

    loadAirports();

    function loadAirports() {
        $.get('{{ route("admin.airports.list") }}', function(data){
            if (!data.length) {
                tableBody.html('<tr><td colspan="5" class="text-center text-muted">No airports found</td></tr>');
                return;
            }
            let html = '';
            data.forEach(a => {
                html += `
                <tr>
                    <td>${a.order_by}</td>
                    <td>${a.name}</td>
                    <td>${a.code}</td>
                    <td>${a.country || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning editBtn" data-id="${a.id}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${a.id}">Delete</button>
                    </td>
                </tr>`;
            });
            tableBody.html(html);
        });
    }

    $('#airportSearch').select2({
        theme: 'classic',
        placeholder: $('#airportSearch').data('placeholder'),
        ajax: {
            url: '{{ route("airport") }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ term: params.term }),
            processResults: data => ({ results: data.results }),
        }
    }).on('select2:select', function (e) {
        const code = e.params.data.id;

        $.get('{{ route("admin.airports.single") }}', { code }, function (airport) {
            if (airport && airport.id) {
                openModal(airport);
                $('#airportSearch').val(null).trigger('change');
            } else {
                _alert('Airport not found in local DB', 'error');
            }
        }).fail(() => {
            _alert('Error fetching airport details.', 'error');
        });
    });


    $('#addAirportBtn').on('click', function(){
        $('#modalTitle').text('Add Airport');
        $('#airportForm')[0].reset();
        $('#airport_id').val('');
        editMode = false;
        modal.show();
    });

    $(document).on('click', '.editBtn', function(){
        const id = $(this).data('id');
        $.get('{{ route("admin.airports.show", ":id") }}'.replace(':id', id), function(airport){
            if (airport && airport.id) setTimeout(() => openModal(airport), 100);
            else _alert('Airport not found.', 'error');
        }).fail(() => _alert('Error fetching airport details.', 'error'));
    });

    function openModal(airport) {
        $('#airportForm')[0].reset();
        $('#airport_id').val('');

        $('#modalTitle').text('Edit Airport');
        $('#airport_id').val(airport.id);
        $('#name').val(airport.name);
        $('#order_by').val(airport.order_by);
        $('#code').val(airport.code);
        $('#country').val(airport.country);
        $('#time_zone').val(airport.time_zone);
        $('#city_code').val(airport.city_code);
        $('#is_local').val(airport.is_local ? '1' : '0'); 
        $('#city').val(airport.city);
        $('#state').val(airport.state);
        $('#county').val(airport.county);
        editMode = true;
        modal.hide();
        setTimeout(() => modal.show(), 150);
    }
    $('#airportModal').on('hidden.bs.modal', function(){
        // Reset modal state completely when hidden
        $('#airportForm')[0].reset();
        $('#airport_id').val('');
        editMode = false;
    });


    $('#saveAirportBtn').on('click', function(){
        const id = $('#airport_id').val();
        const data = {
            _token: '{{ csrf_token() }}',
            name: $('#name').val(),
            order_by: $('#order_by').val(),
            code: $('#code').val(),
            country: $('#country').val(),
            time_zone: $('#time_zone').val(),
            city_code: $('#city_code').val(),
            is_local: $('#is_local').val() === '1' ? 1 : 0,
            city: $('#city').val(),
            state: $('#state').val(),
            county: $('#county').val()
        };

        const url = editMode
            ? '{{ route("admin.airports.update", ":id") }}'.replace(':id', id)
            : '{{ route("admin.airports.store") }}';
        const method = editMode ? 'PUT' : 'POST';

        $.ajax({ url, type: method, data })
            .done(() => { modal.hide(); loadAirports(); _alert('Airport saved successfully.'); })
            .fail(err => _alert('Error: ' + err.responseJSON.message, 'error'));
    });

    $(document).on('click', '.deleteBtn', function(){
        if (!confirm('Delete this airport?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: '{{ route("admin.airports.destroy", ":id") }}'.replace(':id', id),
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(){
                _alert('Airport deleted successfully.');
                loadAirports();
            }
        });
    });
});
</script>
@endsection
