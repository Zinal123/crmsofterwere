<?php

namespace App\Http\Controllers\Auditing;

use App\Http\Controllers\Controller;
use App\Services\Auditing\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private AuditLogService $service)
    {
    }

    public function index(Request $request)
    {
        $data = $this->service->paginatedIndex($request->user(), $request->input('type'));

        return view('admin.audit-logs.index', $data);
    }

    public function forRecord(Request $request, string $type, int $id)
    {
        abort_unless($this->service->canView($request->user(), $type), 403);

        return view('components.ui.audit-trail', ['logs' => $this->service->forRecord($type, $id)]);
    }
}
