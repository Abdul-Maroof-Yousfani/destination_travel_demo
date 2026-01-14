@extends('home/layouts/master')
@section('title', 'Traveler Profile')
@section('style')
<style>
    :root{--accent:#0ea5a4;/* teal */
    --accent-2:#0b74d1;/* blue */
    --muted:#6c757d;--card-bg:#ffffff;--page-bg:#f4f7fa;}
    body{background:var(--page-bg);font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;}
    .profile-header{background:linear-gradient(90deg,rgba(14,165,164,0.06),rgba(11,116,209,0.03));border-radius:10px;padding:22px}
    .avatar{width:86px;height:86px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#e9f7f6,#fff);box-shadow:0 4px 14px rgba(11,116,209,0.06); overflow: hidden;}
    .avatar img {width: 100%; height: 100%; object-fit: cover;}
    .stat-card{border-radius:10px;box-shadow:0 6px 18px rgba(15,20,30,0.04);background:var(--card-bg);}
    .btn-teal{background:var(--accent);color:#fff;border-radius:10px;}
    .btn-teal:hover{background:#089091; color: white;}
    .tag{font-size:12px;padding:6px 8px;border-radius:6px;}
    .table thead th{font-size:13px;color:var(--muted)}
    .small-muted{color:var(--muted);font-size:13px}
    .card-section{border-radius:8px;box-shadow:0 6px 18px rgba(15,20,30,0.03);}

    /* Modal Tweaks */
    .modal-content { border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .modal-header { border-bottom: 1px solid #eee; padding: 20px 24px; }
    .modal-title { font-weight: 600; color: #333; }
    .modal-body { padding: 24px; }
    .modal-footer { border-top: 1px solid #eee; padding: 16px 24px; background: #f9f9f9; border-radius: 0 0 12px 12px; }
    .form-label { font-size: 13px; font-weight: 500; color: var(--muted); margin-bottom: 6px; }
    .form-control { padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; }
    .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,164,0.1); }
    .form-select { padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; }
    .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,164,0.1); }

    /* responsive tweaks */
    @media (max-width:767px){.avatar{width:72px;height:72px}
    }
</style>
@endsection
@section('content')
  <div class="container py-4">
    <!-- Cover Image + Profile card -->
    <div class="mb-3 position-relative">
      <img src="https://images.unsplash.com/photo-1502920514313-52581002a659?q=80&w=1200" class="w-100 rounded" style="height:180px; object-fit:cover; border-radius:12px;" />
      <div class="rounded-circle border border-3 border-white position-absolute avatar" style="width:110px; height:110px; bottom:-20px; left:20px; background:#fff;">
         @if($client->profile_path)
            <img src="{{ asset('storage/' . $client->profile_path) }}" alt="Profile">
         @else
            <i class="fa fa-user-circle fa-3x text-muted"></i>
         @endif
      </div>
    </div>

    <!-- Profile card -->
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="p-3 profile-header card-section">
          <div class="row align-items-center">
             <div class="col-md-9 offset-md-3"> 
               <div class="d-flex justify-content-between align-items-start ms-5 ps-3">
                  <div class="flex-grow-1">
                    <h4 class="mb-1">{{ $client->name }} <span class="badge bg-light text-dark ms-2">Member</span></h4>
                    <div class="small-muted">{{ $client->email }} &nbsp; • &nbsp; {{ $client->full_phone }}</div>
                    <div class="mt-2">
                       @if($client->city)
                        <span class="tag bg-light text-info"><i class="fa fa-map-marker-alt me-1"></i> {{ $client->city }}, {{ $client->country_name }}</span>
                       @endif
                    </div>
                  </div>
                  <div class="">
                    <button class="btn btn-teal" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fa fa-edit me-2"></i> Edit Profile</button>
                  </div>
               </div>
             </div>
          </div>


          <!-- optional quick info row -->
          <div class="row mt-4 ms-2">
             <div class="col-12"><hr class="text-muted opacity-25"></div>
            <div class="col-md-4 small-muted">IP Address<br><strong class="text-dark">{{ $client->ip ?? 'N/A' }}</strong></div>
            <div class="col-md-4 small-muted">Login Provider<br><strong class="text-dark">{{ $client->login_provider ?? 'local' }}</strong></div>
            <div class="col-md-4 small-muted">Joined<br><strong class="text-dark">{{ $client->created_at->format('M d, Y') }}</strong></div>
          </div>
        </div>

        <!-- Recent bookings -->
        <div class="mt-3 card-section p-3">
          <h5 class="mb-3">Recent Bookings</h5>
          <div class="table-responsive profile-destinations">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Order ID</th>
                  <th>Airline</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Price</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($client->bookings as $booking)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $booking->order_id }}</strong></td>
                   <td>
                      <div class="d-flex align-items-center gap-2">
                          @php
                             $airlineName = $booking->airline ?? $booking->airline_code;
                             $logoPath = 'assets/images/logos/' . strtolower($airlineName) . '.png';
                          @endphp
                          <img src="{{ asset($logoPath) }}" alt="Logo" style="width: 24px; height: auto;">
                          <span>{{ $airlineName }}</span>
                      </div>
                  </td>
                  <td>{{ $booking->type }}</td>
                  <td>
                      @php
                          $badgeClass = match($booking->status) {
                              'confirmed', 'issued' => 'bg-success',
                              'pending' => 'bg-warning text-dark',
                              'cancelled', 'cancel' => 'bg-danger',
                              'expired' => 'bg-secondary',
                              default => 'bg-primary'
                          };
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ ucfirst($booking->status) }}</span>
                  </td>
                  <td>{{ $booking->total_price }}</td>
                  <td>{{ $booking->created_at->format('M d, Y') }}</td>
                  <td>
                      <a href="{{ route('view.booking.details', ['order_id' => $booking->order_id]) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a>
                  </td>
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
        <!-- Passengers list -->
        <div class="mt-3 card-section p-3">
          <h5 class="mb-3">Passengers</h5>
          <div class="table-responsive profile-destinations">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Nationality</th>
                  <th>Passport / CNIC</th>
                  <th>Expiry</th>
                  <th>DOB</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($client->passengers as $passenger)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $passenger->title }} {{ $passenger->given_name }} {{ $passenger->surname }}</td>
                  <td>{{ $passenger->type }}</td>
                  <td>{{ $passenger->nationality }}</td>
                  <td>{{ $passenger->passport_no }}</td>
                  <td>{{ $passenger->passport_exp ? \Carbon\Carbon::parse($passenger->passport_exp)->format('M d, Y') : '-' }}</td>
                  <td>{{ \Carbon\Carbon::parse($passenger->dob)->format('M d, Y') }}</td>
                   <td>
                      <button class="btn btn-sm btn-outline-secondary" onclick="editPassenger({{ json_encode($passenger) }})"><i class="fa fa-pencil"></i></button>
                  </td>
                </tr>
                @empty
                 <tr>
                    <td colspan="8" class="text-center text-muted">No passengers found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Right column: stats and actions -->
      <div class="col-lg-4">
        <div class="d-grid gap-3">
          <div class="p-3 stat-card text-center">
            <div class="small-muted">Total Bookings</div>
            <h3 class="my-1">{{ $client->bookings->count() }}</h3>
            <div class="small-muted">Lifetime</div>
          </div>

          <div class="p-3 mt-3 stat-card text-center">
            <div class="small-muted">Total Passengers</div>
            <h3 class="my-1">{{ $client->passengers->count() }}</h3>
            <div class="small-muted">Saved profiles</div>
          </div>

          <div class="p-3 card-section">
            <h6>Actions</h6>
            <div class="d-grid gap-2 mt-2">
              <button class="btn btn-outline-danger logoutBtn"><i class="fa fa-sign-out-alt me-2"></i> Logout</button>
            </div>
          </div>

          <div class="p-3 card-section small-muted">
            <h6 class="mb-2">Notes</h6>
            <p class="mb-0" style="font-size:13px">
                Welcome to your dashboard. Here you can track all your flight bookings and manage your passenger details.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Profile Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('update.client', $client->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-4 text-center">
                    <div class="position-relative d-inline-block">
                        <div class="avatar" style="width: 100px; height: 100px; margin: 0 auto;">
                           @if($client->profile_path)
                              <img src="{{ asset('storage/' . $client->profile_path) }}" id="preview-img" alt="Profile">
                           @else
                              <i class="fa fa-user-circle fa-3x text-muted" id="preview-icon"></i>
                              <img src="" id="preview-img" alt="Profile" style="display:none;">
                           @endif
                        </div>
                        <label for="profile_path" class="position-absolute bottom-0 end-0 bg-white shadow-sm p-2 rounded-circle border cursor-pointer" style="cursor: pointer;">
                            <i class="fa fa-camera text-muted"></i>
                            <input type="file" id="profile_path" name="profile_path" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </label>
                    </div>
                    <div class="small text-muted mt-2">Tap icon to change photo</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $client->name }}" placeholder="e.g. John Doe">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone" value="{{ $client->phone }}" placeholder="e.g. 321 1234567">
                    </div>
                    <div class="col-md-12">
                         <hr class="my-2 opacity-25">
                         <label class="form-label">New Password <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal px-4">Save Changes</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Passenger Modal -->
  <div class="modal fade" id="editPassengerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Passenger Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editPassengerForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-2">
                         <label class="form-label">Title</label>
                         <select class="form-select" name="title" id="p_title">
                             <option value="MR">MR</option>
                             <option value="MS">MS</option>
                             <option value="MRS">MRS</option>
                             <option value="MSTR">MSTR</option>
                         </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Given Name</label>
                        <input type="text" class="form-control" name="given_name" id="p_given_name" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Surname</label>
                        <input type="text" class="form-control" name="surname" id="p_surname" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <input type="text" class="form-control" name="nationality" id="p_nationality" required maxlength="2" placeholder="e.g. PK">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" id="p_dob" required>
                    </div>
                    <div class="col-md-4">
                         <! -- spacer -->
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Passport Number</label>
                        <input type="text" class="form-control" name="passport_no" id="p_passport_no">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Passport Expiry</label>
                        <input type="date" class="form-control" name="passport_exp" id="p_passport_exp">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-teal px-4">Update Passenger</button>
            </div>
        </form>
      </div>
    </div>
  </div>

    @section('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       function previewImage(input) {
           if (input.files && input.files[0]) {
               var reader = new FileReader();
               reader.onload = function(e) {
                   document.getElementById('preview-img').src = e.target.result;
                   document.getElementById('preview-img').style.display = 'block';
                   var icon = document.getElementById('preview-icon');
                   if(icon) icon.style.display = 'none';
               }
               reader.readAsDataURL(input.files[0]);
           }
       }

       function editPassenger(passenger) {
           // Populate form fields
           document.getElementById('p_title').value = passenger.title;
           document.getElementById('p_given_name').value = passenger.given_name;
           document.getElementById('p_surname').value = passenger.surname;
           document.getElementById('p_nationality').value = passenger.nationality;
           document.getElementById('p_passport_no').value = passenger.passport_no;

           // Date formatting for input type=date (YYYY-MM-DD)
           if(passenger.dob) {
               document.getElementById('p_dob').value = new Date(passenger.dob).toISOString().split('T')[0];
           }
           if(passenger.passport_exp) {
               document.getElementById('p_passport_exp').value = new Date(passenger.passport_exp).toISOString().split('T')[0];
           } else {
               document.getElementById('p_passport_exp').value = '';
           }

           // Set form action
           var form = document.getElementById('editPassengerForm');
           form.action = "{{ route('passenger.update', ':id') }}".replace(':id', passenger.id);

           // Show modal
           var myModal = new bootstrap.Modal(document.getElementById('editPassengerModal'));
           myModal.show();
       }
    </script>
    @endsection
@endsection