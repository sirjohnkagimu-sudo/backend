@extends('layouts.master')

@section('content')


<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title">{{session('title')}}</h4>
    </div>
    <div class="page-rightheader ml-auto d-lg-flex d-none">
        <a class="btn btn-success" href="{{ route('labs.create') }}"> Add Product</a>

    </div>
</div>
<!--End Page header-->

<!-- Expiry Alert Section -->
@php
$expiringProducts = $labs->filter(function($lab) {
    return $lab->is_expiring_soon || $lab->is_expired;
});
$expiringCount = $expiringProducts->count();
@endphp

@if($expiringCount > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle mr-2"></i> Expiry Alert!</h5>
            <p class="mb-0">
                <strong>{{ $expiringCount }} product(s)</strong> are expiring within 30 days or have expired!
            </p>
            @if($expiringCount > 0)
            <div class="mt-2">
                <button class="btn btn-danger btn-sm" type="button" data-toggle="collapse" data-target="#expiringProductsList" aria-expanded="false" aria-controls="expiringProductsList">
                    View Expiring Products
                </button>
            </div>
            <div class="collapse mt-2" id="expiringProductsList">
                <ul class="list-group list-group-flush">
                    @foreach($expiringProducts as $product)
                    <li class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                        <span>
                            <strong>{{ $product->name }}</strong>
                            @if($product->is_expired)
                            <span class="badge badge-danger ml-2">EXPIRED</span>
                            @else
                            <span class="badge badge-warning ml-2">Expires in {{ $product->days_until_expiry }} days</span>
                            @endif
                        </span>
                        <small>Expiry: {{ $product->expiry_date ? $product->expiry_date->format('d M Y') : 'N/A' }}</small>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Row -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">All Products</div>
            </div>
            <div class="card-body">
                @if(Session::has('message'))
                <div class="alert alert-info" role="alert"><button type="button" class="close" data-dismiss="alert"
                        aria-hidden="true"></button>
                    {{Session::get('message')}}
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="example1">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th class="wd-15p border-bottom-0">title</th>
                                <th class="wd-15p border-bottom-0">category</th>
                                <th class="wd-15p border-bottom-0">price</th>
                                <th class="wd-15p border-bottom-0">cover image</th>
                                <th class="wd-15p border-bottom-0">Category</th>
                                <th class="wd-15p border-bottom-0">Subcategory</th>
                                <th class="wd-15p border-bottom-0">Expiry Date</th>
                                <th class="wd-15p border-bottom-0">Days Until Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labs as $opportunity)
                            <tr @if($opportunity->is_expired) class="table-danger" @elseif($opportunity->is_expiring_soon) class="table-warning" @endif>
                                <td>{{$opportunity->id}}</td>
                                <td>
                                    @if($opportunity->is_expired)
                                    <span class="text-danger font-weight-bold">{{$opportunity->name}}</span>
                                    @elseif($opportunity->is_expiring_soon)
                                    <span class="text-warning font-weight-bold">{{$opportunity->name}}</span>
                                    @else
                                    {{$opportunity->name}}
                                    @endif
                                </td>
                                <td>{!! nl2br(wordwrap($opportunity->category, 20, "\n", true)) !!}</td>
                                <td>UGX {{$opportunity->price}}</td>
                                <td>
                                    @php
                                    $avatar = $opportunity->avatar;
                                    $isExternal = filter_var($avatar, FILTER_VALIDATE_URL);
                                    $imageSrc = $isExternal
                                        ? $avatar
                                        : asset('storage/' .($avatar));
                                    @endphp

                                    @if(!empty($avatar))
                                        <img src="{{ $imageSrc }}" alt="Avatar" width="100" height="100">
                                    @else
                                        No image available
                                    @endif

                                </td>
                                <td>{{$opportunity->category}}</td>
                                <td>{{$opportunity->subcategory}}</td>
                                <td>
                                    @if($opportunity->expiry_date)
                                        {{ $opportunity->expiry_date->format('d M Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($opportunity->is_expired)
                                        <span class="badge badge-danger">EXPIRED</span>
                                    @elseif($opportunity->is_expiring_soon)
                                        <span class="badge badge-warning">{{ $opportunity->days_until_expiry }} days</span>
                                    @elseif($opportunity->days_until_expiry !== null)
                                        <span class="badge badge-success">{{ $opportunity->days_until_expiry }} days</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                        <a href="{{ route('labs.edit', $opportunity->id) }}" class="btn btn-primary mb-1">
                                            Edit
                                        </a>
                                        <form action="{{ route('labs.destroy', $opportunity->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light" onclick="return confirm('Are you sure you want to delete this blog?')">
                                                Delete
                                            </button>
                                        </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
                <!-- table-responsive -->
            </div>
        </div>
    </div>
    <!-- End Row -->
    <!--End Page header-->

    @endsection
