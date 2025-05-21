<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return inertia('admin/order/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/order/Detail', [
            'data' => Order::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Order::with('brand');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                // $q->where('name', 'like', '%' . $filter['search'] . '%');
                // $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                // $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = Order::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/order/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Order::findOrFail($id) : new Order([
            'order_date' => now(),
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        return inertia('admin/order/Editor', [
            'data' => $item,
            'brands' => Brand::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'brand_id'        => 'required|exists:brands,id',
            'order_type'      => 'required|string|max:50',
            'order_date'      => 'required|date',
            'due_date'        => 'required|date|after_or_equal:order_date',
            'status'          => 'required|string|max:50',
            'total_quantity'  => 'required|integer|min:1',
            'total_price'     => 'required|numeric|min:0',
            'payment_status'  => 'required|string|max:50',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $item = !$request->id ? new Order() : Order::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.order.index'))->with('success', "Order #$item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Order::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Order $item->name telah dihapus."
        ]);
    }
}
