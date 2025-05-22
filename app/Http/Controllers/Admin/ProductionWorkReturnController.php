<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrderItem;
use App\Models\ProductionWorkAssignment;
use App\Models\ProductionWorkReturn;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionWorkReturnController extends Controller
{
    public function assignments(Request $request, $order_id)
    {
        allowed_roles([User::Role_Admin]); // atau sesuaikan dengan middleware/logic autentikasi kamu

        $q = ProductionWorkAssignment::with(['tailor', 'order_item', 'order_item.order']);

        $q->whereHas('order_item.order', function ($query) use ($request) {
            $query->where('id', $request->order_id);
        });

        $items = $q->get();

        return response()->json($items);
    }

    public function data(Request $request)
    {
        $q = ProductionWorkReturn::with(['work_assignment', 'work_assignment.tailor', 'work_assignment.order_item', 'work_assignment.order_item.order']);

        $q->whereHas('work_assignment.order_item.order', function ($query) use ($request) {
            $query->where('id', $request->order_id);
        });

        $q->orderBy('id', 'asc');

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function save(Request $request)
    {
        allowed_roles([User::Role_Admin]);

        $validated = $request->validate([
            'assignment_id' => 'required|exists:production_work_assignments,id',
            'quantity' => 'required|numeric',
            'datetime' => 'required|date',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? ProductionWorkReturn::findOrFail($request->id) : new ProductionWorkReturn([
            'assignment_id' => $request->assignment_id,
        ]);

        // Ambil assignment
        $assignment = ProductionWorkAssignment::with('returns')->findOrFail($validated['assignment_id']);

        // Hitung total return sebelumnya (kecuali yang sedang diedit)
        $totalReturned = $assignment->returns()
            ->when($request->id, fn($q) => $q->where('id', '!=', $request->id))
            ->sum('quantity');

        $remainingQty = $assignment->quantity - $totalReturned;

        if ($validated['quantity'] > $remainingQty) {
            return response()->json([
                'errors' => [
                    'quantity' => 'Jumlah return melebihi sisa yang tersedia (' . $remainingQty . ').'
                ]
            ], 422);
        }

        $item->fill($validated);
        $item->save();

        $item = ProductionWorkReturn::with(['work_assignment', 'work_assignment.tailor', 'work_assignment.order_item', 'work_assignment.order_item.order'])
            ->find($item->id);

        return response()->json([
            'message' => 'Item berhasil diperbarui',
            'id' => $item->id,
            'item' => $item,
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionWorkReturn::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => `Item return #$item->id telah dihapus.`
        ]);
    }
}
