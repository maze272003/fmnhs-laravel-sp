<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;

class AdminAuditTrailController extends Controller
{
    public function __construct(private readonly AuditTrailService $auditTrailService)
    {
    }

    public function index(Request $request)
    {
        return view('admin.audit_trail', $this->auditTrailService->list(
            $request->only(['action', 'user_type', 'search', 'date_from', 'date_to'])
        ));
    }
}
