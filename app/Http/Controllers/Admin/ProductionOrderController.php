<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductionOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    public function index()
    {
        return inertia('admin/production-order/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/production-order/Detail', [
            'data' => ProductionOrder::with('customer')->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        // Subquery untuk menghitung total return quantity per order_id
        $subQuery = DB::table('production_work_returns as r')
            ->join('production_work_assignments as a', 'r.assignment_id', '=', 'a.id')
            ->join('production_order_items as i', 'a.order_item_id', '=', 'i.id')
            ->selectRaw('i.order_id, SUM(r.quantity) as total_returned')
            ->groupBy('i.order_id');

        // Query utama
        $q = ProductionOrder::with('customer')
            ->leftJoinSub($subQuery, 'returns', function ($join) {
                $join->on('production_orders.id', '=', 'returns.order_id');
            })
            ->select('production_orders.*')
            ->selectRaw('COALESCE(returns.total_returned, 0) as returned_quantity')
            ->selectRaw('CASE WHEN production_orders.total_quantity > 0 THEN 
                        ROUND(returns.total_returned / production_orders.total_quantity * 100, 2)
                     ELSE 0 END as progress');

        // Filter
        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('model', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%')
                    ->orWhereHas('customer', function ($q2) use ($filter) {
                        $q2->where('name', 'like', '%' . $filter['search'] . '%');
                    });
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('production_orders.status', '=', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        // Paginate dan ambil data
        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? ProductionOrder::findOrFail($id) : new ProductionOrder([
            'date' => Carbon::now(),
            'type' => ProductionOrder::Type_Maklon,
            'status' => ProductionOrder::Status_Draft,
            'payment_status' => ProductionOrder::PaymentStatus_Unpaid,
            'delivery_status' => ProductionOrder::DeliveryStatus_NotSent,
        ]);

        return inertia('admin/production-order/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|string|max:50',
            'model' => 'required|string|max:100',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'status' => 'required|string|max:50',
            'payment_status' => 'required|string|max:50',
            'delivery_status' => 'required|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $item = !$request->id ? new ProductionOrder() : ProductionOrder::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return response()->json([
            'data' => $item,
            'message' => "Order $item->id telah disimpan."
        ]);
    }

    public function items(Request $request)
    {
        allowed_roles([User::Role_Admin]);
        $item = ProductionOrder::with('customer')->findOrFail($request->id);

        return inertia('admin/production-order/ItemEditor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function itemEditor(Request $request)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionOrder::with('brand')->findOrFail($request->id);

        return inertia('admin/production-order/ItemEditor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionOrder::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Order $item->id telah dihapus."
        ]);
    }
}
