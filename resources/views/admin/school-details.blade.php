@extends('layouts.master')

@section('content')

<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <a href="{{ route('web.schools.all') }}" class="btn btn-sm btn-secondary mr-2">
            <i class="fa fa-arrow-left"></i> Back to Schools
        </a>
        <h4 class="page-title">{{ $school->name }}</h4>
        <span class="text-muted mt-1">Complete school analysis and management</span>
    </div>
    <div class="page-rightheader">
        <div class="btn-list">
            <button type="button" class="btn btn-success" onclick="updateStatus('{{ $school->id }}', 'active')" {{ $school->status === 'active' ? 'disabled' : '' }}>
                <i class="fa fa-check"></i> Activate
            </button>
            <button type="button" class="btn btn-warning" onclick="updateStatus('{{ $school->id }}', 'inactive')" {{ $school->status === 'inactive' ? 'disabled' : '' }}>
                <i class="fa fa-pause"></i> Deactivate
            </button>
            <button type="button" class="btn btn-danger" onclick="updateStatus('{{ $school->id }}', 'suspended')" {{ $school->status === 'suspended' ? 'disabled' : '' }}>
                <i class="fa fa-ban"></i> Suspend
            </button>
        </div>
    </div>
</div>
<!--End Page header-->

<!-- School Status Banner -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">
                            School Status:
                            <span class="badge badge-{{ $school->status === 'active' ? 'success' : ($school->status === 'inactive' ? 'warning' : 'danger') }}">
                                {{ ucfirst($school->status) }}
                            </span>
                        </h5>
                        <p class="text-muted mb-0">Registered on: {{ $school->created_at->format('Y-m-d H:i:s') }}</p>
                        <p class="text-muted mb-0">Last updated: {{ $school->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overview Cards -->
<div class="row">
    <div class="col-sm-6 col-md-3">
        <div class="card bg-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Users</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $users->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-users fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card bg-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Inventory Items</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $totalItems }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-box fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card bg-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Suppliers</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $suppliers->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-truck fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card bg-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Storage Locations</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $locations->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-map-marker fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for Different Sections -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-pills card-header-pills" id="schoolTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-toggle="pill" href="#info" role="tab">
                            <i class="fa fa-info-circle"></i> School Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" data-toggle="pill" href="#users" role="tab">
                            <i class="fa fa-users"></i> Users ({{ $users->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inventory-tab" data-toggle="pill" href="#inventory" role="tab">
                            <i class="fa fa-box"></i> Inventory ({{ $totalItems }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="suppliers-tab" data-toggle="pill" href="#suppliers" role="tab">
                            <i class="fa fa-truck"></i> Suppliers ({{ $suppliers->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="locations-tab" data-toggle="pill" href="#locations" role="tab">
                            <i class="fa fa-map-marker"></i> Storage Locations ({{ $locations->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="categories-tab" data-toggle="pill" href="#categories" role="tab">
                            <i class="fa fa-tags"></i> Categories ({{ $categories->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="departments-tab" data-toggle="pill" href="#departments" role="tab">
                            <i class="fa fa-building"></i> Departments
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="schoolTabsContent">

                    <!-- School Info Tab -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tr>
                                                <th width="150">School Name:</th>
                                                <td>{{ $school->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Centre Number:</th>
                                                <td>{{ $school->centre_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge badge-{{ $school->status === 'active' ? 'success' : ($school->status === 'inactive' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($school->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Created At:</th>
                                                <td>{{ $school->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Updated At:</th>
                                                <td>{{ $school->updated_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Location Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tr>
                                                <th width="150">District:</th>
                                                <td>{{ $school->district ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>County:</th>
                                                <td>{{ $school->county ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sub-county:</th>
                                                <td>{{ $school->subcounty ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Parish:</th>
                                                <td>{{ $school->parish ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Village:</th>
                                                <td>{{ $school->village ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">Admin Contact Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tr>
                                                <th width="150">Admin Name:</th>
                                                <td>{{ $school->admin_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Admin Email:</th>
                                                <td>{{ $school->admin_email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Admin Phone:</th>
                                                <td>{{ $school->admin_phone ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0"><i class="fa fa-building"></i> Department Access</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($departments as $dept)
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-{{ $dept['color'] }}">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <i class="fa {{ $dept['icon'] }} fa-2x text-{{ $dept['color'] }}"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $dept['name'] }}</h6>
                                                                <small class="text-muted">{{ $dept['description'] }}</small>
                                                                <div class="mt-2">
                                                                    @if($dept['access'] === 'unlocked')
                                                                        <span class="badge badge-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                                                    @else
                                                                        <span class="badge badge-danger"><i class="fa fa-lock"></i> Locked</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        @if(count($lockedDepartmentsList) > 0)
                                        <div class="alert alert-warning mt-3">
                                            <h6 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Locked Departments</h6>
                                            <p class="mb-0">The following departments are currently locked for this school and require activation:</p>
                                            <ul class="mb-0">
                                                @foreach($lockedDepartmentsList as $locked)
                                                <li>{{ $locked }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif

                                        @if(count($unlockedDepartments) > 0)
                                        <div class="alert alert-success mt-3">
                                            <h6 class="alert-heading"><i class="fa fa-check-circle"></i> Active Departments</h6>
                                            <p class="mb-0">This school has access to the following departments:</p>
                                            <ul class="mb-0">
                                                @foreach($unlockedDepartments as $unlocked)
                                                <li>{{ $unlocked }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        <!-- Lab Access Codes Section -->
                        @if($labAccessCodes->count() > 0)
                        <div class="mb-4">
                            <h5 class="card-title"><i class="fa fa-key"></i> Lab Access Codes (User Permissions)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Access Code</th>
                                            <th>Status</th>
                                            <th>Last Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($labAccessCodes as $accessCode)
                                        <tr>
                                            <td>{{ $accessCode->user_name }}</td>
                                            <td>{{ $accessCode->email ?? 'N/A' }}</td>
                                            <td>{{ $accessCode->department }}</td>
                                            <td><code>{{ $accessCode->access_code }}</code></td>
                                            <td>
                                                @if($accessCode->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $accessCode->last_used_at ? $accessCode->last_used_at->format('Y-m-d H:i') : 'Never' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>School Admin</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->firstName }} {{ $user->lastName }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role_id == 1)
                                                <span class="badge badge-primary">Admin</span>
                                            @elseif($user->role)
                                                {{ is_object($user->role) ? $user->role->name : $user->role }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->role_id == 1)
                                                Admin
                                            @elseif($user->department)
                                                {{ $user->department }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->is_school_admin)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->last_login ? (is_string($user->last_login) ? $user->last_login : $user->last_login->format('Y-m-d H:i')) : 'Never' }}</td>
                                        <td>
                                            @if($user->is_active ?? true)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="showResetPasswordModal('{{ $user->id }}', '{{ $user->firstName }} {{ $user->lastName }}')" title="Reset Password">
                                                    <i class="fa fa-key"></i> Reset
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="forcePasswordReset('{{ $user->id }}')" title="Force Reset on Next Login">
                                                    <i class="fa fa-refresh"></i> Force Reset
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No users found for this school</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Inventory Tab -->
                    <div class="tab-pane fade" id="inventory" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Min Qty</th>
                                        <th>Unit</th>
                                        <th>Unit Cost</th>
                                        <th>Total Value</th>
                                        <th>Supplier</th>
                                        <th>Location</th>
                                        <th>Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr class="{{ $item->quantity <= $item->min_quantity ? 'table-warning' : '' }}">
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category ? $item->category->name : 'N/A' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->min_quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ number_format($item->unit_cost, 2) }}</td>
                                        <td>{{ number_format($item->total_value, 2) }}</td>
                                        <td>{{ $item->supplier ? $item->supplier->name : 'N/A' }}</td>
                                        <td>{{ $item->location ? $item->location->name : 'N/A' }}</td>
                                        <td>{{ $item->expiry_date ? (is_string($item->expiry_date) ? $item->expiry_date : $item->expiry_date->format('Y-m-d')) : 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No inventory items found for this school</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Suppliers Tab -->
                    <div class="tab-pane fade" id="suppliers" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact Person</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                                        <td>{{ $supplier->phone }}</td>
                                        <td>{{ $supplier->email }}</td>
                                        <td>{{ $supplier->address ?? 'N/A' }}</td>
                                        <td>
                                            @if($supplier->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No suppliers found for this school</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Storage Locations Tab -->
                    <div class="tab-pane fade" id="locations" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Lab Type</th>
                                        <th>Capacity</th>
                                        <th>Current Usage</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locations as $location)
                                    <tr>
                                        <td>{{ $location->name }}</td>
                                        <td>{{ $location->type ?? 'N/A' }}</td>
                                        <td>{{ $location->lab_type ?? 'N/A' }}</td>
                                        <td>{{ $location->capacity ?? 'N/A' }}</td>
                                        <td>{{ $location->current_usage ?? 0 }}</td>
                                        <td>{{ $location->creator ? $location->creator->firstName : 'N/A' }} {{ $location->creator ? $location->creator->lastName : '' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No storage locations found for this school</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="categories" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Items Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->items ? $category->items->count() : 0 }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No categories found for this school</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Departments Tab -->
                    <div class="tab-pane fade" id="departments" role="tabpanel">
                        <div class="row">
                            @foreach($departments as $dept)
                            <div class="col-md-4 mb-3">
                                <div class="card border-{{ $dept['color'] }}">
                                    <div class="card-header bg-{{ $dept['color'] }} text-white">
                                        <h6 class="mb-0"><i class="fa {{ $dept['icon'] }}"></i> {{ $dept['name'] }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">{{ $dept['description'] }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge badge-{{ $dept['color'] }}">{{ $dept['items_count'] }} Items</span>
                                            @if($dept['access'] === 'unlocked')
                                                <span class="badge badge-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fa fa-lock"></i> Locked</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fa fa-check-circle"></i> Active Departments</h6>
                                    </div>
                                    <div class="card-body">
                                        @if(count($unlockedDepartments) > 0)
                                            <ul class="list-unstyled mb-0">
                                                @foreach($unlockedDepartments as $unlocked)
                                                <li><i class="fa fa-check text-success mr-2"></i>{{ $unlocked }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted mb-0">No active departments</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fa fa-lock"></i> Locked Departments</h6>
                                    </div>
                                    <div class="card-body">
                                        @if(count($lockedDepartmentsList) > 0)
                                            <ul class="list-unstyled mb-0">
                                                @foreach($lockedDepartmentsList as $locked)
                                                <li><i class="fa fa-times text-danger mr-2"></i>{{ $locked }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-success mb-0"><i class="fa fa-check-circle"></i> All departments unlocked!</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for inventory
    $('#inventory-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']]
    });
});

// Update school status with beautiful popup
function updateStatus(schoolId, status) {
    // Determine icon and color based on action
    let title = '';
    let text = '';
    let icon = '';
    let confirmButtonText = '';
    let confirmButtonColor = '';

    if (status === 'active') {
        title = 'Activate School?';
        text = 'This will activate the school account and all its users. They will be able to log in and access the system.';
        icon = 'question';
        confirmButtonText = 'Yes, Activate!';
        confirmButtonColor = '#28a745';
    } else if (status === 'inactive') {
        title = 'Deactivate School?';
        text = 'This will deactivate the school account and all its users. They will not be able to log in until reactivated.';
        icon = 'warning';
        confirmButtonText = 'Yes, Deactivate!';
        confirmButtonColor = '#ffc107';
        text += '\n\nThis action can be undone later.';
    } else if (status === 'suspended') {
        title = 'Suspend School?';
        text = '⚠️ This will immediately suspend the school account and all its users. This is usually done for policy violations or unpaid dues.';
        icon = 'danger';
        confirmButtonText = 'Yes, Suspend!';
        confirmButtonColor = '#dc3545';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-lg mr-2',
            cancelButton: 'btn btn-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Updating school status...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/schools/' + schoolId + '/' + status,
                type: 'POST',
                xhrFields: {
                    withCredentials: true
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function(response) {
                    // Show success animation
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: true,
                        confirmButtonColor: '#28a745',
                        customClass: {
                            confirmButton: 'btn btn-success btn-lg'
                        }
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred while updating status',
                        confirmButtonColor: '#dc3545',
                        customClass: {
                            confirmButton: 'btn btn-danger btn-lg'
                        }
                    });
                }
            });
        }
    });
}

// Show reset password modal
function showResetPasswordModal(userId, userName) {
    $('#resetUserId').val(userId);
    $('#resetUserName').text(userName);
    $('#resetPasswordModal').modal('show');
}

// Reset password
function resetPassword() {
    var newPassword = $('#new_password').val();
    var confirmPassword = $('#new_password_confirmation').val();

    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Passwords do not match'
        });
        return;
    }

    if (newPassword.length < 8) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Password must be at least 8 characters'
        });
        return;
    }

    $.ajax({
        url: '/users/reset-password',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            user_id: $('#resetUserId').val(),
            new_password: newPassword,
            new_password_confirmation: confirmPassword
        },
        success: function(response) {
            $('#resetPasswordModal').modal('hide');
            $('#new_password').val('');
            $('#new_password_confirmation').val('');
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                timer: 3000,
                showConfirmButton: false
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'An error occurred while resetting password'
            });
        }
    });
}

// Force password reset on next login
function forcePasswordReset(userId) {
    Swal.fire({
        title: 'Force Password Reset?',
        text: 'This user will be required to reset their password on their next login.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, force reset',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/users/force-password-reset',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred'
                    });
                }
            });
        }
    });
}
</script>

<!-- Password Reset Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Resetting password for: <strong id="resetUserName"></strong></p>
                <form id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="user_id" id="resetUserId">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                        <small class="text-muted">Password must be at least 8 characters</small>
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="resetPassword()">Reset Password</button>
            </div>
        </div>
    </div>
</div>

@endpush
