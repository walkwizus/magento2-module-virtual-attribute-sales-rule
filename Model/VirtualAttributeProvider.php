<?php

declare(strict_types=1);

namespace Walkwizus\VirtualAttributeSalesRule\Model;

class VirtualAttributeProvider
{
    /**
     * @param array $attributes
     */
    public function __construct(private readonly array $attributes = []) {}

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->formatAttributes();
    }

    /**
     * @return array
     */
    private function formatAttributes(): array
    {
        $data = [];
        foreach ($this->attributes as $section => $attributes) {
            foreach ($attributes as $code => $attribute) {
                if ($section == 'cart_item') {
                    $code = 'quote_item_' . $code;
                }
                $data[$section == 'cart_item' ? 'product' : $section][$code] = $attribute;
            }
        }

        return $data;
    }
}
