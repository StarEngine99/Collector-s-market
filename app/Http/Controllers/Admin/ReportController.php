<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\View\View;

/**
 * Controller báo cáo thống kê (B7).
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function index(ReportFilterRequest $request): View
    {
        $data = $this->reportService->getDashboardData($request->validated());

        return view('admin.reports.index', $data);
    }
}
