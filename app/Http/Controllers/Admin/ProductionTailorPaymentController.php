<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionTailorPayment;
use App\Models\ProductionWorkReturn;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionTailorPaymentController extends Controller
{
    public function returns(Request $request, $order_id)
    {
        allowed_roles([User::Role_Admin]);

        $q = ProductionWorkReturn::with([
            'work_assignment',
            'work_assignment.tailor',
            'work_assignment.order_item',
            'work_assignment.order_item.order',
        ]);

        $q->whereHas('work_assignment.order_item.order', function ($query) use ($request) {
            $query->where('id', $request->order_id);
        });

        $items = $q->get();

        return response()->json($items);
    }

    public function data(Request $request)
    {
        $q = ProductionTailorPayment::with([
            'work_return',
            'work_return.work_assignment',
            'work_return.work_assignment.tailor',
            'work_return.work_assignment.order_item',
            'work_return.work_assignment.order_item.order'
        ]);

        // Filter berdasarkan order_id jika tersedia
        if ($request->filled('order_id')) {
            $q->whereHas('work_assignment.order_item.order', function ($query) use ($request) {
                $query->where('id', $request->order_id);
            });
        }

        $q->orderBy('payment_date', 'desc');

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }


    public function save(Request $request)
    {
        allowed_roles([User::Role_Admin]);

        $validated = $request->validate([
            'work_return_id' => 'required|exists:production_work_returns,id',
            'quantity' => 'required|numeric',
            'cost' => 'required|numeric',
            'amount' => 'required|numeric',
            'datetime' => 'required|date',
            'notes' => 'nullable|max:1000',
            'payroll_id' => 'nullable',
        ]);

        $item = $request->id ? ProductionTailorPayment::findOrFail($request->id) : new ProductionTailorPayment([
            'work_return_id' => $request->work_return_id,
        ]);

        $item->fill($validated);
        $item->save();

        $item = ProductionTailorPayment::with([
            'work_return',
            'work_return.work_assignment',
            'work_return.work_assignment.tailor',
            'work_return.work_assignment.order_item',
            'work_return.work_assignment.order_item.order'
        ])
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

        $item = ProductionTailorPayment::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => `Item pembayaran #$item->id telah dihapus.`
        ]);
    }
}
