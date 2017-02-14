<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Plugin\Catalog\Product;

class Label
{
    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Amasty\Label\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $product = $subject->getProduct();

        if ($product) {
            $result .= $this->_helper->renderProductLabel($product , 'category');
        }

        return $result;
    }
}
