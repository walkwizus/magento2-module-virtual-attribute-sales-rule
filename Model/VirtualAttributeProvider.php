<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Model;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;

class VirtualAttributeProvider
{
    /**
     * @param array $attributes
     */
    public function __construct(private readonly array $attributes = []) {}

    /**
     * @return VirtualAttributeInterface[]
     */
    public function get(): array
    {
        return $this->attributes;
    }
}
