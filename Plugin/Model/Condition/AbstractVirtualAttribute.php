<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Walkwizus\VirtualAttributeSalesRule\Model\VirtualAttributeProvider;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;
use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;

abstract class AbstractVirtualAttribute
{
    /**
     * @param VirtualAttributeProvider $attributeProvider
     * @param Yesno $yesno
     */
    public function __construct(
        protected readonly VirtualAttributeProvider $attributeProvider,
        protected readonly Yesno $yesno
    ) { }

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return void
     */
    abstract protected function beforeValidate(AbstractCondition $subject, AbstractModel $model): void;

    /**
     * @param AbstractCondition $subject
     * @param AbstractCondition $result
     * @return AbstractCondition
     */
    public function afterLoadAttributeOptions(AbstractCondition $subject, AbstractCondition $result): AbstractCondition
    {
        $attributes = $subject->getAttributeOption();

        foreach ($this->getAttributes() as $code => $attribute) {
            $attributes[$code] = $attribute->getLabel();
        }

        $subject->setAttributeOption($attributes);

        return $result;
    }

    /**
     * @param AbstractCondition $subject
     * @param $result
     * @return array
     */
    public function afterGetValueSelectOptions(AbstractCondition $subject, $result): array
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            if ($subject->getAttribute() == $code) {
                $attributeType = $attribute->getType();
                if (in_array($attributeType, ['select', 'multiselect'])) {
                    return $attribute->getOptionSource() ?? [['label' => get_class($attribute) . ' must implement getOptionSource() method', 'value' => 0]];
                }
                if ($attributeType == 'boolean') {
                    return $this->yesno->toOptionArray();
                }
            }
        }

        return $result ?? [];
    }

    /**
     * @param AbstractCondition $subject
     * @param $result
     * @return string
     */
    public function afterGetInputType(AbstractCondition $subject, $result): string
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            if ($subject->getAttribute() == $code) {
                return $attribute->getType();
            }
        }

        return $result;
    }

    /**
     * @param AbstractCondition $subject
     * @param $result
     * @return bool
     */
    public function afterGetExplicitApply(AbstractCondition $subject, $result): bool
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            if ($subject->getAttribute() == $code) {
                return $attribute->getType() == 'date' ? true : $result;
            }
        }

        return $result;
    }

    /**
     * @param AbstractCondition $subject
     * @param $result
     * @return string
     */
    public function afterGetValueElementType(AbstractCondition $subject, $result): string
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            if ($subject->getAttribute() == $code) {
                return match ($attribute->getType()) {
                    'select', 'boolean' => 'select',
                    'multiselect' => 'multiselect',
                    'date' => 'date',
                    default => 'text',
                };
            }
        }

        return $result;
    }

    /**
     * @return VirtualAttributeInterface[]
     */
    protected function getAttributes(): array
    {
        return $this->attributeProvider->get();
    }
}
