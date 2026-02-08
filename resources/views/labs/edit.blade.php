@extends('layouts.master')

@section('content')

<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Product</h4>
        </div>
        <div class="pull-right mb-3">
            <a class="btn btn-primary" href="{{ route('labs.index') }}"> Back</a>
        </div>

        @if(session('status'))
            <div class="alert alert-success mb-1 mt-1">{{ session('status') }}</div>
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
            <form action="{{ route('labs.update', $lab->id)}}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $lab->name) }}">
                    @error('name')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="desc">Description:</label>
                    <textarea name="desc" rows="4" class="form-control tinymce-editor">{{ old('desc', $lab->desc) }}</textarea>
                    @error('desc')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="form-control">
                        <option value="" disabled>Select a category</option>
                        <option value="laboratory" {{ old('category', $lab->category) == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                        <option value="textbooks" {{ old('category', $lab->category) == 'textbooks' ? 'selected' : '' }}>Textbooks</option>
                        <option value="stationery" {{ old('category', $lab->category) == 'stationery' ? 'selected' : '' }}>Stationery</option>
                        <option value="school_accessories" {{ old('category', $lab->category) == 'school_accessories' ? 'selected' : '' }}>School Wear and Accessories</option>
                        <option value="boardingSchool" {{ old('category', $lab->category) == 'boardingSchool' ? 'selected' : '' }}>Boarding School</option>
                        <option value="sports" {{ old('category', $lab->category) == 'sports' ? 'selected' : '' }}>Sports & Physical Education</option>
                        <option value="food" {{ old('category', $lab->category) == 'food' ? 'selected' : '' }}>Food & Snacks</option>
                        <option value="technology" {{ old('category', $lab->category) == 'technology' ? 'selected' : '' }}>Technology</option>
                        <option value="furniture" {{ old('category', $lab->category) == 'furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="health" {{ old('category', $lab->category) == 'health' ? 'selected' : '' }}>Health</option>
                    </select>
                    @error('category')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Subcategory -->
                <div class="form-group" id="subcategory-container"></div>
                @error('subcategory')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                @enderror

                <!-- Color -->
                <div class="form-group">
                    <label for="color">Product Color:</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color', $lab->color) }}">
                </div>

                <!-- Rating -->
                <div class="form-group">
                    <label for="rating">Product Rating (1 to 5):</label>
                    <input type="text" name="rating" class="form-control" value="{{ old('rating', $lab->rating) }}">
                </div>

                <!-- In Stock -->
                <div class="form-group">
                    <label for="in_stock">Number of Available Pieces:</label>
                    <input type="text" name="in_stock" class="form-control" value="{{ old('in_stock', $lab->in_stock) }}">
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label for="price">Product Price:</label>
                    <input type="text" name="price" class="form-control" value="{{ old('price', $lab->price) }}">
                </div>

                <!-- Purchase Type -->
                <div class="form-group">
                    <label for="purchaseType">Purchase or Hire?</label>
                    <input type="text" name="purchaseType" class="form-control" value="{{ old('purchaseType', $lab->purchaseType) }}">
                </div>

                <!-- Unit -->
                <div class="form-group">
                    <label for="unit">Product Unit:</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $lab->unit) }}">
                </div>

                <!-- Expiry Date -->
                <div class="form-group">
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $lab->expiry_date ? $lab->expiry_date->format('Y-m-d') : '') }}">
                    @error('expiry_date')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Condition -->
                <div class="form-group">
                    <label for="condition">Condition:</label>
                    <select name="condition" id="condition" class="form-control">
                        <option value="new" {{ old('condition', $lab->condition) == 'new' ? 'selected' : '' }}>New</option>
                        <option value="old" {{ old('condition', $lab->condition) == 'old' ? 'selected' : '' }}>Old</option>
                    </select>
                </div>

                <!-- Main Avatar -->
                <div class="col-md-10">
                    <label for="avatar">Main Image:</label>
                    @if($lab->avatar)
                        <img src="{{ filter_var($lab->avatar, FILTER_VALIDATE_URL) ? $lab->avatar : asset('storage/' . $lab->avatar) }}" width="150" class="mb-2">
                    @endif
                    <input type="file" name="avatar" id="avatar" class="form-control" />
                </div>

                <div class="form-group">
                    <label for="avatar_url">Or online image URL:</label>
                    <input type="url" name="avatar_url" class="form-control" value="{{ old('avatar_url', $lab->avatar) }}">
                </div>

                <!-- Additional Images -->
                <div class="col-md-10">
                    <label for="images">Additional Images:</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple />
                </div>

                <!-- Additional Image URLs -->
                @php
                    $imageUrls = isset($lab->images) ? array_filter($lab->images, fn($img) => filter_var($img, FILTER_VALIDATE_URL)) : [];
                    $imageUrls = array_unique($imageUrls); // Remove duplicates
                @endphp
                <div class="form-group mt-3">
                    <label for="images_url">Or enter image URLs (comma-separated):</label>
                    <textarea name="images_url" class="form-control" rows="3">{{ old('images_url', implode(',', $imageUrls)) }}</textarea>
                </div>

                <!-- Image Previews -->
                <div class="form-group mt-2">
                    <label>Image Previews:</label>
                    <div id="image-previews" class="d-flex flex-wrap gap-2"></div>
                </div>

                <button type="submit" class="btn btn-primary mt-4 mb-0">Update Product</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Subcategory handling
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

    populateSubcategory(categorySelect.value, "{{ old('subcategory', $lab->subcategory ?? '') }}");
    categorySelect.addEventListener('change', () => populateSubcategory(categorySelect.value));

    // Image previews
    const imagesInput = document.getElementById('images');
    const imagesUrlTextarea = document.querySelector('textarea[name="images_url"]');
    const previewContainer = document.getElementById('image-previews');

    function updatePreviews() {
        previewContainer.innerHTML = '';

        // Uploaded files
        if (imagesInput.files) {
            Array.from(imagesInput.files).forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.width = 100;
                img.height = 100;
                img.style.objectFit = 'cover';
                img.classList.add('border', 'p-1');
                previewContainer.appendChild(img);
            });
        }

        // URL images
        const urls = imagesUrlTextarea.value.split(',').map(u => u.trim()).filter(u => u);
        urls.forEach(url => {
            const img = document.createElement('img');
            img.src = url;
            img.width = 100;
            img.height = 100;
            img.style.objectFit = 'cover';
            img.classList.add('border', 'p-1');
            previewContainer.appendChild(img);
        });
    }

    imagesInput.addEventListener('change', updatePreviews);
    imagesUrlTextarea.addEventListener('input', updatePreviews);

    // Initial load
    updatePreviews();
</script>

@endsection
