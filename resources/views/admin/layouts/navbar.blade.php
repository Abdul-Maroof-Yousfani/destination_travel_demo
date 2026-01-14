<header class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ config('app.name') }}</a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        @can('view dashboard')
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.dashboard') }}">Home</a>
          </li>
        @endcan
        @can('manage bookings')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.orders.index') }}">Orders</a>
          </li>
        @endcan
        @can('manage agents')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.agents.index') }}">Agents</a>
          </li>
        @endcan
        @can('manage setting')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.settings') }}">Settings</a>
          </li>
        @endcan
        @can('manage roles')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.roles.index') }}">Roles & Permissions</a>
          </li>
        @endcan
        @can('manage users')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.clients.index') }}">Users</a>
          </li>
        @endcan
      </ul>

      <!-- Right Icons -->
      <ul class="navbar-nav ms-auto align-items-start align-items-lg-center gap-2">
        <!-- Notifications -->
        <li class="nav-item">
          <a class="nav-link position-relative" role="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotifications" aria-controls="offcanvasNotifications">
            <i class='bx bx-bell fs-4'></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
              <span class="visually-hidden">New alerts</span>
            </span>
          </a>
        </li>

        <!-- Messages -->
        <li class="nav-item">
          <a class="nav-link position-relative" role="button" >
            <i class='bx bx-message-square-dots fs-4'></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
              <span class="visually-hidden">New messages</span>
            </span>
          </a>
        </li>

        <!-- Profile Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class='bx bx-user-circle fs-4'></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item logoutBtn">Sign Out</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</header>

<!-- Offcanvas for Notifications -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotifications" aria-labelledby="offcanvasNotificationsLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasNotificationsLabel">Notifications</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <p>No new notifications.</p>
    <!-- You can loop through notifications here -->
  </div>
</div>
