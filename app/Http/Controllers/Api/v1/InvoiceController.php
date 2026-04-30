<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $filterDate = $request->query('filter_date', 'all'); // all, today, this_month, this_year
        $perPage = $request->query('per_page', 15);

        $invoices = $this->service->getAllInvoices($search, $sortBy, $sortDir, $filterDate, $perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }
}
