@extends('layouts.master')

@section('content')

<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary mr-2">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
        <h4 class="page-title">All Users</h4>
        <span class="text-muted mt-1">Manage all registered users across all schools</span>
    </div>
    <div class="page-rightheader">
        <div class="btn-list">
            <a href="{{ route('schools.all') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-building"></i> View Schools
            </a>
        </div>
    </div>
</div>
<!--End Page header-->

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">All Users ({{ isset($users) ? $users->total() : 0 }})</h5>
            </div>
            <div class="card-body">
                @if(isset($users) && $users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>School</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>School Admin</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->firstName }} {{ $user->lastName }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($user->school)
                                        <a href="{{ route('schools.details', $user->school) }}" class="text-primary">
                                            {{ $user->school->name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $user->school->centre_number }}</small>
                                    @else
                                        <span class="text-muted">No School</span>
                                    @endif
                                </td>
                                <td>{{ $user->role ? $user->role->name : 'N/A' }}</td>
                                <td>{{ $user->department ?? 'N/A' }}</td>
                                <td>
                                    @if($user->is_school_admin)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $user->last_login ? (is_string($user->last_login) ? $user->last_login : $user->last_login->format('Y-m-d H:i')) : 'Never' }}</td>
                                <td>
                                    @if(isset($user->is_active) ? $user->is_active : true)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ isset($users) ? $users->links() : '' }}
                </div>
                @else
                <div class="alert alert-info">
                    <strong>No users found.</strong> There are no users in the system yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#users-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[10, 'desc']] // Sort by registered date descending
    });
});
</script>
@endpush
