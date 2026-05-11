@extends('layouts.master')

@section('content')

<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title">Schools Management</h4>
        <span class="text-muted mt-1">Manage all registered schools, their accounts, and data</span>
    </div>
    <div class="page-rightheader">
        <div class="btn-list">
            <a href="{{ route('web.schools.all') }}" class="btn btn-primary">
                <i class="fe fe-refresh-cw"></i> Refresh
            </a>
        </div>
    </div>
</div>
<!--End Page header-->

<!-- Stats Row -->
<div class="row">
    <div class="col-sm-6 col-md-6 col-xl-3">
        <div class="card bg-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Total Schools</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $schools->total() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-school fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-6 col-xl-3">
        <div class="card bg-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Active Schools</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $schools->where('status', 'active')->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-check-circle fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-6 col-xl-3">
        <div class="card bg-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Inactive Schools</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $schools->where('status', 'inactive')->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-pause-circle fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-6 col-xl-3">
        <div class="card bg-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Suspended Schools</h6>
                        <h2 class="text-white m-0 font-weight-bold">{{ $schools->where('status', 'suspended')->count() }}</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-ban fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schools Table Row -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Registered Schools</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="schools-table" class="table table-bordered text-nowrap mb-0 w-100">
                        <thead>
                            <tr>
                                <th>School Name</th>
                                <th>Centre Number</th>
                                <th>Location</th>
                                <th>Admin Contact</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th>Departments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                            <tr data-id="{{ $school->id }}">
                                <td>
                                    <strong>{{ $school->name }}</strong>
                                </td>
                                <td>{{ $school->centre_number }}</td>
                                <td>
                                    {{ $school->district ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $school->county ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $school->admin_name ?? 'N/A' }}<br>
                                    <small>{{ $school->admin_email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $school->users_count ?? 0 }} Users</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $school->status === 'active' ? 'success' : ($school->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($school->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $schoolData = $school->data ?? [];
                                        $unlockedDepts = $schoolData['unlocked_departments'] ?? ['Laboratory'];
                                        $lockedCount = 4 - (count($unlockedDepts) - 1);
                                    @endphp
                                    <span class="badge badge-success">{{ count($unlockedDepts) }} Active</span>
                                    @if($lockedCount > 0)
                                    <span class="badge badge-secondary">{{ $lockedCount }} Locked</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('web.schools.details', $school->id) }}" class="btn btn-sm btn-primary" title="View Full Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item" onclick="updateStatus('{{ $school->id }}', 'active')" {{ $school->status === 'active' ? 'disabled' : '' }}>
                                                <i class="fa fa-check"></i> Activate
                                            </button>
                                            <button class="dropdown-item" onclick="updateStatus('{{ $school->id }}', 'inactive')" {{ $school->status === 'inactive' ? 'disabled' : '' }}>
                                                <i class="fa fa-pause"></i> Deactivate
                                            </button>
                                            <button class="dropdown-item" onclick="updateStatus('{{ $school->id }}', 'suspended')" {{ $school->status === 'suspended' ? 'disabled' : '' }}>
                                                <i class="fa fa-ban"></i> Suspend
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item" onclick="viewSchoolDetails('{{ $school->id }}')">
                                                <i class="fa fa-bar-chart"></i> Quick Analysis
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $schools->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- School Details Modal -->
<div class="modal fade" id="schoolDetailsModal" tabindex="-1" role="dialog" aria-labelledby="schoolDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="schoolDetailsModalLabel">School Quick Analysis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="schoolDetailsContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Loading school details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#schools-table').DataTable({
        responsive: true,
        paging: false,
        info: false,
        searching: true,
        ordering: true
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

