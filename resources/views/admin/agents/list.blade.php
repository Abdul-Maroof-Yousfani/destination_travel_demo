@extends('admin/layouts/master')

@section('title', 'Order List')
@section('style')
{{-- style --}}
@endsection
@section('content')
<div class="d-flex flex-column justify-content-between h-100">
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manage Agents</h2>
            <a href="#" class="btn btn_primary" id="addAgentBtn">Add New Agent</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-active">
                    <tr>
                        <th>Fullname</th>
                        <th>Email</th>
                        <th>Roles</th>
                        {{-- <th>Permissions</th> --}}
                        <th>Register date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                        <tr>
                            <td>{{ $agent->name }}</td>
                            <td>{{ $agent->email }}</td>
                            <td>{{ $agent->getRoleNames()->join(', ') ?: '—' }}</td>
                            {{-- <td>{{ $agent->getAllPermissions()->pluck('name')->join(', ') ?: '—' }}</td> --}}
                            <td>{{ $agent->created_at_formatted  }}</td>
                            <td class="position-relative">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn_primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu show-on-top">
                                        <li>
                                            <a class="dropdown-item editPermissionBtn" href="#" data-id="{{ $agent->id }}" data-name="{{ $agent->name }}" data-roles="{{ $agent->getRoleNames()->join(',') }}">
                                                Assign Role
                                            </a>
                                        </li>
                                        {{-- <li><a class="dropdown-item" href="{{ route('admin.agents.edit.permission', $agent) }}">Edit Permissions</a></li> --}}
                                        <li>
                                            <a class="dropdown-item editAgentBtn" href="#" data-id="{{ $agent->id }}" data-name="{{ $agent->name }}" data-email="{{ $agent->email }}">
                                                Edit Details
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        <tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Agent Modal -->
        <div class="modal fade" id="agentModal" tabindex="-1" aria-labelledby="agentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="agentForm" method="POST" action="{{ route('admin.agents.store') }}">
                    @csrf
                    <input type="hidden" id="agent_id" name="agent_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="agentModalLabel">Add Agent</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="agent_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="agent_name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="agent_email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="agent_email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3" id="passwordField">
                                <label for="agent_password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="agent_password" name="password" value="{{ old('password') }}" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-danger d-none" id="deleteAgentBtn">Delete</button>
                                <a href="#" class="btn btn-secondary d-none" id="loginAgentBtn" target="_blank">Login</a>
                            </div>
                            <button type="submit" class="btn btn_primary" id="saveAgentBtn">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Assign Role Modal -->
        <div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="assignRoleForm" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignRoleModalLabel">Assign Roles</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <p class="fw-bold mb-2">Agent:</p>
                                <p id="agentName" class="mb-0 text-muted"></p>
                            </div>

                            <div class="mb-3">
                                <h5 class="mb-3">Roles</h5>
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="role_{{ $role->id }}" name="roles[]" value="{{ $role->name }}">
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    {{ ucfirst($role->name) }}
                                                </label>
                                            </div>
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
        $(document).ready(function () {
            const modal = new bootstrap.Modal(document.getElementById('agentModal'));
            // Add new agent
            $('#addAgentBtn').click(function () {
                $('#agentModalLabel').text('Add New Agent');
                $('#agentForm').attr('action', '{{ route("admin.agents.store") }}');
                $('#agentForm')[0].reset();
                $('#agent_id').val('');
                $('#deleteAgentBtn, #loginAgentBtn').addClass('d-none');

                $('#passwordField').show();
                $('#agent_password').attr('required', true);

                modal.show();
            });

            // Edit agent
            $('.editAgentBtn').click(function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const email = $(this).data('email');

                $('#agentModalLabel').text('Edit Agent');
                $('#agentForm').attr('action', '{{ route("admin.agents.update", ":id") }}'.replace(':id', id));
                $('#agent_name').val(name);
                $('#agent_email').val(email);
                $('#agent_id').val(id);

                $('#passwordField').hide();
                $('#agent_password').removeAttr('required').val('');

                $('#deleteAgentBtn').removeClass('d-none').off().click(function () {
                    if (confirm('Are you sure you want to delete this agent?')) {
                        window.location.href = '{{ route("admin.agents.destroy", ":id") }}'.replace(':id', id);
                    }
                });

                $('#loginAgentBtn').removeClass('d-none').attr(
                    'href', '{{ route("admin.agents.login", ":id") }}'.replace(':id', id)
                );
                modal.show();
            });

            
            const assignModal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
            // Open "Assign Roles" modal
            $('.editPermissionBtn').click(function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const roles = $(this).data('roles') ? $(this).data('roles').split(',') : [];

                $('#agentName').text(name);
                $('#assignRoleForm').attr('action', '{{ route("admin.agents.update.permission", ":id") }}'.replace(':id', id));

                // Uncheck all, then check current roles
                $('input[name="roles[]"]').prop('checked', false);
                roles.forEach(role => {
                    $('input[value="' + role.trim() + '"]').prop('checked', true);
                });

                assignModal.show();
            });
        });
    </script>
@endsection