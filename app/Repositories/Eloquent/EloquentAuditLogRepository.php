<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function record(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void
    {
        // Guard, not an oversight: database/migrations/2014_10_12_000000_create_users_table.php
        // seeds an admin User::create() inside its own up() method, which runs before the
        // audit_logs migration in timestamp order. Once User uses Auditable, that seed insert
        // fires a 'created' event against a table that doesn't exist yet on a fresh migrate
        // (every RefreshDatabase test run, and any fresh install). Audit logging is a
        // side-concern and must never break the primary write path it's observing.
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::create([
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'field_name' => $fieldName,
            'old_value' => $oldValue !== null ? (string) $oldValue : null,
            'new_value' => $newValue !== null ? (string) $newValue : null,
            // Explicitly the "web" (staff) guard, not the unguarded auth()->id()
            // - audit_logs.user_id has a foreign key to the staff `users` table,
            // but Laravel's auth:<guard> middleware calls Auth::shouldUse() on a
            // successful check, so the "default" guard becomes whichever guard
            // authenticated the current request. Once the client portal (its
            // own "client" guard) started mutating Auditable models, auth()->id()
            // silently returned a client_accounts.id instead - fine numerically,
            // but not a real users.id, so the FK insert failed outright.
            'user_id' => auth('web')->id(),
        ]);
    }

    public function forRecord(string $type, int $id): Collection
    {
        return $this->tenantScope->apply(
            AuditLog::where('auditable_type', $type)->where('auditable_id', $id)
        )->orderByDesc('created_at')->get();
    }
}
