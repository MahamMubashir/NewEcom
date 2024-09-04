<?php

namespace NewEcom\ShopSmart\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Show search product grid in row class
 */
class ProductGrid implements OptionSourceInterface
{
    protected const ONE_PRODUCTS_ROW = '1';
    protected const TWO_PRODUCTS_ROW = '2';
    protected const THREE_PRODUCTS_ROW = '3';
    protected const FOUR_PRODUCTS_ROW = '4';
    protected const FIVE_PRODUCTS_ROW = '5';
    protected const SIX_PRODUCTS_ROW = '6';

    /**
     * @var array
     */
    public static array $layoutProductGrid = [
        self::ONE_PRODUCTS_ROW => '1 Product/Row',
        self::TWO_PRODUCTS_ROW => '2 Product/Row',
        self::THREE_PRODUCTS_ROW => '3 Product/Row',
        self::FOUR_PRODUCTS_ROW =>  '4 Product/Row',
        self::FIVE_PRODUCTS_ROW =>  '5 Product/Row',
        self::SIX_PRODUCTS_ROW =>   '6 Product/Row',
    ];

    /**
     * Returns the array
     *
     * @return string[]
     */
    public function getOptionArray(): array
    {
        return self::$layoutProductGrid;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        $opts = [];
        foreach (self::getOptionArray() as $key => $value) {

            $opts[] = [
                'label' => __($value),
                'value' => $key,
            ];
        }
        return $opts;
    }
}
