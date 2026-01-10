<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabSession;
use App\Models\Transaction;

class LabCalendarController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $sessions = LabSession::where('tenant_id', $tenantId)
            ->orderBy('start_date')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'type' => $session->type,
                    'labType' => $session->lab_type,
                    'description' => $session->description,
                    'notes' => $session->notes,
                    'startDate' => $session->start_date,
                    'endDate' => $session->end_date,
                    'startTime' => $session->start_time,
                    'endTime' => $session->end_time,
                    'students' => $session->students,
                    'instructor' => $session->instructor,
                    'status' => $session->status ?? 'scheduled',
                    'requiredItems' => [], // Not stored yet
                ];
            });

        return response()->json([
            'sessions' => $sessions,
            'transactions' => Transaction::with('item')
                ->where('tenant_id', $tenantId)
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LabSession::class);

        $data = $request->validate([
            'title' => 'required|string',
            'type' => 'required',
            'labType' => 'required',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'startTime' => 'required',
            'endTime' => 'required',
            'students' => 'nullable|integer|min:0',
            'instructor' => 'nullable|string',
        ]);

        $session = LabSession::create([
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'lab_type' => $data['labType'],
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'start_time' => $data['startTime'],
            'end_time' => $data['endTime'],
            'students' => $data['students'] ?? 0,
            'instructor' => $data['instructor'] ?? null,
        ]);

        return response()->json([
            'id' => $session->id,
            'title' => $session->title,
            'type' => $session->type,
            'labType' => $session->lab_type,
            'description' => $session->description,
            'notes' => $session->notes,
            'startDate' => $session->start_date,
            'endDate' => $session->end_date,
            'startTime' => $session->start_time,
            'endTime' => $session->end_time,
            'students' => $session->students,
            'instructor' => $session->instructor,
            'status' => $session->status ?? 'scheduled',
            'requiredItems' => [],
        ], 201);
    }

    public function update(Request $request, LabSession $labSession)
    {
        $this->authorize('update', $labSession);

        $data = $request->only([
            'title',
            'type',
            'labType',
            'description',
            'notes',
            'startDate',
            'endDate',
            'startTime',
            'endTime',
            'students',
            'instructor',
        ]);

        // Convert camelCase to snake_case for database
        $updateData = [
            'title' => $data['title'] ?? $labSession->title,
            'type' => $data['type'] ?? $labSession->type,
            'lab_type' => $data['labType'] ?? $labSession->lab_type,
            'description' => $data['description'] ?? $labSession->description,
            'notes' => $data['notes'] ?? $labSession->notes,
            'start_date' => $data['startDate'] ?? $labSession->start_date,
            'end_date' => $data['endDate'] ?? $labSession->end_date,
            'start_time' => $data['startTime'] ?? $labSession->start_time,
            'end_time' => $data['endTime'] ?? $labSession->end_time,
            'students' => $data['students'] ?? $labSession->students,
            'instructor' => $data['instructor'] ?? $labSession->instructor,
        ];

        $labSession->update($updateData);

        return response()->json([
            'id' => $labSession->id,
            'title' => $labSession->title,
            'type' => $labSession->type,
            'labType' => $labSession->lab_type,
            'description' => $labSession->description,
            'notes' => $labSession->notes,
            'startDate' => $labSession->start_date,
            'endDate' => $labSession->end_date,
            'startTime' => $labSession->start_time,
            'endTime' => $labSession->end_time,
            'students' => $labSession->students,
            'instructor' => $labSession->instructor,
            'status' => $labSession->status ?? 'scheduled',
            'requiredItems' => [],
        ]);
    }

    public function destroy(LabSession $labSession)
    {
        $this->authorize('delete', $labSession);

        $labSession->delete();

        return response()->json(['message' => 'Session deleted']);
    }
}

