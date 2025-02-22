<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote\Address;

class AddressVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @param AbstractModel $model
     * @return AbstractModel
     */
    protected function getModel(AbstractModel $model): AbstractModel
    {
        if ($model instanceof Address) {
            return $model;
        }

        return $model->getQuote()->isVirtual()
            ? $model->getQuote()->getBillingAddress()
            : $model->getQuote()->getShippingAddress();
    }
}
