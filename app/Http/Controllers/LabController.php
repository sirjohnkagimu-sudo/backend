<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class LabController extends Controller
{
    /**
     * Subcategories per category
     */
    private $subcategories = [
        'laboratory' => ['apparatus', 'specimen', 'chemical'],
        'textbooks' => ['textbook', 'revision guide', 'novel'],
        'stationery' => ['scholastic', 'paper'],
        'school_accessories' => ['accessories', 'schoolwear'],
        'boardingSchool' => ['dormitory', 'toiletries'],
        'sports' => ['equipment', 'wear'],
        'food' => ['snacks', 'beverages'],
        'technology' => ['devices', 'accessories'],
        'furniture' => ['chairs', 'tables', 'storage'],
        'health' => ['hygieneTools', 'firstaid'],
    ];

    /**
     * Display all labs
     */
    public function index()
    {
        session(['title' => 'Products']);
        $labs = Lab::all();
        return view('labs.index', compact('labs'));
    }

    /**
     * Show form to create a new lab
     */
    public function create()
    {
        return view('labs.create', ['subcategories' => $this->subcategories]);
    }

    /**
     * Store a new lab item
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Received lab creation request');

            $validated = $this->validateLab($request);

            // Set defaults
            $validated['rating'] = $validated['rating'] ?? '0';
            $validated['in_stock'] = $validated['in_stock'] ?? '1';
            $validated['purchaseType'] = $validated['purchaseType'] ?? 'purchase';

            $lab = new Lab();
            $lab->fill($validated);

            // Avatar: file OR URL
            $lab->avatar = $this->handleFileOrUrl($request, 'avatar', 'avatar_url', false);

            // Multiple images: files + URLs
            $lab->images = $this->handleFileOrUrl($request, 'images', 'images_url', true);

            $lab->save();

            return redirect()->route('labs.index')->with('success', 'Lab item created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Lab creation error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error creating lab item: ' . $e->getMessage());
        }
    }

    /**
     * Display a single lab item
     */
    public function show(Lab $lab)
    {
        return view('labs.show', compact('lab'));
    }

    /**
     * Edit form
     */
    public function edit(Lab $lab)
    {
        return view('labs.edit', ['lab' => $lab, 'subcategories' => $this->subcategories]);
    }

    /**
     * Update an existing lab item
     */
    public function update(Request $request, Lab $lab)
    {
        try {
            $validated = $this->validateLab($request);

            // Defaults
            $validated['rating'] = $validated['rating'] ?? '0';
            $validated['in_stock'] = $validated['in_stock'] ?? '1';
            $validated['purchaseType'] = $validated['purchaseType'] ?? 'purchase';

            $lab->fill($validated);

            // Update avatar: delete old file if needed
            if ($lab->avatar && !filter_var($lab->avatar, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($lab->avatar);
            }
            $lab->avatar = $this->handleFileOrUrl($request, 'avatar', 'avatar_url', false);

            // Merge old and new images
            $newImages = $this->handleFileOrUrl($request, 'images', 'images_url', true);
            $lab->images = array_unique(array_merge($lab->images ?? [], $newImages));

            $lab->save();

            return redirect()->route('labs.index')->with('status', 'Lab updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating lab item: ' . $e->getMessage());
        }
    }

    /**
     * Delete a lab item
     */
    public function destroy(Lab $lab)
    {
        // Delete avatar if it's a local file
        if ($lab->avatar && !filter_var($lab->avatar, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($lab->avatar);
        }

        // Delete multiple images if local files
        if (!empty($lab->images)) {
            foreach ($lab->images as $image) {
                if (!filter_var($image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $lab->delete();

        return redirect()->route('labs.index')->with('success', 'Lab deleted successfully.');
    }

    /**
     * Import labs from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ProductsImport, $request->file('excel_file'));

        return redirect()->route('labs.index')->with('success', 'Product Excel sheet added successfully.');
    }

    /**
     * Validation rules for store/update
     */
    private function validateLab(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'category' => 'required|in:laboratory,textbooks,stationery,school_accessories,boardingSchool,sports,food,health,furniture,technology',
            'avatar' => 'nullable|image|max:10240',
            'avatar_url' => 'nullable|url',
            'color' => 'nullable|string',
            'rating' => 'nullable|string',
            'in_stock' => 'nullable|string',
            'condition' => 'required|in:new,old',
            'price' => 'required|string',
            'unit' => 'nullable|string',
            'desc' => 'nullable|string',
            'purchaseType' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ];

        // Add subcategory validation dynamically
        $category = $request->input('category');
        if (isset($this->subcategories[$category])) {
            $rules['subcategory'] = 'required|in:' . implode(',', $this->subcategories[$category]);
        } else {
            $rules['subcategory'] = 'nullable|string';
        }

        return $request->validate($rules);
    }

    /**
     * Handle single or multiple uploads and URLs
     */
    private function handleFileOrUrl(Request $request, $fileInput, $urlInput, $multiple = false)
    {
        $results = [];

        // Handle uploaded files
        if ($request->hasFile($fileInput)) {
            foreach ((array) $request->file($fileInput) as $file) {
                $path = $file->store('images/labs', 'public');
                if ($path) $results[] = $path;
            }
        }

        // Handle URLs
        if ($request->filled($urlInput)) {
            $urls = preg_split('/[\s,]+/', trim($request->input($urlInput)));
            $urls = array_filter($urls, fn($url) => filter_var($url, FILTER_VALIDATE_URL));
            $results = array_merge($results, $urls);
        }

        // Single vs multiple
        return $multiple ? array_unique($results) : ($results[0] ?? null);
    }

}
