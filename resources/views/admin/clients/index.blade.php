@extends('admin.layouts.master')
@section('title', 'Manage Clients')

@section('content')
<div class="d-flex flex-column justify-content-between h-100">
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Clients</h2>
        <button class="btn btn_primary" id="addClientBtn">Add Client</button>
        </div>

        <form method="GET" action="{{ route('admin.clients.index') }}" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Search name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Search email">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Phone</label>
                    <input type="number" name="phone" value="{{ request('phone') }}" class="form-control" placeholder="Search phone">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3 text-end d-flex gap-2">
                    <button type="submit" class="btn btn_primary w-100">Filter</button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Register date</th>
                        <th>Total bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                    <tr data-id="{{ $client->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->full_phone }}</td>
                        <td>{{ $client->created_at->format('d-m-y h:i a') }}</td>
                        <td>{{ $client->bookings->count() }}</td>
                        <td class="position-relative text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn_primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                <ul class="dropdown-menu show-on-top text-start">
                                    <li><a href="{{ route('admin.clients.show', $client->id) }}" class="dropdown-item"><i class="bx bx-detail me-2"></i>Details</a></li>
                                    <li><a href="#" class="dropdown-item editClientBtn"><i class="bx bx-pencil me-2"></i>Edit</a></li>
                                    <li>
                                        <a href="#" class="dropdown-item toggle-status {{ $client->is_active ? 'text-success' : 'text-secondary' }}">
                                            <i class="bx bx-user{{ $client->is_active ? '-check' : '' }} me-2"></i>{{ $client->is_active ? 'Active' : 'Inactive' }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No clients found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $clients->appends(request()->query())->links() }}
    </div>
</div>
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="clientForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="clientModalLabel">Add Client</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="client_id" name="client_id">

              <div class="row mb-3">
                  <div class="col-md-6">
                      <label>Name</label>
                      <input type="text" name="name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                      <label>Email</label>
                      <input type="email" name="email" class="form-control" required>
                  </div>
              </div>

              <div class="row mb-3">
                  <div class="col-md-3">
                      <label>Phone Code</label>
                      <input type="text" name="phone_code" class="form-control" value="+92" required>
                  </div>
                  <div class="col-md-3">
                      <label>Phone</label>
                      <input type="number" name="phone" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                      <label>Password</label>
                      <input type="password" name="password" class="form-control">
                  </div>
              </div>

              <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                  <label class="form-check-label" for="is_active">Active</label>
              </div>
              <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="accept_notification" id="accept_notification" value="1">
                  <label class="form-check-label" for="accept_notification">Accept Notifications</label>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    let modal = $('#clientModal');
    let form = $('#clientForm');

    $('#addClientBtn').on('click', function() {
        form[0].reset();
        $('#client_id').val('');
        modal.find('.modal-title').text('Add Client');
        modal.modal('show');
    });

    $('.editClientBtn').on('click', function() {
        let id = $(this).closest('tr').data('id');
        $.get("{{ route('admin.clients.edit', ':id') }}".replace(':id', id), function(data) {
            modal.find('.modal-title').text('Edit Client');
            $('#client_id').val(data.id);
            form.find('[name=name]').val(data.name);
            form.find('[name=email]').val(data.email);
            form.find('[name=phone_code]').val(data.phone_code);
            form.find('[name=phone]').val(data.phone);
            form.find('[name=is_active]').prop('checked', data.is_active);
            form.find('[name=accept_notification]').prop('checked', data.accept_notification);
            modal.modal('show');
        });
    });

    form.on('submit', function(e) {
        e.preventDefault();
        let id = $('#client_id').val();
        let url = id
            ? "{{ route('admin.clients.update', ':id') }}".replace(':id', id)
            : "{{ route('admin.clients.store') }}";
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: form.serialize(),
            success: function() {
                modal.modal('hide');
                location.reload();
            },
            error: function(err) {
                alert('Error: ' + err.responseJSON.message);
            }
        });
    });

    $('.toggle-status').on('click', function() {
        let id = $(this).closest('tr').data('id');
        $.post("{{ route('admin.clients.toggle-status', ':id') }}".replace(':id', id), { _token: '{{ csrf_token() }}' }, function() {
            location.reload();
        });
    });
});
</script>
@endsection
