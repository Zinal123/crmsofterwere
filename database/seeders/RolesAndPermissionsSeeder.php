<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view',
        'products.view', 'products.create', 'products.update', 'products.delete', 'products.manage-config',
        'inventory.view', 'inventory.create', 'inventory.update', 'inventory.view-audit',
        'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.view-details', 'invoices.record-payment',
        'vendors.view', 'vendors.manage',
        'payment-history.view',
        'quotations.view', 'quotations.create', 'quotations.update', 'quotations.download-pdf', 'quotations.delete',
        'admin.manage-roles',
        'admin.manage-users',
        'jobs.view-own', 'jobs.create', 'jobs.view-all', 'jobs.approve', 'jobs.assign', 'jobs.manage-machines', 'jobs.manage-materials', 'machines.view-audit', 'jobs.view-audit',
        'employees.view', 'employees.manage', 'attendance.view', 'attendance.manage', 'attendance.view-audit', 'payroll.view', 'payroll.manage-payments',
        'employees.view-audit',
        'payroll.view-audit',
        'invoices.view-audit',
        'products.view-audit',
        'quotations.view-audit',
        'admin.view-audit',
        'client-machines.view', 'client-machines.manage', 'client-machines.view-audit',
        'ticket-problem-types.manage', 'ticket-problem-types.view-audit',
        'tickets.view', 'tickets.assign', 'tickets.view-audit',
        'spare-parts.manage', 'spare-part-requests.view', 'spare-part-requests.manage', 'spare-part-requests.view-audit',
        'reports.view',
        'accounting.view',
        'expenses.view', 'expenses.manage', 'expenses.delete', 'expenses.view-audit',
        'vendor-payments.view', 'vendor-payments.manage',
        'vendors.view-audit',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        $owner = Role::findOrCreate('Owner');
        $owner->syncPermissions(self::PERMISSIONS);

        // Worker keeps its own-jobs scope, plus dashboard.view so it lands on
        // the "my day" dashboard at '/' instead of a bare jobs list.
        $worker = Role::findOrCreate('Worker');
        $worker->syncPermissions(['dashboard.view', 'jobs.view-own', 'jobs.create']);

        // Manager: operational, read-only across the shop floor. No finance,
        // no admin. This is a sensible baseline - widen it as the role's
        // responsibilities are finalised.
        $manager = Role::findOrCreate('Manager');
        $manager->syncPermissions([
            'dashboard.view',
            'jobs.view-all', 'jobs.view-own', 'jobs.approve', 'jobs.assign',
            'tickets.view', 'spare-part-requests.view',
            'employees.view', 'attendance.view',
            'reports.view', 'inventory.view',
        ]);

        // Account: finance-facing, read plus the money entry points. No job
        // operations or admin.
        $account = Role::findOrCreate('Account');
        $account->syncPermissions([
            'dashboard.view',
            'invoices.view', 'invoices.view-details', 'invoices.record-payment',
            'payment-history.view',
            'expenses.view', 'expenses.manage',
            'vendors.view', 'vendor-payments.view', 'vendor-payments.manage',
            'reports.view', 'accounting.view',
        ]);
    }
}
