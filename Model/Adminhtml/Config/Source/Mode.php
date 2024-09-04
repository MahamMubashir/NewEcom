<?php

namespace NewEcom\ShopSmart\Model\Adminhtml\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Check Mode URL
 */
class Mode implements OptionSourceInterface
{

    public const STAGING_URL = 'https://newecomenginestaging.azurewebsites.net/';
    public const PRODUCTION_URL = 'production';

    /**
     * @var array
     */
    public static array $checkModeURL = [
        self::STAGING_URL => 'Staging',
        self::PRODUCTION_URL => 'Production'
    ];

    /**
     * Returns the array for available modes
     *
     * @return string[]
     */
    public function getOptionArray(): array
    {
        return self::$checkModeURL;
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
