<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * All report service bindings.
     * Add new bindings here when creating a new report service.
     *
     * Note: Report services are registered as singletons because they are stateless
     * and can be safely shared across requests for better performance.
     */
    protected array $reportServices = [
        \App\Services\Reports\SalesReportService::class,
        \App\Services\Reports\PopularItemsReportService::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register Report Services as singletons
        // These services are stateless and can be safely shared across requests
        foreach ($this->reportServices as $service) {
            $this->app->singleton($service);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
