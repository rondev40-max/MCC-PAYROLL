<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Management & Monitoring</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>

    {{-- ** LEAFLET MAP CSS (kapalit ng Google Maps) ** --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        /* Base Styles - Hindi ko ginalaw */
        :root{
            --brand:#3498db;
            --brand-600:#2980b9;
            --muted:#f5f6f8;
            --card:#ffffff;
            --text:#111111;
        }
        .night-mode {
            --brand:#222831;
            --brand-600:#393e46;
            --muted:#18191a;
            --card:#c5c8ce;
            --text:#ffffff;
        }
        body{ background:var(--muted); color:var(--text); transition:background .3s, color .3s; }
        .app{ min-height:100vh; }
        .sidebar{
            background: linear-gradient(180deg, var(--brand), var(--brand-600));
            color:#fff; width:260px; position:sticky; top:0; height:100vh; padding:1.25rem 1rem;
            box-shadow: 0 10px 25px rgba(52,152,219,.25);
        }
        .sidebar .nav-link{
            color:#e3f2fd; border-radius:.75rem; padding:.6rem .8rem; font-weight:500;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active{
            background:#fff; color:var(--brand-600);
        }
        .sidebar .section-title{ font-size:.8rem; text-transform:uppercase; opacity:.85; margin:.9rem .5rem .3rem; }
        .content{ flex:1; }
        .topbar{ background:var(--card); border-bottom:1px solid #f0f0f0; padding:.75rem 1rem; position:sticky; top:0; z-index:1020; }
        .logout-icon{ font-size:1.4rem; color:var(--brand); }
        .logout-icon:hover{ color:#85c1e9; }
        .card-soft{ background:var(--card); border:1px solid #f0f0f0; border-radius:1rem; box-shadow:0 8px 20px rgba(0,0,0,.03); }

        @media (max-width: 992px){
            .sidebar{ position:fixed; transform:translateX(-100%); transition:.25s; z-index:1030; }
            .sidebar.show{ transform:none; }
            .content{ margin-left:0!important; }
        }

        /* Page-specific styles - Hindi ko ginalaw */
        .status-dot { height: 12px; width: 12px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #6c757d; }
        .status-badge-active { background-color: #28a745; color: white; }
        .status-badge-suspended { background-color: #dc3545; color: white; }
        .status-badge-disabled { background-color: #6c757d; color: white; }
        .badge-role { font-size: 0.85em; padding: 0.4em 0.7em; }
        .badge-super-admin { background-color: #dc3545; color: white; }
        .badge-admin { background-color: #007bff; color: white; }
        .badge-attendance-checker { background-color: #ffc107; color: #212529; }
        .table-monitoring { font-size: 0.85em; max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .table-monitoring-sm { max-width: 60px; }

        /* ** NEW STYLES FOR MAP MODAL ** */
        #map {
            height: 100%; /* Important: Dapat may height ang map container */
            min-height: 400px;
            border-radius: 0.5rem;
        }
        .modal-map-container {
            display: flex;
            height: 80vh; /* Set a fixed height for the modal body */
        }
        .map-details-sidebar {
            width: 300px;
            min-width: 300px;
            padding: 1.5rem;
            background-color: var(--muted);
            border-left: 1px solid #f0f0f0;
            overflow-y: auto;
        }
        .map-wrapper {
            flex-grow: 1;
            padding: 1rem;
        }
        .detail-item h6 {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.2rem;
        }
        .detail-item p {
            font-weight: 500;
            margin-bottom: 1rem;
            word-break: break-all;
        }
        /* Make map responsive for small screens */
        @media (max-width: 992px) {
            .modal-map-container {
                flex-direction: column;
                height: auto;
            }
            .map-details-sidebar {
                width: 100%;
                min-width: unset;
                border-left: none;
                border-top: 1px solid #f0f0f0;
            }
        }
    </style>
</head>
<body>
    <div class="app d-flex">
        
        {{-- SIDEBAR - Hindi ko ginalaw --}}
        
        <div class="content w-100">
            {{-- TOPBAR - Hindi ko ginalaw --}}
            <div class="topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary d-lg-none me-3" id="mobileMenuBtn" aria-label="Open menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0">User & Access Management</h5>
                </div>
                
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="user-welcome d-none d-md-block">
                        <h6 class="mb-0 text-muted">Welcome, <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span></h6>
                    </div>
                    {{-- Pinalitan ang logout link para mas gumana ang Swal.fire --}}
                    <a href="{{ route('logout') }}" 
                        onclick="event.preventDefault();"
                        id="logoutBtn">
                        <i class="bi bi-box-arrow-right logout-icon"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf 
                    </form>
                </div>
            </div>

            <div class="container-fluid py-4">
                <div class="card-soft p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">System Users (Admins & Checkers)</h5>
                        <div class="d-flex gap-2">
                            {{-- **NEW**: Ito ang nawawalang Activity Log button --}}
                            <a href="{{ route('admin.activity-log') }}" class="btn btn-info">
                                <i class="bi bi-clock-history"></i> Activity Log
                            </a>
                            @if(Auth::user()->role === 'super_admin')
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill"></i> Add New User
                            </a>
                            @endif
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Online Status</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-nowrap">Account Status</th> 
                                    <th class="text-nowrap">Last Activity</th>
                                    <th class="table-monitoring">Last Login</th>
                                    <th class="table-monitoring">IP Address</th>
                                    <th class="table-monitoring-sm">Session ID</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allUsers as $user)
                                    @php
                                        // Gamitin ang isOnline() method na nilagay mo sa User Model
                                        $isOnline = method_exists($user, 'isOnline') ? $user->isOnline() : false; 
                                        $statusClass = [
                                            'active' => 'status-badge-active',
                                            'suspended' => 'status-badge-suspended',
                                            'disabled' => 'status-badge-disabled',
                                            // Ensure status is handled gracefully if not set
                                        ][strtolower($user->status ?? 'disabled')] ?? 'badge-secondary';

                                        // Para sa role badge
                                        $roleClass = [
                                            'super_admin' => 'badge-super-admin',
                                            'admin' => 'badge-admin',
                                            'attendance_checker' => 'badge-attendance-checker',
                                        ][$user->role ?? ''] ?? 'badge-secondary';

                                        // Geolocation fallback (Kung walang geo data, gumamit ng isang common location)
                                        $ipAddress = $user->last_login_ip ?? 'N/A';
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="status-dot {{ $isOnline ? 'status-online' : 'status-offline' }}"></span>
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge badge-role {{ $roleClass }}">{{ ucwords(str_replace('_', ' ', $user->role ?? 'N/A')) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ ucwords($user->status ?? 'N/A') }}</span>
                                        </td>
                                        
                                        <td>
                                            {{ $isOnline ? 'Now' : ($user->last_seen_at ? \Carbon\Carbon::parse($user->last_seen_at)->diffForHumans() : 'Never') }}
                                        </td>
                                        
                                        <td class="table-monitoring" title="{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i:s') : 'N/A' }}">
                                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i') : 'N/A' }}
                                        </td>
                                        <td class="table-monitoring" title="{{ $ipAddress }}">
                                            {{ $ipAddress }}
                                        </td>
                                        {{-- Panatilihin ang pagpaikli sa table --}}
                                        <td class="table-monitoring-sm" title="{{ $user->session_id ?? 'N/A' }}">
                                            {{ $user->session_id ? substr($user->session_id, 0, 8) . '...' : 'N/A' }}
                                        </td>
                                        
                                        <td class="text-center text-nowrap">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    {{-- BUTTON: VIEW LOCATION --}}
                                                    <li class="d-grid px-2 mb-1">
                                                        <button type="button" class="btn btn-sm btn-success btn-view-location" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#locationModal"
                                                            data-user-ip="{{ $ipAddress }}"
                                                            data-user-name="{{ $user->name }}"
                                                            data-user-email="{{ $user->email }}"
                                                            data-user-activity="{{ $isOnline ? 'Now' : ($user->last_seen_at ? \Carbon\Carbon::parse($user->last_seen_at)->diffForHumans() : 'Never') }}"
                                                            data-user-login="{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i') : 'N/A' }}"
                                                            data-user-lat="{{ $user->latitude ?? '' }}"
                                                            data-user-lon="{{ $user->longitude ?? '' }}"
                                                            data-user-accuracy="{{ $user->location_accuracy ?? '' }}"
                                                            {{-- FIXED: Inalis ang substr() dito para buo ang Session ID sa modal --}}
                                                            data-user-session="{{ $user->session_id ?? 'N/A' }}">
                                                            <i class="bi bi-geo-alt-fill"></i> View Location
                                                        </button>
                                                    </li>
                                                    
                                                    @if(Auth::user()->role === 'super_admin' && Auth::user()->id !== $user->id)
                                                        <li><hr class="dropdown-divider"></li>
                                                        {{-- BUTTON PARA SA STATUS MANAGEMENT --}}
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-change-status" 
                                                                data-bs-toggle="modal" data-bs-target="#statusModal" 
                                                                data-user-id="{{ $user->id }}" 
                                                                data-user-name="{{ $user->name }}"
                                                                data-current-status="{{ $user->status }}">
                                                                <i class="bi bi-toggles"></i> Change Status
                                                            </button>
                                                        </li>
                                                        
                                                        {{-- BUTTON PARA SA DELETE --}}
                                                        <li>
                                                            <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="dropdown-item text-danger btn-delete" title="Delete User">
                                                                    <i class="bi bi-trash3-fill"></i> Delete User
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MODAL PARA SA STATUS UPDATE - Hindi ko ginalaw --}}
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Change User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStatusForm" method="POST" action="">
                    @csrf
                    @method('PUT') 
                    <div class="modal-body">
                        <p>Updating status for user: <strong id="userName"></strong></p>
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Select New Status</label>
                            <select class="form-select" id="statusSelect" name="status" required>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ** MODAL PARA SA MAP VIEW ** (UPDATE: Tinanggal ang IP Address Display) --}}
    <div class="modal fade modal-xl" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="locationModalLabel"><i class="bi bi-geo-alt"></i> User Location & Monitoring</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="modal-map-container">
                        {{-- MAP CONTAINER --}}
                        <div class="map-wrapper">
                            <div id="map">
                            </div>
                        </div>

                        {{-- RIGHT SIDEBAR FOR USER DETAILS --}}
                        <div class="map-details-sidebar">
                             <div class="detail-item">
                                <h6>Name</h6>
                                <h5 class="mb-3 text-primary" id="mapUserName"></h5>
                            </div>
                            
                            
                            <div class="detail-item">
                                <h6>Email</h6>
                                <p id="detailEmail"></p>
                            </div>
                            <div class="detail-item">
                                <h6>Last Activity</h6>
                                <p id="detailLastActivity"></p>
                            </div>
                            <div class="detail-item">
                                <h6>Last Login</h6>
                                <p id="detailLastLogin"></p>
                            </div>
                            
                            {{-- <div class="detail-item">
                                <h6>IP Address (Estimated Location)</h6>
                                <p id="detailIPAddress"></p>
                            </div> --}}

                            {{-- ** NEW: Latitude ** --}}
                            <div class="detail-item">
                                <h6>Latitude</h6>
                                <p id="detailLatitude">N/A</p>
                            </div>
                            
                            {{-- ** NEW: Longitude ** --}}
                            <div class="detail-item">
                                <h6>Longitude</h6>
                                <p id="detailLongitude">N/A</p>
                            </div>
                            
                            {{-- ** NEW: Accuracy Display ** --}}
                            <div class="detail-item">
                                <h6>Accuracy</h6>
                                <p id="detailAccuracy">N/A</p>
                            </div>

                            <div class="detail-item">
                                <h6>Session ID</h6>
                                <p id="detailSessionID"></p>
                            </div>
                            
                            <hr>
                            <p class="text-muted small">Location is based on precise device coordinates (if available) or is unavailable.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ** LEAFLET MAP JAVASCRIPT ** (kapalit ng Google Maps script) --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        // Global Map variable for Leaflet
        let map = null; // Set to null initially
        let marker = null;
        // Fallback Location
        const fallbackLocation = [12.8797, 121.7740]; // Center of PH
        const fallbackZoom = 6;
        const defaultZoom = 13;

        // ** TANGGAL/UPDATE: Inalis ang isPrivateIP function (dahil hindi na gagamitin ang IP geocoding) **
        // function isPrivateIP(ip) { /* ... original code ... */ return false; }


        /**
         * Function to initialize the Leaflet Map
         * Tinitiyak na tama ang rendering kapag nasa loob ng modal.
         */
        function initLeafletMap() {
            // Tanggalin ang dating map instance kung meron man
            if (map) {
                map.remove(); 
                map = null;
                marker = null;
            }

            // I-set ang view sa default center at zoom level
            map = L.map('map').setView(fallbackLocation, fallbackZoom); 

            // Add OpenStreetMap tiles (ito ang map design/data source)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18,
            }).addTo(map);

            // Initial marker (invisible)
            marker = L.marker(fallbackLocation, { opacity: 0 }).addTo(map); 
            console.log("Leaflet Map Initialized.");
        }

        /**
         * Function to update the Latitude and Longitude display in the modal sidebar.
         */
        function updateModalCoordinates(lat, lon) {
            // Tiyakin na nag-r-render ng N/A kapag walang valid na coordinate
            const displayLat = (typeof lat === 'number' && !isNaN(lat)) ? lat.toFixed(4) : 'N/A';
            const displayLon = (typeof lon === 'number' && !isNaN(lon)) ? lon.toFixed(4) : 'N/A';
            
            document.getElementById('detailLatitude').textContent = displayLat;
            document.getElementById('detailLongitude').textContent = displayLon;
        }

        /**
         * Fallback function when precise coordinates are not available.
         * @param {string} userName - Name of the user for the popup.
         */
        function applyFallbackMap(userName) { // ** UPDATE: Inalis ang 'ipAddress' parameter **
            map.setView(fallbackLocation, fallbackZoom);
            const [lat, lon] = fallbackLocation;

            // Gumamit ng ibang icon (hal. Red) para sa fallback
            const fallbackIcon = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            // ** UPDATE: Inalis ang IP sa popup **
            marker.setIcon(fallbackIcon).setLatLng(fallbackLocation).setOpacity(1).bindPopup(`<h6>${userName}</h6><p>Location: Not available</p>`).openPopup();
            
            // Update Modal Coordinates
            updateModalCoordinates('N/A', 'N/A'); // Reset Lat/Long to N/A
        }

        /**
         * Function to set the map view based on precise Latitude and Longitude.
         * @param {number} lat - Latitude.
         * @param {number} lon - Longitude.
         * @param {string} userName - Name of the user for the popup.
         */
        function setMapToCoordinates(lat, lon, userName) { // ** UPDATE: Inalis ang 'ipAddress' parameter **
            const location = [lat, lon];
            
            // Normal Leaflet icon
            const defaultIcon = L.Icon.Default; 
            
            map.setView(location, defaultZoom); // Zoom closer
            
            // Update marker position and make it visible with default icon
            marker.setIcon(new defaultIcon()).setLatLng(location).setOpacity(1); 
            
            // Add a popup/tooltip to the marker
            // ** UPDATE: Inalis ang IP sa popup **
            marker.bindPopup(`<h6>${userName}</h6><p>Location: Precise (from device)</p>`).openPopup();
            
            // Update Modal Coordinates
            updateModalCoordinates(lat, lon); 

            // I-invalidate size para sigurado ang rendering
            if (map) {
                map.invalidateSize();
            }
        }


        // ** TANGGAL/UPDATE: Inalis ang geocodeIPAndSetMap function (dahil hindi na gagamitin ang IP geocoding) **
        /* function geocodeIPAndSetMap(ipAddress, userName) {
            // ... original code for ip-api.com fetch ...
        } */

        document.addEventListener('DOMContentLoaded', function () {
            
            // Auto-dismiss success alert (Logic not modified)
            window.setTimeout(function() {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    new bootstrap.Alert(alert).close();
                }
            }, 5000);

            // Delete confirmation (Logic not modified)
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this! The user will be permanently deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Logout confirmation (Logic not modified)
            const logoutBtn = document.getElementById('logoutBtn');
            if(logoutBtn) {
                logoutBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirm Logout',
                        text: "Are you sure you want to log out?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3498db',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Log Out'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            }

            // DevTools detection (Logic not modified)
            devtools.detect(function(status){
                if(status){
                    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999; display: flex; justify-content: center; align-items: center; font-family: sans-serif; font-size: 24px;">Developer Tools Detected. Access Denied.</div>';
                }
            });
            
            // --- LOGIC PARA SA STATUS MODAL --- (Logic not modified)
            const statusModal = document.getElementById('statusModal');
            if (statusModal) {
                statusModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');
                    const currentStatus = button.getAttribute('data-current-status');
                    
                    const modalTitle = statusModal.querySelector('#userName');
                    const statusSelect = statusModal.querySelector('#statusSelect');
                    const updateStatusForm = statusModal.querySelector('#updateStatusForm');
                    
                    modalTitle.textContent = userName;
                    // Tiyaking tama ang route mo:
                    updateStatusForm.action = `/admin/users/${userId}/status`; 
                    statusSelect.value = currentStatus; 
                });
            }
            
            // --- LOGIC PARA SA LOCATION MODAL at LEAFLET MAP ---
            const locationModal = document.getElementById('locationModal');
            if (locationModal) {
                
                // Event: Bago i-display ang modal
                locationModal.addEventListener('show.bs.modal', event => {
                    // I-reset muna ang Lat/Long sa N/A habang naglo-load
                    document.getElementById('detailLatitude').textContent = 'N/A';
                    document.getElementById('detailLongitude').textContent = 'N/A';
                    
                    // Initialise the Leaflet map (Dito dapat i-initialize)
                    initLeafletMap();
                });

                // Event: Pagkatapos i-display ang modal
                locationModal.addEventListener('shown.bs.modal', event => { 
                    
                    const button = event.relatedTarget;
                    
                    // Kukunin ang user data
                    const userName = button.getAttribute('data-user-name');
                    // ** TANGGAL/UPDATE: Inalis ang pagkuha ng userIP **
                    // const userIP = button.getAttribute('data-user-ip'); 
                    const userEmail = button.getAttribute('data-user-email');
                    const userActivity = button.getAttribute('data-user-activity');
                    const userLogin = button.getAttribute('data-user-login');
                    const userLat = parseFloat(button.getAttribute('data-user-lat'));
                    const userLon = parseFloat(button.getAttribute('data-user-lon'));
                    const userAccuracy = parseFloat(button.getAttribute('data-user-accuracy'));
                    // FIXED: Walang substr() dito, kukunin ang buong session ID
                    const userSession = button.getAttribute('data-user-session');

                    // Ipapakita ang user details
                    document.getElementById('mapUserName').textContent = userName;
                    document.getElementById('detailEmail').textContent = userEmail;
                    document.getElementById('detailLastActivity').textContent = userActivity;
                    document.getElementById('detailLastLogin').textContent = userLogin;
                    // ** TANGGAL/UPDATE: Inalis ang display ng IP **
                    // document.getElementById('detailIPAddress').textContent = userIP;
                    document.getElementById('detailSessionID').textContent = userSession;

                    // I-display ang accuracy
                    if (!isNaN(userAccuracy)) {
                        // Kung ang accuracy ay > 1000 meters, ipakita as kilometers
                        const accuracyText = userAccuracy > 1000 ? `${(userAccuracy / 1000).toFixed(2)} km` : `${Math.round(userAccuracy)} meters`;
                        document.getElementById('detailAccuracy').textContent = `Within ${accuracyText} (Device-provided)`;
                    } else {
                        document.getElementById('detailAccuracy').textContent = 'Not available';
                    }
                    
                    // Maglo-load ng location sa mapa
                    if (map) {
                        // ** LOGIC UPDATE **
                        // Tanging tumpak na coordinates lang ang gagamitin.
                        if (!isNaN(userLat) && !isNaN(userLon)) {
                            // ** UPDATE: Inalis ang userIP sa function call **
                            setMapToCoordinates(userLat, userLon, userName); 
                        } else {
                            // Kung walang coordinates (N/A), diretsong gamitin ang fallback function.
                            // ** UPDATE: Inalis ang userIP sa function call **
                            applyFallbackMap(userName); 
                        }
                    } else {
                        console.error("Leaflet Map object not initialized after modal show event.");
                    }
                    
                });

                // Event: Isasara na ang modal
                locationModal.addEventListener('hidden.bs.modal', event => {
                    if (map) {
                        map.remove(); // Tanggalin ang mapa sa DOM
                        map = null;
                        marker = null;
                    } 
                });
            }
        });
    </script>
</body>
</html>