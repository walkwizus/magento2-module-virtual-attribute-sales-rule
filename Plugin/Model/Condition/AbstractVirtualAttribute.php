<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Plugin\Model\Condition;

use Walkwizus\VirtualAttributeSalesRule\Model\VirtualAttributeProvider;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;
use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractVirtualAttribute
{
    /**
     * @var array
     */
    private array $virtualAttributes;

    /**
     * @param VirtualAttributeProvider $attributeProvider
     * @param Yesno $yesno
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private readonly VirtualAttributeProvider $attributeProvider,
        private readonly Yesno $yesno,
        private readonly ProductRepositoryInterface $productRepository
    ) {
        $this->virtualAttributes = $this->attributeProvider->get();
    }

    /**
     * @return string
     */
    abstract protected function getSection(): string;

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return void
     * @throws NoSuchEntityException
     */
    public function beforeValidate(AbstractCondition $subject, AbstractModel $model): void
    {
        if (!$this->hasVirtualAttributes()) {
            return;
        }

        $section = $this->getSection();

        $attributeCode = $subject->getAttribute();
        $attribute = $this->getAttribute($attributeCode);

        if ($attribute) {
            $valueObject = $this->getModelForValueRetrieval($model, $section, $attributeCode);
            $value = $this->getAttributeValue($attribute, $subject, $valueObject);

            $targetObject = $this->getModelForDataSetting($model, $section, $attributeCode);
            $targetObject->setData($attributeCode, $value);
        }
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractCondition $result
     * @return AbstractCondition
     */
    public function afterLoadAttributeOptions(AbstractCondition $subject, AbstractCondition $result): AbstractCondition
    {
        if (!$this->hasVirtualAttributes()) {
            return $result;
        }

        $section = $this->getSection();
        $attributes = $subject->getAttributeOption();

        /** @var VirtualAttributeInterface $attribute */
        foreach ($this->virtualAttributes[$section] as $code => $attribute) {
            $attributes[$code] = $attribute->getLabel() . __('(Virtual Attribute)');
        }

        asort($attributes);
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
        $attributeCode = $subject->getAttribute();
        $attribute = $this->getAttribute($attributeCode);

        if (!$attribute) {
            return $result ?? [];
        }

        $attributeType = $attribute->getType();

        if (in_array($attributeType, ['select', 'multiselect'])) {
            return $attribute->getOptionSource() ?? [
                [
                    'label' => get_class($attribute) . ' must implement getOptionSource() method',
                    'value' => 0
                ]
            ];
        }

        if ($attributeType === 'boolean') {
            return $this->yesno->toOptionArray();
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
        $attributeCode = $subject->getAttribute();
        $attribute = $this->getAttribute($attributeCode);

        if ($attribute) {
            return $attribute->getType();
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
        $attributeCode = $subject->getAttribute();
        $attribute = $this->getAttribute($attributeCode);

        if ($attribute && $attribute->getType() === 'date') {
            return true;
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
        $attributeCode = $subject->getAttribute();
        $attribute = $this->getAttribute($attributeCode);

        if (!$attribute) {
            return $result;
        }

        return match ($attribute->getType()) {
            'select', 'boolean' => 'select',
            'multiselect' => 'multiselect',
            'date' => 'date',
            default => 'text',
        };
    }

    /**
     * @return bool
     */
    private function hasVirtualAttributes(): bool
    {
        return isset($this->virtualAttributes[$this->getSection()]);
    }

    /**
     * @param string $attributeCode
     * @return VirtualAttributeInterface|null
     */
    private function getAttribute(string $attributeCode): ?VirtualAttributeInterface
    {
        if (!$this->hasVirtualAttributes()) {
            return null;
        }

        return $this->virtualAttributes[$this->getSection()][$attributeCode] ?? null;
    }

    /**
     * @param VirtualAttributeInterface $attribute
     * @param AbstractCondition $subject
     * @param AbstractModel $valueObject
     * @return mixed
     */
    private function getAttributeValue(
        VirtualAttributeInterface $attribute,
        AbstractCondition $subject,
        AbstractModel $valueObject
    ): mixed {
        if ($attribute->getType() === 'boolean') {
            return $attribute->getValue($subject, $valueObject) ? 1 : 0;
        }

        return $attribute->getValue($subject, $valueObject);
    }

    /**
     * @param AbstractModel $model
     * @param string $section
     * @param string $code
     * @return Item|Product|Address|AbstractModel
     * @throws NoSuchEntityException
     */
    private function getModelForValueRetrieval(AbstractModel $model, string $section, string $code): Item|Product|Address|AbstractModel
    {
        return $this->getModelBySection($model, $section, $code);
    }

    /**
     * @param AbstractModel $model
     * @param string $section
     * @param string $code
     * @return Item|Product|Address|AbstractModel
     * @throws NoSuchEntityException
     */
    private function getModelBySection(AbstractModel $model, string $section, string $code): Item|Product|Address|AbstractModel
    {
        if ($section == 'product' || $section == 'cart_item') {
            if (str_contains($code, 'quote_item_')) {
                return $model;
            }
            /** @var Product $product */
            $product = $model->getProduct();
            if (!$product instanceof Product) {
                $product = $this->productRepository->getById($model->getProductId());
            }
            return $product;
        } else if($section == 'cart') {
            if (!$model instanceof Quote) {
                return $model->getQuote();
            }
            return $model;
        }

        return $model;
    }

    /**
     * @param AbstractModel $model
     * @param string $section
     * @param string $code
     * @return Item|Product|Address|AbstractModel
     * @throws NoSuchEntityException
     */
    private function getModelForDataSetting(AbstractModel $model, string $section, string $code): Item|Product|Address|AbstractModel
    {
        if ($section == 'product') {
            if ($model instanceof Item) {
                /** @var Product $product */
                $product = $model->getProduct();
                if (!$product instanceof Product) {
                    $product = $this->productRepository->getById($model->getProductId());
                }
                return $product;
            }
        } else if ($section == 'cart') {
            if (!$model instanceof Address) {
                return $model->getQuote()->isVirtual()
                    ? $model->getQuote()->getBillingAddress()
                    : $model->getQuote()->getShippingAddress();
            }
            return $model;
        }

        return $this->getModelBySection($model, $section, $code);
    }
}
