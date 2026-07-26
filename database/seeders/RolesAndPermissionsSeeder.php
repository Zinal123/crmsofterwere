<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view',
        'products.view', 'products.create', 'products.delete', 'products.manage-config',
        'inventory.view', 'inventory.create', 'inventory.update', 'inventory.view-audit',
        'invoices.view', 'invoices.create', 'invoices.view-details', 'invoices.record-payment',
        'vendors.view',
        'payment-history.view',
        'quotations.view', 'quotations.create', 'quotations.download-pdf', 'quotations.delete',
        'admin.manage-roles',
        'admin.manage-users',
        'jobs.view-own', 'jobs.create', 'jobs.view-all', 'jobs.approve', 'jobs.assign', 'jobs.manage-machines', 'machines.view-audit', 'jobs.view-audit',
        'employees.view', 'employees.manage', 'attendance.view', 'attendance.manage', 'attendance.view-audit', 'payroll.view', 'payroll.manage-payments',
        'employees.view-audit',
        'payroll.view-audit',
        'invoices.view-audit',
        'products.view-audit',
        'quotations.view-audit',
        'admin.view-audit',
        'client-machines.view', 'client-machines.manage',
        'ticket-problem-types.manage',
        'tickets.view', 'tickets.assign', 'tickets.view-audit',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        $owner = Role::findOrCreate('Owner');
        $owner->syncPermissions(self::PERMISSIONS);

        $worker = Role::findOrCreate('Worker');
        $worker->syncPermissions(['jobs.view-own', 'jobs.create']);
    }
}
