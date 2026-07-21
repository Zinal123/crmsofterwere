<?php

namespace App\Services\Auditing;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    private const TYPE_PERMISSIONS = [
        'employee' => 'employees.view-audit',
        'employee_document' => 'employees.view-audit',
        'attendance' => 'attendance.view-audit',
        'salary_payment' => 'payroll.view-audit',
        'invoice' => 'invoices.view-audit',
        'customer' => 'invoices.view-audit',
        'invoiceproduct' => 'invoices.view-audit',
        'paidamount' => 'invoices.view-audit',
        'product' => 'products.view-audit',
        'softerwere' => 'products.view-audit',
        'softerwere1' => 'products.view-audit',
        'lasercutting' => 'products.view-audit',
        'fource' => 'products.view-audit',
        'power' => 'products.view-audit',
        'motor' => 'products.view-audit',
        'gear' => 'products.view-audit',
        'rack' => 'products.view-audit',
        'cutting' => 'products.view-audit',
        'cnsthinks' => 'products.view-audit',
        'termandcondition' => 'products.view-audit',
        'quotation' => 'quotations.view-audit',
        'inventory' => 'inventory.view-audit',
        'user' => 'admin.view-audit',
        'machine' => 'machines.view-audit',
    ];

    public function __construct(private AuditLogRepositoryInterface $repository)
    {
    }

    public function canView(Authenticatable $user, string $type): bool
    {
        $permission = self::TYPE_PERMISSIONS[$type] ?? null;

        return $permission !== null && $user->can($permission);
    }

    public function forRecord(string $type, int $id): Collection
    {
        return $this->repository->forRecord($type, $id);
    }

    public function log(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void
    {
        $this->repository->record($model, $action, $fieldName, $oldValue, $newValue);
    }
}
