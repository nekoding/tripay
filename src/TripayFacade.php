<?php

namespace Nekoding\Tripay;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nekoding\Tripay\Skeleton\SkeletonClass
 */
class TripayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tripay';
    }
}
