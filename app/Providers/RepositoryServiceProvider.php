<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     * Add new bindings here when creating a new microservice.
     *
     * Format: Interface::class => Implementation::class
     */
    protected array $repositories = [
        // \App\Repositories\Contracts\ExampleRepositoryInterface::class => \App\Repositories\ExampleRepository::class,
        // \App\Repositories\Contracts\ReportRepositoryInterface::class => \App\Repositories\ReportRepository::class,
        \App\Repositories\Contracts\OrderRepositoryInterface::class => \App\Repositories\OrderRepository::class,
        \App\Repositories\Contracts\OrderItemRepositoryInterface::class => \App\Repositories\OrderItemRepository::class,
        \App\Repositories\Contracts\DiscountOrderRepositoryInterface::class => \App\Repositories\DiscountOrderRepository::class,
        \App\Repositories\Contracts\OrderStatusLogRepositoryInterface::class => \App\Repositories\OrderStatusLogRepository::class,
        \App\Repositories\Contracts\CustomerRepositoryInterface::class => \App\Repositories\CustomerRepository::class,
        \App\Repositories\Contracts\InventoryMovementRepositoryInterface::class => \App\Repositories\InventoryMovementRepository::class,
        \App\Repositories\Contracts\DiscountRepositoryInterface::class => \App\Repositories\DiscountRepository::class,
        \App\Repositories\Contracts\UserRepositoryInterface::class => \App\Repositories\UserRepository::class,
        \App\Repositories\Contracts\CategoryRepositoryInterface::class => \App\Repositories\CategoryRepository::class,
        \App\Repositories\Contracts\MenuItemRepositoryInterface::class => \App\Repositories\MenuItemRepository::class,

    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    public function boot(): void
    {
        //
    }
}
