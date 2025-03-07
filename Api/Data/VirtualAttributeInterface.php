<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Api\Data;

use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;

interface VirtualAttributeInterface
{
    /**
     * @return Phrase|string
     */
    public function getLabel(): Phrase|string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return mixed
     */
    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed;
}
