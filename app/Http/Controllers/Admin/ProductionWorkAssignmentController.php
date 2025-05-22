<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionWorkAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionWorkAssignmentController extends Controller
{
    public function data(Request $request)
    {
        $q = ProductionWorkAssignment::with(['tailor', 'order_item', 'order_item.order']);

        $q->whereHas('order_item.order', function ($query) use ($request) {
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
            'order_item_id' => 'required|exists:production_order_items,id',
            'tailor_id' => 'required|exists:tailors,id',
            'quantity' => 'required|numeric',
            'datetime' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? ProductionWorkAssignment::findOrFail($request->id) : new ProductionWorkAssignment([
            'order_id' => $request->order_id,
        ]);

        // Ambil order_item lengkap dengan relasi work assignments
        $orderItem = ProductionOrderItem::with('work_assignments')->findOrFail($validated['order_item_id']);

        // Hitung total quantity yang sudah dipakai
        $totalAssigned = $orderItem->work_assignments()
            ->when($request->id, fn($q) => $q->where('id', '!=', $request->id)) // kecualikan jika sedang update
            ->sum('quantity');

        // Sisa quantity yang tersedia
        $remainingQty = $orderItem->ordered_quantity - $totalAssigned;

        // Validasi tambahan
        if ($validated['quantity'] > $remainingQty) {
            return response()->json([
                'errors' => [
                    'quantity' => "Jumlah kwantitas melebihi sisa yang tersedia ($remainingQty)."
                ]
            ], 422);
        }

        $item->fill($validated);
        $item->save();

        // supaya data relasi di client terjaga
        $item = ProductionWorkAssignment::with(['tailor', 'order_item', 'order_item.order'])->find($item->id);

        return response()->json([
            'message' => 'Item berhasil diperbarui',
            'id' => $item->id,
            'item' => $item,
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionWorkAssignment::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => `Item tugas #$item->id telah dihapus.`
        ]);
    }
}
