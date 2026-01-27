<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionUpdateHistory;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\LabSession;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Only allow school admins to view audit trail
        if (!$user->is_school_admin) {
            abort(403, 'Unauthorized access to audit trail');
        }

        $tenantId = $user->tenant_id;
        $auditEntries = [];

        // 1. Stock Movements
        $stockMovements = StockMovement::where('tenant_id', $tenantId)
            ->with(['item', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($stockMovements as $movement) {
            $auditEntries[] = [
                'id' => 'sm_' . $movement->id,
                'type' => 'stock_movement',
                'action' => $movement->type,
                'description' => ucfirst($movement->type) . ' ' . $movement->quantity . ' units of ' . ($movement->item->name ?? 'Unknown Item'),
                'user' => [
                    'id' => $movement->created_by,
                    'name' => $movement->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                    'email' => $movement->creator->email ?? $user->email,
                ],
                'entity' => [
                    'id' => $movement->item_id,
                    'name' => $movement->item->name ?? 'Unknown Item',
                    'type' => 'Item',
                ],
                'timestamp' => $movement->created_at,
                'ip_address' => null,
                'user_agent' => null,
            ];
        }

        // 2. Transaction Update History
        $updateHistories = TransactionUpdateHistory::with(['transaction.item', 'updater'])
            ->whereHas('transaction', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($updateHistories as $history) {
            $changes = [];
            if ($history->previous_values && $history->new_values) {
                foreach ($history->new_values as $field => $newValue) {
                    $oldValue = $history->previous_values[$field] ?? null;
                    if ($oldValue !== $newValue) {
                        $changes[] = [
                            'field' => $field,
                            'old_value' => $oldValue,
                            'new_value' => $newValue,
                        ];
                    }
                }
            }

            $auditEntries[] = [
                'id' => 'th_' . $history->id,
                'type' => 'transaction_update',
                'action' => 'update',
                'description' => 'Updated stock movement: ' . ($history->update_reason ?? 'No reason provided'),
                'user' => [
                    'id' => $history->updated_by,
                    'name' => $history->updater->name ?? ($user->firstName . ' ' . $user->lastName),
                    'email' => $history->updater->email ?? $user->email,
                ],
                'entity' => [
                    'id' => $history->inventory_transaction_id,
                    'name' => $history->transaction->item->name ?? 'Unknown Item',
                    'type' => 'Stock Movement',
                ],
                'changes' => $changes,
                'timestamp' => $history->updated_at,
                'ip_address' => null,
                'user_agent' => null,
            ];
        }

        // 3. Item Changes (creation/updates)
        $items = Item::where('tenant_id', $tenantId)
            ->with(['supplier', 'location', 'creator'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($items as $item) {
            // Track creation
            if ($item->created_at == $item->updated_at) {
                $auditEntries[] = [
                    'id' => 'ic_' . $item->id . '_create',
                    'type' => 'item_change',
                    'action' => 'create',
                    'description' => 'Created new item: ' . $item->name,
                    'user' => [
                        'id' => $item->created_by ?: $user->id,
                        'name' => $item->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                        'email' => $item->creator->email ?? $user->email,
                    ],
                    'entity' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => 'Item',
                    ],
                    'timestamp' => $item->created_at,
                ];
            }
        }

        // 4. Supplier Changes
        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($suppliers as $supplier) {
            $auditEntries[] = [
                'id' => 'sc_' . $supplier->id . '_create',
                'type' => 'supplier_change',
                'action' => 'create',
                'description' => 'Added supplier: ' . $supplier->name,
                'user' => [
                    'id' => $supplier->created_by ?: $user->id,
                    'name' => $supplier->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                    'email' => $supplier->creator->email ?? $user->email,
                ],
                'entity' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'type' => 'Supplier',
                ],
                'timestamp' => $supplier->created_at,
            ];
        }

        // 5. Location Changes
        $locations = Location::where('tenant_id', $tenantId)
            ->with('creator')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($locations as $location) {
            if ($location->created_at == $location->updated_at) {
                $auditEntries[] = [
                    'id' => 'lc_' . $location->id . '_create',
                    'type' => 'location_change',
                    'action' => 'create',
                    'description' => 'Created new storage location: ' . $location->name,
                    'user' => [
                        'id' => $location->created_by ?: $user->id,
                        'name' => $location->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                        'email' => $location->creator->email ?? $user->email,
                    ],
                    'entity' => [
                        'id' => $location->id,
                        'name' => $location->name,
                        'type' => 'Location',
                    ],
                    'timestamp' => $location->created_at,
                ];
            }
        }

        // 6. Lab Sessions
        $labSessions = LabSession::where('tenant_id', $tenantId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($labSessions as $session) {
            $auditEntries[] = [
                'id' => 'ls_' . $session->id,
                'type' => 'lab_session',
                'action' => 'create',
                'description' => 'Created lab session: ' . $session->title,
                'user' => [
                    'id' => $session->created_by,
                    'name' => $session->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                    'email' => $session->creator->email ?? $user->email,
                ],
                'entity' => [
                    'id' => $session->id,
                    'name' => $session->title,
                    'type' => 'Lab Session',
                ],
                'timestamp' => $session->created_at,
            ];
        }

        // 7. Activity Logs
        $activityLogs = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($activityLogs as $log) {
            $auditEntries[] = [
                'id' => 'al_' . $log->id,
                'type' => $log->type,
                'action' => $log->action,
                'description' => $log->description,
                'user' => [
                    'id' => $log->user_id,
                    'name' => $log->user->name ?? ($user->firstName . ' ' . $user->lastName),
                    'email' => $log->user->email ?? $user->email,
                ],
                'entity' => [
                    'id' => $log->id,
                    'name' => $log->description,
                    'type' => ucfirst($log->type),
                ],
                'timestamp' => $log->created_at,
            ];
        }

        // Sort all entries by timestamp (newest first)
        usort($auditEntries, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return response()->json($auditEntries);
    }
}
