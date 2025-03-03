<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

class CartVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @return string
     */
    protected function getSection(): string
    {
        return 'cart';
    }
}
