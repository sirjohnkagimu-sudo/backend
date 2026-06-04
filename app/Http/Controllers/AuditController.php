<?php

namespace App\Http\Controllers;

use App\Models\Pantry;
use App\Models\StockMovement;
use App\Models\TransactionUpdateHistory;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\LabSession;
use App\Models\ActivityLog;
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

        if (!$user->is_school_admin) {
            abort(403, 'Unauthorized access to audit trail');
        }

        $tenantId = $user->tenant_id;
        $department = strtolower((string) $request->query('department'));
        $filterUserId = (string) $request->query('user_id');
        $startDate = (string) $request->query('start_date');
        $endDate = (string) $request->query('end_date');

        if ($department === '') {
            $department = 'laboratory';
        }

        $auditEntries = [];

        if ($department === 'pantry') {
            $pantryItems = Pantry::where('tenant_id', $tenantId)
                ->with('creator')
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($pantryItems as $pantryItem) {
                if ($this->shouldSkip($filterUserId, $startDate, $endDate, $pantryItem->updated_at, (string) ($pantryItem->created_by ?? ''))) {
                    continue;
                }

                $type = $pantryItem->created_at == $pantryItem->updated_at ? 'create' : 'update';
                $auditEntries[] = [
                    'id' => 'pantry_' . $pantryItem->id . '_' . $type,
                    'type' => 'pantry_item',
                    'action' => $type,
                    'description' => ($type === 'create' ? 'Created' : 'Updated') . ' pantry item: ' . $pantryItem->name,
                    'user' => [
                        'id' => $pantryItem->created_by ?: $user->id,
                        'name' => $pantryItem->creator->name ?? ($user->firstName . ' ' . $user->lastName),
                        'email' => $pantryItem->creator->email ?? $user->email,
                    ],
                    'entity' => [
                        'id' => $pantryItem->id,
                        'name' => $pantryItem->name,
                        'type' => 'Pantry Item',
                    ],
                    'timestamp' => $type === 'create' ? $pantryItem->created_at : $pantryItem->updated_at,
                ];
            }

            $stockMovements = StockMovement::whereHas('item', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
                ->with(['item', 'creator'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($stockMovements as $movement) {
                $actorId = (string) ($movement->created_by ?? '');
                if ($this->shouldSkip($filterUserId, $startDate, $endDate, $movement->created_at, $actorId)) {
                    continue;
                }

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
        }

        if ($department === 'laboratory') {
            $items = Item::where('tenant_id', $tenantId)
                ->with(['supplier', 'location', 'creator'])
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($items as $item) {
                if ($item->created_at == $item->updated_at) {
                    $actorId = (string) ($item->created_by ?? '');
                    if ($this->shouldSkip($filterUserId, $startDate, $endDate, $item->created_at, $actorId)) {
                        continue;
                    }

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

            $labSessions = LabSession::where('tenant_id', $tenantId)
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($labSessions as $session) {
                $actorId = (string) ($session->created_by ?? '');
                if ($this->shouldSkip($filterUserId, $startDate, $endDate, $session->created_at, $actorId)) {
                    continue;
                }

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
        }

        $activityLogs = ActivityLog::where('tenant_id', $tenantId)
            ->where(function ($query) use ($department) {
                if ($department === 'pantry') {
                    $query->where('type', 'pantry_item_create')
                        ->orWhere('type', 'pantry_item_update')
                        ->orWhere('type', 'pantry_item_delete')
                        ->orWhere('type', 'pantry_session_create')
                        ->orWhere('type', 'pantry_session_update')
                        ->orWhere('type', 'pantry_session_delete');
                } elseif ($department === 'laboratory') {
                    $query->where('type', '!=', 'pantry_item_create')
                        ->where('type', '!=', 'pantry_item_update')
                        ->where('type', '!=', 'pantry_item_delete')
                        ->where('type', '!=', 'pantry_session_create')
                        ->where('type', '!=', 'pantry_session_update')
                        ->where('type', '!=', 'pantry_session_delete');
                }
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($activityLogs as $log) {
            $actorId = (string) ($log->user_id ?? '');
            if ($this->shouldSkip($filterUserId, $startDate, $endDate, $log->created_at, $actorId)) {
                continue;
            }

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

        usort($auditEntries, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return response()->json($auditEntries);
    }

    private function shouldSkip(?string $filterUserId, ?string $startDate, ?string $endDate, string $entryTimestamp, string $actorId): bool
    {
        if ($filterUserId && $filterUserId !== '' && $filterUserId !== 'all' && (string) $filterUserId !== (string) $actorId) {
            return true;
        }
        if ($startDate && $entryTimestamp < $startDate . ' 00:00:00') {
            return true;
        }
        if ($endDate && $entryTimestamp > $endDate . ' 23:59:59') {
            return true;
        }
        return false;
    }
}
