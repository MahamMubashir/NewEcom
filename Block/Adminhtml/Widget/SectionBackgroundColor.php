<?php

namespace NewEcom\ShopSmart\Block\Adminhtml\Widget;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Change Popup Background Color
 */
class SectionBackgroundColor extends Template implements BlockInterface
{

    /**
     * Prepare Widget Background Color Picker Element
     *
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $defaultColor = "#FFFFFF";
        $value = $element->getValue() ?: $defaultColor;
        $element->setData('after_element_html', '
                <input type="text"
                    style="height: 30px; width: 100px;"
                    value="' . $value . '"
                    id="section_background_color"
                    name="' . $element->getName() . '"
                >
                <script type="text/javascript">
                require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
                    $currElem = $("#section_background_color");
                    $currElem.css("backgroundColor", "'. $value .'");
                    $currElem.ColorPicker({
                        color: "' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            $currElem.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
                </script><style>.colorpicker {z-index: 10010}</style>');
        $element->setValue(null);
        return $element;
    }
}
