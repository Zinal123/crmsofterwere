<?php

namespace App\Providers;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\BankRepositoryInterface;
use App\Repositories\Contracts\ClientAccountRepositoryInterface;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Repositories\Contracts\ProductConfigRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use App\Repositories\Contracts\SparePartRequestRepositoryInterface;
use App\Repositories\Contracts\TicketProblemTypeRepositoryInterface;
use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VendorRepositoryInterface;
use App\Repositories\Eloquent\EloquentAttendanceRepository;
use App\Repositories\Eloquent\EloquentAuditLogRepository;
use App\Repositories\Eloquent\EloquentBankRepository;
use App\Repositories\Eloquent\EloquentClientAccountRepository;
use App\Repositories\Eloquent\EloquentClientMachineRepository;
use App\Repositories\Eloquent\EloquentDashboardRepository;
use App\Repositories\Eloquent\EloquentEmployeeDocumentRepository;
use App\Repositories\Eloquent\EloquentEmployeeRepository;
use App\Repositories\Eloquent\EloquentInventoryRepository;
use App\Repositories\Eloquent\EloquentInvoiceRepository;
use App\Repositories\Eloquent\EloquentJobPhotoRepository;
use App\Repositories\Eloquent\EloquentJobRepository;
use App\Repositories\Eloquent\EloquentMachineRepository;
use App\Repositories\Eloquent\EloquentProductConfigRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentQuotationRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentSalaryPaymentRepository;
use App\Repositories\Eloquent\EloquentSparePartRequestRepository;
use App\Repositories\Eloquent\EloquentTicketProblemTypeRepository;
use App\Repositories\Eloquent\EloquentTicketRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentVendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(ProductConfigRepositoryInterface::class, EloquentProductConfigRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, EloquentInventoryRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, EloquentQuotationRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(BankRepositoryInterface::class, EloquentBankRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(MachineRepositoryInterface::class, EloquentMachineRepository::class);
        $this->app->bind(JobRepositoryInterface::class, EloquentJobRepository::class);
        $this->app->bind(JobPhotoRepositoryInterface::class, EloquentJobPhotoRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(EmployeeDocumentRepositoryInterface::class, EloquentEmployeeDocumentRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
        $this->app->bind(SalaryPaymentRepositoryInterface::class, EloquentSalaryPaymentRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
        $this->app->bind(ClientAccountRepositoryInterface::class, EloquentClientAccountRepository::class);
        $this->app->bind(ClientMachineRepositoryInterface::class, EloquentClientMachineRepository::class);
        $this->app->bind(TicketProblemTypeRepositoryInterface::class, EloquentTicketProblemTypeRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
        $this->app->bind(SparePartRequestRepositoryInterface::class, EloquentSparePartRequestRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, EloquentVendorRepository::class);
    }
}
