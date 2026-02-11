<?php

namespace App\Traits;

trait UsesTenantConnection
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootUsesTenantConnection()
    {
        static::addGlobalScope('tenant_connection', function ($builder) {
            $builder->getModel()->setConnection('tenant');
        });
    }

    /**
     * Get the connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return 'tenant';
    }
}