// View school details in modal
function viewSchoolDetails(schoolId) {
    $('#schoolDetailsModal').modal('show');

    $.ajax({
        url: '/api/schools/' + schoolId + '/details',
        type: 'GET',
        success: function(response) {
            const school = response.school;
            const users = response.users;
            const inventory = response.inventory;
            const suppliers = response.suppliers;
            const storageLocations = response.storage_locations;
            const orders = response.orders;
            const departments = response.departments;
            const unlockedDepartments = response.unlocked_departments;
            const lockedDepartments = response.locked_departments;

            // Build department list HTML
            let deptHtml = '';
            departments.forEach(function(dept) {
                const statusClass = dept.access === 'unlocked' ? 'success' : 'danger';
                const statusIcon = dept.access === 'unlocked' ? 'fa-unlock' : 'fa-lock';
                deptHtml += `<p><i class="fa ${dept.icon}"></i> ${dept.name}: <span class="badge badge-${statusClass}"><i class="fa ${statusIcon}"></i> ${dept.access}</span> (${dept.items_count} items)</p>`;
            });

            // Build unlocked departments list
            let unlockedHtml = unlockedDepartments && unlockedDepartments.length > 0
                ? unlockedDepartments.map(d => `<li><i class="fa fa-check text-success mr-2"></i>${d}</li>`).join('')
                : '<li>No active departments</li>';

            // Build locked departments list
            let lockedHtml = lockedDepartments && lockedDepartments.length > 0
                ? lockedDepartments.map(d => `<li><i class="fa fa-times text-danger mr-2"></i>${d}</li>`).join('')
                : '<li class="text-success"><i class="fa fa-check-circle"></i> All departments unlocked!</li>';

            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><i class="fa fa-school"></i> ${school.name}</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Centre Number:</strong> ${school.centre_number}</p>
                                <p><strong>District:</strong> ${school.district || 'N/A'}</p>
                                <p><strong>County:</strong> ${school.county || 'N/A'}</p>
                                <p><strong>Sub-county:</strong> ${school.subcounty || 'N/A'}</p>
                                <p><strong>Status:</strong> <span class="badge badge-${school.status === 'active' ? 'success' : (school.status === 'inactive' ? 'warning' : 'danger')}">${school.status}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0"><i class="fa fa-user"></i> Admin Contact</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> ${school.admin_name || 'N/A'}</p>
                                <p><strong>Email:</strong> ${school.admin_email || 'N/A'}</p>
                                <p><strong>Phone:</strong> ${school.admin_phone || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary">${users.count}</h3>
                                <p class="mb-0">Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success">${inventory.total_items}</h3>
                                <p class="mb-0">Inventory Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning">${suppliers.count}</h3>
                                <p class="mb-0">Suppliers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-info">${storageLocations.count}</h3>
                                <p class="mb-0">Storage Locations</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fa fa-building"></i> Department Access Status</h6>
                            </div>
                            <div class="card-body">
                                ${deptHtml}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">Inventory Stats</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Total Items:</strong> ${inventory.total_items}</p>
                                <p><strong>Total Value:</strong> ${inventory.total_value.toLocaleString()}</p>
                                <p><strong>Low Stock:</strong> <span class="text-danger">${inventory.low_stock_count}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fa fa-check-circle"></i> Active Departments</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">${unlockedHtml}</ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fa fa-lock"></i> Locked Departments</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">${lockedHtml}</ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Orders Summary</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Total Orders:</strong> ${orders.total}</p>
                                <p><strong>Total Amount:</strong> ${orders.total_amount ? orders.total_amount.toLocaleString() : 0}</p>
                                <p><strong>Pending:</strong> ${orders.pending}</p>
                                <p><strong>Completed:</strong> ${orders.completed}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-center">
                        <a href="/schools/${school.id}" class="btn btn-primary btn-lg">
                            <i class="fa fa-eye"></i> View Full School Details
                        </a>
                    </div>
                </div>
            `;

            $('#schoolDetailsContent').html(html);
        },
        error: function(xhr) {
            $('#schoolDetailsContent').html(`
                <div class="alert alert-danger">
                    <strong>Error!</strong> Failed to load school details. Please try again.
                </div>
            `);
        }
    });
}
</script>
@endpush
