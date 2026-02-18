@extends('layouts.master')

@section('content')


<!-- End Row-->

<div class="col-lg-12">>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Product</h4>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('index.libraries') }}"> Back</a>
        </div>
        @if(session('status'))
        <div class="alert alert-success mb-1 mt-1">
            {{ session('status') }}
        </div>
        @endif
        <div class="card-body">
            <form action="{{ route('update.libraries', $library->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name" class="form-label">Product Name:</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $library->name) }}" placeholder="">
                    @error('name')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="desc" class="form-label">Description:</label>
                    <textarea name="desc" rows="4" cols="30" class="form-control tinymce-editor">{{ old('desc', $library->desc) }}</textarea>
                    @error('desc')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Select the Category of the Product:</label>
                    <select name="category" id="category" class="form-control">
                        <option value="textbook" {{ old('category', $library->category) == 'textbook' ? 'selected' : '' }}>textbook</option>
                        <option value="NewCurriculum" {{ old('category', $library->category) == 'NewCurriculum' ? 'selected' : '' }}>NewCurriculum</option>
                    </select>
                    @error('category')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="color" class="form-label">Product Color:</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color', $library->color) }}" placeholder="">
                    @error('color')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="brand" class="form-label">Product brand:</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $library->brand) }}" placeholder="">
                    @error('brand')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="in_stock" class="form-label">Number of Available pieces in stock:</label>
                    <input type="text" name="in_stock" class="form-control" value="{{ old('in_stock', $library->in_stock) }}" placeholder="">
                    @error('in_stock')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Product price:</label>
                    <input type="text" name="price" class="form-control" value="{{ old('price', $library->price) }}" placeholder="">
                    @error('price')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="discount" class="form-label">Price discount if available:</label>
                    <input type="text" name="discount" class="form-control" value="{{ old('discount', $library->discount) }}" placeholder="">
                    @error('discount')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="condition" class="form-label">whats the condition of the Product:</label>
                    <select name="condition" id="condition" class="form-control">
                        <option value="new" {{ old('condition', $library->condition) == 'new' ? 'selected' : '' }}>New</option>
                        <option value="old" {{ old('condition', $library->condition) == 'old' ? 'selected' : '' }}>Old</option>
                    </select>
                    @error('condition')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-10">
                    <label for='avatar' class="form-label">Select Product Main Image (leave empty to keep existing):</label>
                    <input type="file" name="avatar" id="avatar" class="form-control" />
                    @error('avatar')
                    <div class="alert alert-danger mt-4 mb-1">{{ $message }}</div>
                    @enderror
                    @if($library->avatar)
                    <div class="mt-2">
                        <p>Current Image:</p>
                        <img src="{{ asset('storage/' . $library->avatar) }}" alt="Current Image" width="150">
                    </div>
                    @endif
                </div>

                <div class="col-md-10">
                    <label for="images" class="form-label">Upload Additional Images (leave empty to keep existing):</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple />
                    @error('images')
                    <div class="alert alert-danger mt-4 mb-1">{{ $message }}</div>
                    @enderror
                </div>


                <button type="submit" class="btn btn-primary mt-5 mb-0">Update Product</button>

            </form>
        </div>
    </div>
</div>

<!-- End Row -->

@endsection
