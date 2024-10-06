<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Model;

class AddressVirtualAttributeProvider extends VirtualAttributeProvider
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
