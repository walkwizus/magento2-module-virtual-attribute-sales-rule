# Walkwizus Virtual Attribute Sales Rule for Magento 2

## Overview

The Virtual Attribute Sales Rule module for Magento 2 allows you to add virtual attributes to product and address conditions in Sales Rules. This module enhances the flexibility of your promotional rules by introducing custom, dynamic attributes that can be used in rule conditions.

## Features

- Add virtual attributes to product conditions in Sales Rules
- Add virtual attributes to address conditions in Sales Rules
- Easily extendable to add new virtual attributes
- Compatible with Magento 2.4.4 and later versions

## Requirements

- Magento 2.4.4 or later
- PHP 8.1 or later

## Installation

### Using Composer

1. In your Magento 2 root directory, run the following command:
   ```
   composer require walkwizus/magento2-module-virtual-attribute-sales-rule
   ```

2. Enable the module:
   ```
   bin/magento module:enable Walkwizus_VirtualAttributeSalesRule
   ```

3. Run the Magento setup upgrade:
   ```
   bin/magento setup:upgrade
   ```

## Usage

After installation, the module will automatically add the configured virtual attributes to the list of available conditions when creating or editing a Sales Rule in the Magento admin panel.

To use a virtual attribute in a rule:

1. Go to Marketing > Promotions > Cart Price Rules
2. Create a new rule or edit an existing one
3. In the Conditions / Actions tab, you will see the new virtual attributes available for selection

## Adding Custom Virtual Attributes

To add your own virtual attributes:

1. Create a new class that implements `Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface`
2. Implement the required methods: `getLabel()`, `getType()`, `getValue()`, and `getOptionSource()` (if applicable)
    - Note: The `getOptionSource()` method is only necessary for attributes of type 'select' or 'multiselect'
3. Add your new attribute to the `di.xml` file:

   ```xml
   <type name="Walkwizus\VirtualAttributeSalesRule\Model\ProductVirtualAttributeProvider">
        <arguments>
            <argument name="attributes" xsi:type="array">
                <item name="custom_product_attribute" xsi:type="object">Vendor\Module\Model\VirtualAttribute\CustomProductAttribute</item>
            </argument>
        </arguments>
   </type>
   ```

   Or for address attributes:

   ```xml
   <type name="Walkwizus\VirtualAttributeSalesRule\Model\AddressVirtualAttributeProvider">
        <arguments>
            <argument name="attributes" xsi:type="array">
                <item name="custom_address_attribute" xsi:type="object">Vendor\Module\Model\VirtualAttribute\CustomAddressAttribute</item>
            </argument>
        </arguments>
   </type>
   ```

### Types returned by getType()

The following table illustrates the different types that should be returned by the `getType()` method:

| Type        | Description                                      |
|-------------|--------------------------------------------------|
| string      | A text value                                     |
| numeric     | A number (integer or float)                      |
| date        | A date value                                     |
| select      | A single selection from a list of options        |
| boolean     | A true/false value                               |
| multiselect | Multiple selections from a list of options       |

Note: For 'select' and 'multiselect' types, you must implement the `getOptionSource()` method to provide the list of available options.

### Examples of Custom Virtual Attributes

Here are two examples of how to create custom virtual attributes:

1. Product Virtual Attribute Example (with select type):

```php
<?php

namespace Your\Module\Model\VirtualAttribute;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

class ProductCustomAttribute implements VirtualAttributeInterface
{
    public function getLabel(): Phrase|string
    {
        return __('Custom Product Attribute');
    }

    public function getType(): string
    {
        return 'select';
    }

    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        $product = $model->getProduct();
        
        // Your custom logic to determine the attribute value
        return 'option1';
    }

    public function getOptionSource(): array
    {
        return [
            ['value' => 'option1', 'label' => __('Option 1')],
            ['value' => 'option2', 'label' => __('Option 2')],
            ['value' => 'option3', 'label' => __('Option 3')],
        ];
    }
}
```

2. Address Virtual Attribute Example (with boolean type):

```php
<?php

namespace Your\Module\Model\VirtualAttribute;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

class AddressCustomAttribute implements VirtualAttributeInterface
{
    public function getLabel(): Phrase|string
    {
        return __('Custom Address Attribute');
    }

    public function getType(): string
    {
        return 'boolean';
    }

    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            $address = $model->getQuote()->isVirtual()
                ? $model->getQuote()->getBillingAddress()
                : $model->getQuote()->getShippingAddress();
        }
        
        // Your custom logic to determine the attribute value
        return $address->getCountryId() === 'US' ? 1 : 0;
    }

    // Note: getOptionSource() is not implemented here because it's not required for boolean type
}
```

In these examples, `ProductCustomAttribute` creates a custom select attribute for products and implements `getOptionSource()`, while `AddressCustomAttribute` creates a boolean attribute for addresses and doesn't need to implement `getOptionSource()`. You can customize these examples to fit your specific needs.

## Support

If you encounter any issues or have questions, please [open an issue](https://github.com/walkwizus/magento2-module-virtual-attribute-sales-rule/issues) on our GitHub repository.
