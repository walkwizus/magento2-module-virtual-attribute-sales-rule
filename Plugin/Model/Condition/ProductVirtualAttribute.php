<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;

class ProductVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return void
     */
    public function beforeValidate(AbstractCondition $subject, AbstractModel $model): void
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            $product = $model->getProduct();
            $product->setData($code, $attribute->getValue($subject, $model));
        }
    }
}
