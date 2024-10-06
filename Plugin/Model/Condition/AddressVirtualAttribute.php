<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote\Address;

class AddressVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return void
     */
    public function beforeValidate(AbstractCondition $subject, AbstractModel $model): void
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            $address = $this->getAddress($model);
            $address->setData($code, $attribute->getValue($subject, $model));
        }
    }

    /**
     * @param AbstractModel $model
     * @return Address
     */
    private function getAddress(AbstractModel $model): Address
    {
        if ($model instanceof Address) {
            return $model;
        }

        return $model->getQuote()->isVirtual()
            ? $model->getQuote()->getBillingAddress()
            : $model->getQuote()->getShippingAddress();
    }
}
