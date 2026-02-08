@extends('layouts.master')

@section('content')

<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Add Product</h4>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('labs.index') }}"> Back</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-1 mt-1">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-1 mt-1">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-1 mt-1">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">
            <form action="{{ route('labs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Name -->
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    @error('name')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="desc">Description:</label>
                    <textarea name="desc" rows="4" class="form-control tinymce-editor">{{ old('desc') }}</textarea>
                    @error('desc')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="form-control">
                        <option value="" disabled selected>Select a category</option>
                        <option value="laboratory" {{ old('category') == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                        <option value="textbooks" {{ old('category') == 'textbooks' ? 'selected' : '' }}>Textbooks</option>
                        <option value="stationery" {{ old('category') == 'stationery' ? 'selected' : '' }}>Stationery</option>
                        <option value="school_accessories" {{ old('category') == 'school_accessories' ? 'selected' : '' }}>School Wear and Accessories</option>
                        <option value="boardingSchool" {{ old('category') == 'boardingSchool' ? 'selected' : '' }}>Boarding School</option>
                        <option value="sports" {{ old('category') == 'sports' ? 'selected' : '' }}>Sports & Physical Education</option>
                        <option value="food" {{ old('category') == 'food' ? 'selected' : '' }}>Food & Snacks </option>
                        <option value="technology" {{ old('category') == 'technology' ? 'selected' : '' }}>Technology & Devices</option>
                        <option value="furniture" {{ old('category') == 'furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="health" {{ old('category') == 'health' ? 'selected' : '' }}>Health & Sanitation</option>
                    </select>
                    @error('category')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Subcategory (dynamic) -->
                <div class="form-group" id="subcategory-container"></div>
                @error('subcategory')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                @enderror

                <!-- Color -->
                <div class="form-group">
                    <label for="color">Product Color:</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color') }}">
                    @error('color')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Rating -->
                <div class="form-group">
                    <label for="rating">Product rating (1 to 5):</label>
                    <input type="text" name="rating" class="form-control" value="{{ old('rating') }}">
                    @error('rating')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- In stock -->
                <div class="form-group">
                    <label for="in_stock">Number of Available pieces:</label>
                    <input type="text" name="in_stock" class="form-control" value="{{ old('in_stock') }}">
                    @error('in_stock')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label for="price">Product price:</label>
                    <input type="text" name="price" class="form-control" value="{{ old('price') }}">
                    @error('price')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Purchase Type -->
                <div class="form-group">
                    <label for="purchaseType">Purchase or Hire?</label>
                    <input type="text" name="purchaseType" class="form-control" value="{{ old('purchaseType') }}">
                    @error('purchaseType')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Unit -->
                <div class="form-group">
                    <label for="unit">Product unit:</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit') }}">
                    @error('unit')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div class="form-group">
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                    @error('expiry_date')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Condition -->
                <div class="form-group">
                    <label for="condition">Condition:</label>
                    <select name="condition" id="condition" class="form-control">
                        <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="old" {{ old('condition') == 'old' ? 'selected' : '' }}>Old</option>
                    </select>
                    @error('condition')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Avatar -->
                <div class="col-md-10">
                    <label for="avatar">Main Image:</label>
                    <input type="file" name="avatar" id="avatar" class="form-control" />
                    @error('avatar')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="avatar_url">Or online image URL:</label>
                    <input type="url" name="avatar_url" class="form-control" value="{{ old('avatar_url') }}">
                </div>

                <!-- Additional Images -->
                <div class="col-md-10">
                    <label for="images">Additional Images:</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple />
                </div>

                <div class="form-group mt-3">
                    <label for="images_url">Or enter image URLs (comma-separated):</label>
                    <textarea name="images_url" class="form-control" rows="3">{{ old('images_url') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary mt-4 mb-0">Upload Product</button>

            </form>
        </div>
         <hr>

        <h3>Bulk Upload Products (Excel)</h3>
        <form action="{{ route('labs.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="excel_file">Upload Excel File:</label>
                <input type="file" name="excel_file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Import Products</button>
        </form>
    </div>
</div>

<script>
    const subcategories = @json($subcategories);
    const categorySelect = document.getElementById('category');
    const subContainer = document.getElementById('subcategory-container');

    function populateSubcategory(cat, selected = '') {
        let html = '';
        if (subcategories[cat]) {
            html += '<label for="subcategory">Subcategory:</label>';
            html += '<select name="subcategory" id="subcategory" class="form-control">';
            html += '<option value="">-- Select Subcategory --</option>';
            subcategories[cat].forEach(sub => {
                html += `<option value="${sub}" ${sub === selected ? 'selected' : ''}>${sub}</option>`;
            });
            html += '</select>';
        } else {
            html += '<label for="subcategory">Subcategory:</label>';
            html += `<input type="text" name="subcategory" id="subcategory" class="form-control" value="${selected}" placeholder="Enter subcategory">`;
        }
        subContainer.innerHTML = html;
    }

    populateSubcategory(categorySelect.value, "{{ old('subcategory') }}");
    categorySelect.addEventListener('change', () => populateSubcategory(categorySelect.value));
</script>

@endsection
