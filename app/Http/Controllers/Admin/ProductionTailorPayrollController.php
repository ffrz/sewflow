<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductionTailorPayroll;
use App\Models\Tailor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionTailorPayrollController extends Controller
{
    public function index()
    {
        return inertia('admin/production-tailor-payroll/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/production-tailor-payroll/Detail', [
            'data' => ProductionTailorPayroll::with('customer')->findOrFail($id),
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
        $q = ProductionTailorPayroll::with('customer')
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
        $item = $id ? ProductionTailorPayroll::findOrFail($id) : new ProductionTailorPayroll([
            'tailor_id' => null,
            'period_start' => null,
            'period_end' => Carbon::now(),
            'total_amount' => 0,
            'status',
        ]);

        return inertia('admin/production-tailor-payroll/Editor', [
            'data' => $item,
            'tailors' => Tailor::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'tailor_id' => 'required|exists:customers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $item = !$request->id ? new ProductionTailorPayroll() : ProductionTailorPayroll::findOrFail($request->post('id', 0));
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
        $item = ProductionTailorPayroll::with('customer')->findOrFail($request->id);

        return inertia('admin/production-tailor-payroll/ItemEditor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function itemEditor(Request $request)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionTailorPayroll::with('brand')->findOrFail($request->id);

        return inertia('admin/production-tailor-payroll/ItemEditor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionTailorPayroll::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Order $item->id telah dihapus."
        ]);
    }
}
