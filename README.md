# Walkwizus Virtual Attribute Sales Rule for Magento 2

## Overview

The Virtual Attribute Sales Rule module for Magento 2 allows you to add virtual attributes to cart price rules conditions. This extension enhances the flexibility of your promotional rules by introducing dynamic attributes that can be used in three key areas:

- Cart Attributes
- Cart Item Attributes
- Product Attributes

Create more sophisticated and targeted promotional rules without core code modifications.

## Features

- Add virtual attributes to Cart Attribute conditions section
- Add virtual attributes to Cart Item Attribute conditions section
- Add virtual attributes to Product Attribute conditions section
- Support for multiple attribute types: string, numeric, date, select, boolean, and multiselect

## Requirements

- Magento 2.4.4 or later
- PHP 8.1 or later

## Installation

### Using Composer (Recommended)

1. In your Magento 2 root directory, run the following command:
    ```bash
    composer require walkwizus/magento2-module-virtual-attribute-sales-rule
    ```

2. Enable the module:
    ```bash
    bin/magento module:enable Walkwizus_VirtualAttributeSalesRule
    ```

3. Run the Magento setup upgrade:
    ```bash
    bin/magento setup:upgrade
    ```

4. Compile the code (in production mode):
    ```bash
    bin/magento setup:di:compile
    ```

5. Clear the cache:
    ```bash
    bin/magento cache:clean
    ```

## Usage

### Step 1: Create Your Attribute Classes

Create new classes that implement `Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface`. Your classes must implement these methods:

- `getLabel()`: Returns the attribute's display name
- `getType()`: Returns the attribute's data type
- `getValue()`: Returns the attribute's value for a given model
- `getOptionSource()`: Returns available options (only for 'select' and 'multiselect' types)

#### Supported Attribute Types

The following types can be returned by the `getType()` method:

| Type        | Description                                      |
|-------------|--------------------------------------------------|
| string      | A text value                                     |
| numeric     | A number (integer or float)                      |
| date        | A date value                                     |
| select      | A single selection from a list of options        |
| boolean     | A true/false value                               |
| multiselect | Multiple selections from a list of options       |

**Note:** For 'select' and 'multiselect' types, you must implement the `getOptionSource()` method to provide the available options.

### Step 2: Add your attributes to di.xml

Create a `di.xml` file in your module's `etc` directory with the following structure:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Walkwizus\VirtualAttributeSalesRule\Model\VirtualAttributeProvider">
        <arguments>
            <argument name="attributes" xsi:type="array">
                <!-- Product attributes -->
                <item name="product" xsi:type="array">
                    <item name="attribute_code" xsi:type="object">Your\Module\Model\VirtualAttribute\YourProductAttribute</item>
                </item>
                <!-- Cart attributes -->
                <item name="cart" xsi:type="array">
                    <item name="attribute_code" xsi:type="object">Your\Module\Model\VirtualAttribute\YourCartAttribute</item>
                </item>
                <!-- Cart item attributes -->
                <item name="cart_item" xsi:type="array">
                    <item name="attribute_code" xsi:type="object">Your\Module\Model\VirtualAttribute\YourCartItemAttribute</item>
                </item>
            </argument>
        </arguments>
    </type>
</config>
```

Replace `Your\Module\Model\VirtualAttribute\YourAttribute` with your actual attribute class paths, and `attribute_code` with the code you want to use for each attribute.

**Important**: Make sure each `attribute_code` is unique and does not conflict with any existing Magento attribute codes, as this could cause unexpected behavior or errors in your rules.

### Implementation Examples

Here are examples of virtual attributes implementation for different section types:

#### Product Attribute Example

```php
<?php

declare(strict_types=1);

namespace Your\Module\Model\VirtualAttribute;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

class ProductIsDiscounted implements VirtualAttributeInterface
{
    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly TimezoneInterface $timezone
    ) { }

    /**
     * @return Phrase|string
     */
    public function getLabel(): Phrase|string
    {
        return __('Is Discounted Product');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'boolean';
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return mixed
     */
    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        /** @var \Magento\Catalog\Model\Product $model */
        $specialPrice = $model->getSpecialPrice();

        if ($specialPrice && $specialPrice < $model->getPrice()) {
            $from = $model->getSpecialFromDate();
            $to = $model->getSpecialToDate();

            $now = $this->timezone->date()->format('Y-m-d H:i:s');

            if ((!$from || $from <= $now) && (!$to || $to >= $now)) {
                return true;
            }
        }

        return false;
    }
}
```

#### Cart Attribute Example

```php
<?php

declare(strict_types=1);

namespace Your\Module\Model\VirtualAttribute;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;

class CartUpdatedAt implements VirtualAttributeInterface
{
    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly TimezoneInterface $timezone
    ) { }

    /**
     * @return Phrase|string
     */
    public function getLabel(): Phrase|string
    {
        return __('Minutes Since Last Update');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'numeric';
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return mixed
     */
    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        /** @var \Magento\Quote\Model\Quote $model */
        $now = $this->timezone->date();
        $updatedAt = $this->timezone->date($model->getUpdatedAt());

        $differenceInSeconds = $now->getTimestamp() - $updatedAt->getTimestamp();

        return (int) ($differenceInSeconds / 60);
    }
}
```

#### Cart Item Attribute Example

```php
<?php

declare(strict_types=1);

namespace Your\Module\Model\VirtualAttribute;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;

class CartItemWeight implements VirtualAttributeInterface
{
    /**
     * @return Phrase|string
     */
    public function getLabel(): Phrase|string
    {
        return __('Item Weight');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'numeric';
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return mixed
     */
    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        /** @var \Magento\Quote\Model\Quote\Item $model */
        return $model->getWeight();
    }
}
```

#### Select/Multiselect Example

```php
<?php

declare(strict_types=1);

namespace Your\Module\Model\VirtualAttribute;

use Walkwizus\VirtualAttributeSalesRule\Api\Data\VirtualAttributeInterface;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Model\AbstractModel;

class ProductCategory implements VirtualAttributeInterface
{
    /**
     * @return Phrase|string
     */
    public function getLabel(): Phrase|string
    {
        return __('Special Category');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'select';
    }

    /**
     * @param AbstractCondition $subject
     * @param AbstractModel $model
     * @return mixed
     */
    public function getValue(AbstractCondition $subject, AbstractModel $model): mixed
    {
        // Logic to determine which category value to return
        return 'category_a';
    }
    
    /**
     * @return array
     */
    public function getOptionSource(): array
    {
        return [
            ['value' => 'category_a', 'label' => __('Category A')],
            ['value' => 'category_b', 'label' => __('Category B')],
            ['value' => 'category_c', 'label' => __('Category C')]
        ];
    }
}
```

### Step 3: Use in Sales Rules

After installing your module and implementing your virtual attributes:

1. Go to **Marketing > Promotions > Cart Price Rules** in the Magento admin
2. Create or edit a rule
3. In the "Conditions" tab, you'll see your virtual attributes available in the respective sections:
    - Product attributes in the "Product attribute combination" condition
    - Cart attributes in the "Cart attribute" condition
    - Cart item attributes in the "Product attribute" condition (with "(Virtual Attribute)" suffix)

## Support

For issues and support, please create an issue on the [GitHub repository](https://github.com/walkwizus/magento2-module-virtual-attribute-sales-rule/issues).
