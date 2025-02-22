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
     * @var string|null
     */
    protected ?string $attributeCodePrefix = null;

    /**
     * @param VirtualAttributeProvider $attributeProvider
     * @param Yesno $yesno
     */
    public function __construct(
        protected readonly VirtualAttributeProvider $attributeProvider,
        protected readonly Yesno $yesno
    ) { }

    /**
     * @param AbstractModel $model
     * @return AbstractModel
     */
    abstract protected function getModel(AbstractModel $model): AbstractModel;

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return void
     */
    public function beforeValidate(AbstractCondition $subject, AbstractModel $model): void
    {
        foreach ($this->getAttributes() as $code => $attribute) {
            $object = $this->getModel($model);
            if ($attribute->getType() == 'boolean') {
                $value = $attribute->getValue($subject, $model) ? 1 : 0;
            } else {
                $value = $attribute->getValue($subject, $model);
            }

            $prefixedCode = $this->getPrefixedCode($code);
            $object->setData($prefixedCode, $value);
        }
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractCondition $result
     * @return AbstractCondition
     */
    public function afterLoadAttributeOptions(AbstractCondition $subject, AbstractCondition $result): AbstractCondition
    {
        $attributes = $subject->getAttributeOption();

        foreach ($this->getAttributes() as $code => $attribute) {
            $prefixedCode = $this->getPrefixedCode($code);
            $attributes[$prefixedCode] = $attribute->getLabel();
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
            $prefixedCode = $this->getPrefixedCode($code);
            if ($subject->getAttribute() == $prefixedCode) {
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
            $prefixedCode = $this->getPrefixedCode($code);
            if ($subject->getAttribute() == $prefixedCode) {
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
            $prefixedCode = $this->getPrefixedCode($code);
            if ($subject->getAttribute() == $prefixedCode) {
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
            $prefixedCode = $this->getPrefixedCode($code);
            if ($subject->getAttribute() == $prefixedCode) {
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
     * @param string $code
     * @return string
     */
    private function getPrefixedCode(string $code): string
    {
        if ($this->attributeCodePrefix !== null) {
            return $this->attributeCodePrefix . '_' . $code;
        }

        return $code;
    }

    /**
     * @return VirtualAttributeInterface[]
     */
    private function getAttributes(): array
    {
        return $this->attributeProvider->get();
    }
}
