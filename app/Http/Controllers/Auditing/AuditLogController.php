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

    public function forRecord(Request $request, string $type, int $id)
    {
        abort_unless($this->service->canView($request->user(), $type), 403);

        return view('components.ui.audit-trail', ['logs' => $this->service->forRecord($type, $id)]);
    }
}
