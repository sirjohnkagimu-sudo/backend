<?php

namespace App\Http\Controllers;

use App\Models\SickbayStudent;
use App\Models\SickbayMedicine;
use App\Models\SickbayVisit;
use App\Models\SickbayAdmission;
use App\Models\SickbayReferral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SickbayController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    // ==================== STUDENTS ====================

    /**
     * Get all students
     */
    public function students(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = SickbayStudent::where('tenant_id', $tenantId);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('class')) {
            $query->where('class', $request->class);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $students = $query->orderBy('first_name')->get();

        return response()->json(['students' => $students]);
    }

    /**
     * Get a specific student
     */
    public function showStudent(SickbayStudent $student): JsonResponse
    {
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this student');
        }

        $student->load(['visits', 'admissions', 'referrals']);

        return response()->json(['student' => $student]);
    }

    /**
     * Store a new student
     */
    public function storeStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admission_number' => 'required|string|max:50|unique:sickbay_students,admission_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|string|in:male,female,other',
            'class' => 'nullable|string|max:50',
            'stream' => 'nullable|string|max:50',
            'parent_name' => 'nullable|string|max:200',
            'parent_phone' => 'nullable|string|max:50',
            'parent_email' => 'nullable|email|max:200',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        // Convert arrays to JSON strings for storage
        if (isset($validated['allergies']) && is_array($validated['allergies'])) {
            $validated['allergies'] = json_encode($validated['allergies']);
        }
        if (isset($validated['chronic_conditions']) && is_array($validated['chronic_conditions'])) {
            $validated['chronic_conditions'] = json_encode($validated['chronic_conditions']);
        }
        if (isset($validated['emergency_contact']) && is_array($validated['emergency_contact'])) {
            $validated['emergency_contact'] = json_encode($validated['emergency_contact']);
        }

        $student = SickbayStudent::create($validated);

        return response()->json(['student' => $student, 'message' => 'Student added successfully'], 201);
    }

    /**
     * Update a student
     */
    public function updateStudent(Request $request, SickbayStudent $student): JsonResponse
    {
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this student');
        }

        $validated = $request->validate([
            'admission_number' => 'sometimes|string|max:50|unique:sickbay_students,admission_number,' . $student->id,
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'gender' => 'sometimes|string|in:male,female,other',
            'date_of_birth' => 'sometimes|date',
            'class' => 'nullable|string|max:50',
            'stream' => 'nullable|string|max:50',
            'parent_name' => 'nullable|string|max:200',
            'parent_phone' => 'nullable|string|max:50',
            'parent_email' => 'nullable|email|max:200',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Convert arrays to JSON strings for storage
        if (isset($validated['allergies']) && is_array($validated['allergies'])) {
            $validated['allergies'] = json_encode($validated['allergies']);
        }
        if (isset($validated['chronic_conditions']) && is_array($validated['chronic_conditions'])) {
            $validated['chronic_conditions'] = json_encode($validated['chronic_conditions']);
        }
        if (isset($validated['emergency_contact']) && is_array($validated['emergency_contact'])) {
            $validated['emergency_contact'] = json_encode($validated['emergency_contact']);
        }

        $student->update($validated);

        return response()->json(['student' => $student, 'message' => 'Student updated successfully']);
    }

    /**
     * Delete a student
     */
    public function destroyStudent(SickbayStudent $student): JsonResponse
    {
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this student');
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    // ==================== MEDICINES ====================

    /**
     * Get all medicines
     */
    public function medicines(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = SickbayMedicine::where('tenant_id', $tenantId);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        }

        $medicines = $query->orderBy('name')->get();

        return response()->json(['medicines' => $medicines]);
    }

    /**
     * Get a specific medicine
     */
    public function showMedicine(SickbayMedicine $medicine): JsonResponse
    {
        if ($medicine->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this medicine');
        }

        return response()->json(['medicine' => $medicine]);
    }

    /**
     * Store a new medicine
     */
    public function storeMedicine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'dosage' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:200',
            'storage_location' => 'nullable|string',
            'instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'requires_prescription' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_active'] = true;

        $medicine = SickbayMedicine::create($validated);

        return response()->json(['medicine' => $medicine, 'message' => 'Medicine added successfully'], 201);
    }

    /**
     * Update a medicine
     */
    public function updateMedicine(Request $request, SickbayMedicine $medicine): JsonResponse
    {
        if ($medicine->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this medicine');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:200',
            'category' => 'nullable|string|max:100',
            'dosage' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'sometimes|integer|min:0',
            'min_quantity' => 'sometimes|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:200',
            'storage_location' => 'nullable|string',
            'instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $medicine->update($validated);

        return response()->json(['medicine' => $medicine, 'message' => 'Medicine updated successfully']);
    }

    /**
     * Delete a medicine
     */
    public function destroyMedicine(SickbayMedicine $medicine): JsonResponse
    {
        if ($medicine->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this medicine');
        }

        $medicine->delete();

        return response()->json(['message' => 'Medicine deleted successfully']);
    }

    // ==================== VISITS ====================

    /**
     * Get all visits
     */
    public function visits(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = SickbayVisit::where('tenant_id', $tenantId);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('visit_type')) {
            $query->where('visit_type', $request->visit_type);
        }

        $visits = $query->with('student')->orderBy('visit_date', 'desc')->get();

        return response()->json(['visits' => $visits]);
    }

    /**
     * Get a specific visit
     */
    public function showVisit(SickbayVisit $visit): JsonResponse
    {
        if ($visit->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this visit');
        }

        $visit->load('student');

        return response()->json(['visit' => $visit]);
    }

    /**
     * Store a new visit
     */
    public function storeVisit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:sickbay_students,id',
            'visit_date' => 'required|date',
            'visit_type' => 'required|string|in:checkup,illness,injury,follow-up,vaccination,walk-in',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'temperature' => 'nullable|string|max:20',
            'blood_pressure' => 'nullable|string|max:30',
            'pulse' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'medicines_given' => 'nullable|array',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:completed,referred,admitted',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['visited_by'] = auth()->user()->id;
        $validated['status'] = $validated['status'] ?? 'completed';

        // Convert symptoms to JSON array if it's an array
        if (isset($validated['symptoms']) && is_array($validated['symptoms'])) {
            $validated['symptoms'] = json_encode($validated['symptoms']);
        }

        $visit = SickbayVisit::create($validated);

        return response()->json(['visit' => $visit->load('student'), 'message' => 'Visit recorded successfully'], 201);
    }

    /**
     * Update a visit
     */
    public function updateVisit(Request $request, SickbayVisit $visit): JsonResponse
    {
        if ($visit->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this visit');
        }

        $validated = $request->validate([
            'visit_date' => 'sometimes|date',
            'visit_type' => 'sometimes|string|in:checkup,illness,injury,follow-up,vaccination',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'temperature' => 'nullable|string|max:20',
            'blood_pressure' => 'nullable|string|max:30',
            'pulse' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'medicines_given' => 'nullable|array',
            'notes' => 'nullable|string',
            'status' => 'sometimes|string|in:completed,referred,admitted',
        ]);

        $visit->update($validated);

        return response()->json(['visit' => $visit->load('student'), 'message' => 'Visit updated successfully']);
    }

    /**
     * Delete a visit
     */
    public function destroyVisit(SickbayVisit $visit): JsonResponse
    {
        if ($visit->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this visit');
        }

        $visit->delete();

        return response()->json(['message' => 'Visit deleted successfully']);
    }

    // ==================== ADMISSIONS ====================

    /**
     * Get all admissions
     */
    public function admissions(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = SickbayAdmission::where('tenant_id', $tenantId);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('admission_date', $request->date);
        }

        $admissions = $query->with('student')->orderBy('admission_date', 'desc')->get();

        return response()->json(['admissions' => $admissions]);
    }

    /**
     * Get a specific admission
     */
    public function showAdmission(SickbayAdmission $admission): JsonResponse
    {
        if ($admission->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this admission');
        }

        $admission->load('student');

        return response()->json(['admission' => $admission]);
    }

    /**
     * Store a new admission
     */
    public function storeAdmission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:sickbay_students,id',
            'visit_id' => 'nullable|exists:sickbay_visits,id',
            'admission_date' => 'required|date',
            'bed_number' => 'nullable|string|max:20',
            'ward' => 'nullable|string|max:100',
            'reason' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'daily_notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['admitted_by'] = auth()->user()->id;
        $validated['status'] = 'admitted';

        $admission = SickbayAdmission::create($validated);

        // Update visit status if linked
        if (!empty($validated['visit_id'])) {
            SickbayVisit::where('id', $validated['visit_id'])->update(['status' => 'admitted']);
        }

        return response()->json(['admission' => $admission->load('student'), 'message' => 'Patient admitted successfully'], 201);
    }

    /**
     * Update an admission
     */
    public function updateAdmission(Request $request, SickbayAdmission $admission): JsonResponse
    {
        if ($admission->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this admission');
        }

        $validated = $request->validate([
            'admission_date' => 'sometimes|date',
            'discharge_date' => 'nullable|date',
            'bed_number' => 'nullable|string|max:20',
            'ward' => 'nullable|string|max:100',
            'reason' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'daily_notes' => 'nullable|string',
            'status' => 'sometimes|string|in:admitted,discharged,transferred',
            'discharge_notes' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
        ]);

        $admission->update($validated);

        return response()->json(['admission' => $admission->load('student'), 'message' => 'Admission updated successfully']);
    }

    /**
     * Discharge a patient
     */
    public function dischargePatient(Request $request, SickbayAdmission $admission): JsonResponse
    {
        if ($admission->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this admission');
        }

        $validated = $request->validate([
            'discharge_date' => 'required|date',
            'discharge_notes' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
        ]);

        $admission->update([
            'discharge_date' => $validated['discharge_date'],
            'discharge_notes' => $validated['discharge_notes'] ?? null,
            'follow_up_instructions' => $validated['follow_up_instructions'] ?? null,
            'status' => 'discharged',
        ]);

        return response()->json(['admission' => $admission->load('student'), 'message' => 'Patient discharged successfully']);
    }

    /**
     * Delete an admission
     */
    public function destroyAdmission(SickbayAdmission $admission): JsonResponse
    {
        if ($admission->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this admission');
        }

        $admission->delete();

        return response()->json(['message' => 'Admission deleted successfully']);
    }

    // ==================== REFERRALS ====================

    /**
     * Get all referrals
     */
    public function referrals(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = SickbayReferral::where('tenant_id', $tenantId);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('date')) {
            $query->whereDate('referral_date', $request->date);
        }

        $referrals = $query->with('student')->orderBy('referral_date', 'desc')->get();

        return response()->json(['referrals' => $referrals]);
    }

    /**
     * Get a specific referral
     */
    public function showReferral(SickbayReferral $referral): JsonResponse
    {
        if ($referral->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this referral');
        }

        $referral->load('student');

        return response()->json(['referral' => $referral]);
    }

    /**
     * Store a new referral
     */
    public function storeReferral(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:sickbay_students,id',
            'visit_id' => 'nullable|exists:sickbay_visits,id',
            'admission_id' => 'nullable|exists:sickbay_admissions,id',
            'referral_date' => 'required|date',
            'facility_name' => 'required|string|max:200',
            'facility_contact' => 'nullable|string|max:50',
            'facility_address' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:100',
            'reason' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'urgency' => 'nullable|string|in:emergency,urgent,routine',
            'follow_up_date' => 'nullable|date',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['referred_by'] = auth()->user()->id;
        $validated['status'] = 'pending';
        $validated['urgency'] = $validated['urgency'] ?? 'routine';

        $referral = SickbayReferral::create($validated);

        // Update visit/admission status if linked
        if (!empty($validated['visit_id'])) {
            SickbayVisit::where('id', $validated['visit_id'])->update(['status' => 'referred']);
        }
        if (!empty($validated['admission_id'])) {
            SickbayAdmission::where('id', $validated['admission_id'])->update(['status' => 'transferred']);
        }

        return response()->json(['referral' => $referral->load('student'), 'message' => 'Referral created successfully'], 201);
    }

    /**
     * Update a referral
     */
    public function updateReferral(Request $request, SickbayReferral $referral): JsonResponse
    {
        if ($referral->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this referral');
        }

        $validated = $request->validate([
            'referral_date' => 'sometimes|date',
            'facility_name' => 'sometimes|string|max:200',
            'facility_contact' => 'nullable|string|max:50',
            'facility_address' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:100',
            'reason' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'urgency' => 'sometimes|string|in:emergency,urgent,routine',
            'status' => 'sometimes|string|in:pending,completed,cancelled',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
        ]);

        $referral->update($validated);

        return response()->json(['referral' => $referral->load('student'), 'message' => 'Referral updated successfully']);
    }

    /**
     * Complete a referral
     */
    public function completeReferral(Request $request, SickbayReferral $referral): JsonResponse
    {
        if ($referral->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this referral');
        }

        $validated = $request->validate([
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
        ]);

        $referral->update([
            'status' => 'completed',
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
        ]);

        return response()->json(['referral' => $referral->load('student'), 'message' => 'Referral completed successfully']);
    }

    /**
     * Delete a referral
     */
    public function destroyReferral(SickbayReferral $referral): JsonResponse
    {
        if ($referral->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this referral');
        }

        $referral->delete();

        return response()->json(['message' => 'Referral deleted successfully']);
    }

    // ==================== DASHBOARD STATS ====================

    /**
     * Get sickbay dashboard stats
     */
    public function dashboardStats(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $today = now()->format('Y-m-d');

        $totalStudents = SickbayStudent::where('tenant_id', $tenantId)->count();
        $activeStudents = SickbayStudent::where('tenant_id', $tenantId)->where('is_active', true)->count();

        $todayVisits = SickbayVisit::where('tenant_id', $tenantId)
            ->whereDate('visit_date', $today)
            ->count();

        $currentAdmissions = SickbayAdmission::where('tenant_id', $tenantId)
            ->where('status', 'admitted')
            ->count();

        $pendingReferrals = SickbayReferral::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $lowStockMedicines = SickbayMedicine::where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        $expiredMedicines = SickbayMedicine::where('tenant_id', $tenantId)
            ->where('expiry_date', '<', $today)
            ->count();

        return response()->json([
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'todayVisits' => $todayVisits,
            'currentAdmissions' => $currentAdmissions,
            'pendingReferrals' => $pendingReferrals,
            'lowStockMedicines' => $lowStockMedicines,
            'expiredMedicines' => $expiredMedicines,
        ]);
    }
}
