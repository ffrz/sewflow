<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalCost;
use App\Models\ProductionOrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionOrderItemController extends Controller
{


    public function data(Request $request)
    {
        $q = ProductionOrderItem::query();
        $q->where('order_id', '=', $request->order_id);
        $q->orderBy('id', 'asc');
        $items = $q->paginate($request->get('per_page', 10))->withQueryString();
        return response()->json($items);
    }

    public function save(Request $request)
    {
        allowed_roles([User::Role_Admin]);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'ordered_quantity' => 'required|numeric|gt:0',
            'unit_cost' => 'required|numeric',
            'unit_price' => 'required|numeric',
            'notes' => 'nullable|max:1000',
        ]);

        $item = $request->id ? ProductionOrderItem::findOrFail($request->id) : new ProductionOrderItem([
            'order_id' => $request->order_id,
        ]);

        DB::beginTransaction();
        $item->fill($validated);
        $item->save();
        DB::commit();

        return response()->json([
            'message' => 'Item berhasil diperbarui',
            'id' => $item->id,
            'item' => $item,
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        DB::beginTransaction();
        $item = ProductionOrderItem::findOrFail($id);
        $item->delete();
        DB::commit();

        return response()->json([
            'message' => `Item #$item->description telah dihapus.`
        ]);
    }

}
