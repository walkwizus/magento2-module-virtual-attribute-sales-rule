<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;

class ProductVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @param AbstractModel $model
     * @return AbstractModel
     */
    protected function getModel(AbstractModel $model): AbstractModel
    {
        return $model->getProduct();
    }
}
