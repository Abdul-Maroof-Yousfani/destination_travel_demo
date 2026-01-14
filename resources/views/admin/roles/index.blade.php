@extends('admin/layouts/master')

@section('title', 'Roles Management')
@section('style')
{{-- Add any role-specific CSS --}}
@endsection

@section('content')
<div class="d-flex flex-column justify-content-between h-100">
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Roles & Permissions</h2>
            <a href="#" class="btn btn_primary" id="addRoleBtn">Add New Role</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-active">
                    <tr>
                        <th>Role Name</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->permissions->pluck('name')->join(', ') ?: '—' }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn_primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu show-on-top">
                                    <li>
                                        <a class="dropdown-item editRoleBtn"
                                           href="#"
                                           data-id="{{ $role->id }}"
                                           data-name="{{ $role->name }}"
                                           data-permissions="{{ $role->permissions->pluck('name')->join(',') }}">
                                           Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item deleteRoleBtn" href="#" data-id="{{ $role->id }}">
                                            Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="role_id" name="role_id">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="roleModalLabel">Add New Role</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <input type="text" class="form-control" id="role_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permissions</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($permissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn_primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        const modal = new bootstrap.Modal($('#roleModal')[0]);

        $('#addRoleBtn').on('click', function () {
            $('#roleModalLabel').text('Add New Role');
            $('#roleForm').attr('action', '{{ route("admin.roles.store") }}');
            $('#formMethod').val('POST');
            $('#roleForm')[0].reset();
            modal.show();
        });

        $('.editRoleBtn').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const perms = $(this).data('permissions').split(',');

            $('#roleModalLabel').text('Edit Role');
            $('#roleForm').attr('action', '/admin/roles/' + id);
            $('#formMethod').val('PUT');
            $('#role_name').val(name);

            $('input[name="permissions[]"]').prop('checked', false);
            perms.forEach(p => {
                $('input[value="' + p.trim() + '"]').prop('checked', true);
            });

            modal.show();
        });

        $('.deleteRoleBtn').on('click', function () {
            const id = $(this).data('id');
            if (confirm('Are you sure you want to delete this role?')) {
                $.ajax({
                    url: '/admin/roles/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: () => location.reload()
                });
            }
        });
    });
</script>
@endsection
