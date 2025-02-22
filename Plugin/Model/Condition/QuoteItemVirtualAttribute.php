<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Magento\Framework\Model\AbstractModel;

class QuoteItemVirtualAttribute extends AbstractVirtualAttribute
{
    /**
     * @var string|null
     */
    protected ?string $attributeCodePrefix = 'quote_item';

    /**
     * @param AbstractModel $model
     * @return AbstractModel
     */
    protected function getModel(AbstractModel $model): AbstractModel
    {
        $product = $model->getProduct();
        return $model->getQuote()->getItemByProduct($product);
    }
}
