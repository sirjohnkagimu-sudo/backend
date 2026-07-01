<?php $__env->startSection('content'); ?>

<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title"><?php echo e(session('title')); ?></h4>
    </div>
    <div class="page-rightheader">
        <div class="btn-list">
            <a href="<?php echo e(route('web.schools.all')); ?>" class="btn btn-primary">
                <i class="fa fa-school"></i> Manage Schools
            </a>
        </div>
    </div>
</div>
<!--End Page header-->


<div class="row">
    <!-- CARD 1 -->
    <div class="col-sm-12 col-md-6 col-xl-3">
        <div class="card bg-teal">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Laboratory Products</h6>
                        <h2 class="text-white m-0 font-weight-bold"><?php echo e($labs ?? '0'); ?></h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-file-text-o fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 2 -->
    <div class="col-sm-12 col-md-6 col-xl-3">
        <div class="card bg-indigo">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Staff</h6>
                        <h2 class="text-white m-0 font-weight-bold">Soon to come</h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-users fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 3 -->
    <div class="col-sm-12 col-md-6 col-xl-3">
        <div class="card bg-teal">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Registered Schools</h6>
                        <h2 class="text-white m-0 font-weight-bold"><?php echo e($schoolsCount ?? '0'); ?></h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-school fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 4 -->
    <div class="col-sm-12 col-md-6 col-xl-3">
        <div class="card bg-indigo">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-white">Clients</h6>
                        <h2 class="text-white m-0 font-weight-bold"><?php echo e($users ?? '0'); ?></h2>
                    </div>
                    <div class="ml-auto">
                        <i class="fa fa-th-list fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 🔽 NEW ROW FOR FULL-WIDTH SCHOOLS MANAGEMENT TABLE -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Schools Management</h3>
                <a href="<?php echo e(route('web.schools.all')); ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-external-link-alt"></i> View All Schools
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="schools-table" class="table table-bordered text-nowrap mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Centre Number</th>
                                <th>District</th>
                                <th>Admin Name</th>
                                <th>Admin Email</th>
                                <th>Status</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-id="<?php echo e($school->id); ?>">
                                <td><strong><?php echo e($school->name); ?></strong></td>
                                <td><?php echo e($school->centre_number); ?></td>
                                <td><?php echo e($school->district ?? 'N/A'); ?></td>
                                <td><?php echo e($school->admin_name); ?></td>
                                <td><?php echo e($school->admin_email); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo e($school->status === 'active' ? 'success' : ($school->status === 'inactive' ? 'warning' : 'danger')); ?>">
                                        <?php echo e(ucfirst($school->status)); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo e($school->users_count ?? 0); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('web.schools.details', $school->id)); ?>" class="btn btn-sm btn-info" title="View Full Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-success" onclick="updateStatus('<?php echo e($school->id); ?>', 'active')" <?php echo e($school->status === 'active' ? 'disabled' : ''); ?> title="Activate">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="updateStatus('<?php echo e($school->id); ?>', 'inactive')" <?php echo e($school->status === 'inactive' ? 'disabled' : ''); ?> title="Deactivate">
                                            <i class="fa fa-pause"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="updateStatus('<?php echo e($school->id); ?>', 'suspended')" <?php echo e($school->status === 'suspended' ? 'disabled' : ''); ?> title="Suspend">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
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
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
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

function getStatusBadge(status) {
    if (status === 'active') return '<span class="badge badge-success">Active</span>';
    if (status === 'inactive') return '<span class="badge badge-warning">Inactive</span>';
    if (status === 'suspended') return '<span class="badge badge-danger">Suspended</span>';
    return '<span class="badge badge-secondary">' + status + '</span>';
}

// Auto-refresh every 30 seconds
setInterval(function() {
    $.ajax({
        url: '/api/schools',
        type: 'GET',
        success: function(response) {
            var tbody = $('#schools-table tbody');
            tbody.empty();
            var schools = response.schools || [];
            schools.forEach(function(school) {
                var row = '<tr data-id="' + school.id + '">' +
                    '<td><strong>' + school.name + '</strong></td>' +
                    '<td>' + school.centre_number + '</td>' +
                    '<td>' + (school.district || 'N/A') + '</td>' +
                    '<td>' + (school.admin_name || 'N/A') + '</td>' +
                    '<td>' + (school.admin_email || 'N/A') + '</td>' +
                    '<td>' + getStatusBadge(school.status) + '</td>' +
                    '<td><span class="badge badge-info">' + (school.users_count) + '</span></td>' +
                    '<td>' +
                        '<div class="btn-group">' +
                            '<a href="/schools/' + school.id + '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a> ' +
                            '<button class="btn btn-sm btn-success" onclick="updateStatus(\'' + school.id + '\', \'active\')" ' + (school.status === 'active' ? 'disabled' : '') + '><i class="fa fa-check"></i></button> ' +
                            '<button class="btn btn-sm btn-warning" onclick="updateStatus(\'' + school.id + '\', \'inactive\')" ' + (school.status === 'inactive' ? 'disabled' : '') + '><i class="fa fa-pause"></i></button> ' +
                            '<button class="btn btn-sm btn-danger" onclick="updateStatus(\'' + school.id + '\', \'suspended\')" ' + (school.status === 'suspended' ? 'disabled' : '') + '><i class="fa fa-ban"></i></button>' +
                        '</div>' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
        }
    });
}, 30000);
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\backend\resources\views/dashboard.blade.php ENDPATH**/ ?>