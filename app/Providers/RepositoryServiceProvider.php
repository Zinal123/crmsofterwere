<?php

namespace App\Providers;

use App\Repositories\Contracts\BankRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
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
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentBankRepository;
use App\Repositories\Eloquent\EloquentDashboardRepository;
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
use App\Repositories\Eloquent\EloquentUserRepository;
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
    }
}
