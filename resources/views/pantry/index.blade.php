@extends('layouts.master')

@section('content')



<!--Page header-->
<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title">{{session('title')}}</h4>
    </div>
    <div class="page-rightheader ml-auto d-lg-flex d-none">
        <a class="btn btn-success" href="{{ route('index.pantries') }}"> Add Item</a>

    </div>
</div>
<!--End Page header-->

<!-- Expiry Alert Section -->
@php
$expiringItems = $items->filter(function($item) {
    return $item->is_expiring_soon || $item->is_expired;
});
$expiringCount = $expiringItems->count();
@endphp

@if($expiringCount > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle mr-2"></i> Expiry Alert!</h5>
            <p class="mb-0">
                <strong>{{ $expiringCount }} item(s)</strong> are expiring within 30 days or have expired!
            </p>
            @if($expiringCount > 0)
            <div class="mt-2">
                <button class="btn btn-danger btn-sm" type="button" data-toggle="collapse" data-target="#expiringItemsList" aria-expanded="false" aria-controls="expiringItemsList">
                    View Expiring Items
                </button>
            </div>
            <div class="collapse mt-2" id="expiringItemsList">
                <ul class="list-group list-group-flush">
                    @foreach($expiringItems as $item)
                    <li class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                        <span>
                            <strong>{{ $item->name }}</strong>
                            @if($item->is_expired)
                            <span class="badge badge-danger ml-2">EXPIRED</span>
                            @else
                            <span class="badge badge-warning ml-2">Expires in {{ $item->days_until_expiry }} days</span>
                            @endif
                        </span>
                        <small>Expiry: {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : 'N/A' }}</small>
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
                <div class="card-title">All Pantry Items</div>
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
                                <th class="wd-15p border-bottom-0">Name</th>
                                <th class="wd-15p border-bottom-0">Category</th>
                                <th class="wd-15p border-bottom-0">Price</th>
                                <th class="wd-15p border-bottom-0">Cover Image</th>
                                <th class="wd-15p border-bottom-0">Stock Quantity</th>
                                <th class="wd-15p border-bottom-0">Expiry Date</th>
                                <th class="wd-15p border-bottom-0">Days Until Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr @if($item->is_expired) class="table-danger" @elseif($item->is_expiring_soon) class="table-warning" @endif>
                                <td>{{$item->id}}</td>
                                <td>
                                    @if($item->is_expired)
                                    <span class="text-danger font-weight-bold">{{$item->name}}</span>
                                    @elseif($item->is_expiring_soon)
                                    <span class="text-warning font-weight-bold">{{$item->name}}</span>
                                    @else
                                    {{$item->name}}
                                    @endif
                                </td>
                                <td>{!! nl2br(wordwrap($item->category, 20, "\n", true)) !!}</td>
                                <td>UGX {{$item->price}}</td>
                                <td>
                                    @php
                                    $avatar = $item->avatar;
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
                                <td>{{$item->in_stock}}</td>
                                <td>
                                    @if($item->expiry_date)
                                        {{ $item->expiry_date->format('d M Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_expired)
                                        <span class="badge badge-danger">EXPIRED</span>
                                    @elseif($item->is_expiring_soon)
                                        <span class="badge badge-warning">{{ $item->days_until_expiry }} days</span>
                                    @elseif($item->days_until_expiry !== null)
                                        <span class="badge badge-success">{{ $item->days_until_expiry }} days</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                        <a href="{{ route('items.edit', $item->id) }}" class="btn btn-primary mb-1">
                                            Edit
                                        </a>
                                        <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light" onclick="return confirm('Are you sure you want to delete this item?')">
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