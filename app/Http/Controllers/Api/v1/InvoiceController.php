<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InvoiceIndexRequest;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $service) {}

    public function index(InvoiceIndexRequest $request): JsonResponse
    {
        $filters = $request->filters();

        $invoices = $this->service->getAllInvoices(
            $filters['search'],
            $filters['sort_by'],
            $filters['sort_dir'],
            $filters['filter_date'],
            $filters['per_page'],
        );

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }
}
