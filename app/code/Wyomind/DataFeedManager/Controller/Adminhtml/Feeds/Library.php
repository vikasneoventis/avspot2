<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Load library action
 */
class Library extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        try {
            $tabOutput = '<div class="data-feed-library"><ul>';
            $tabOutput .=" <li>" . __("Online documentation : ") . "</li>";
            $tabOutput .=" <li><a target='_blank' href='https://www.wyomind.com/magento2/data-feed-manager-magento.html?section=documentation#Attribute%20specifications'>" . __("Attributes") . "</a></li>";
            $tabOutput .=" <li><a target='_blank' href='https://www.wyomind.com/magento2/data-feed-manager-magento.html?section=documentation#BASIC FUNCTIONS'>" . __("Functions") . "</a></li>";
            $tabOutput .=" <li><a target='_blank' href='https://www.wyomind.com/magento2/data-feed-manager-magento.html?section=documentation'>" . __("More documentation") . "</a></li>";
            $tabOutput .= '</ul>';
            $tabOutput .= '<span class="separator"></span>';

            $order = $this->_objectManager->create('\Magento\Framework\Api\SortOrder');
            $order->setField('frontend_label');
            $order->setDirection('ASC');

            $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
            $searchCriteria->setSortOrders([$order]);

            $attributesList = $this->attributeRepository->getList(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $searchCriteria);

            $contentOutput = "<div class='attr-list'><h3></h3>";
            $contentOutput .= "<table class='attr-list'>";
            $contentOutput .= "<thead><tr><th>" . __("Attribute label") . "</th><th>" . __("Attribute code") . "</th></tr></thead><tbody>";
            $i = 0;
            $empties = [];
            
            
            $collection = $this->variablesCollectionFactory->create();
            foreach ($collection as $variable) {
                $contentOutput .=  "<tr class='attribute-documentation_" . ($i % 2) . "'><td title='" . __("Load sample") . "' class='label' att_code='" . $variable->getName() . "'><span class='tv' style='display: inline-block;height: 18px;width: 18px;'></span>" . $variable->getName() . "</td><td class='pink'>{{product." . $variable->getName() . "}}";
                $i++;
            }
            
            
            foreach ($attributesList->getItems() as $attribute) {
                if (empty($attribute->getDefaultFrontendLabel())) {
                    $empties[] = $attribute;
                    continue;
                }
                if (!empty($attribute->getAttributeCode())) {
                    $contentOutput .= "<tr class='attribute-documentation_" . ($i % 2) . "'><td title='" . __("Load sample") . "' class='label load-attr-sample' att_code='" . $attribute->getAttributeCode() . "'><span class='tv closed' ></span>" . $attribute->getDefaultFrontendLabel() . "</td><td class='pink'>{{product." . $attribute->getAttributeCode() . "}}";
                    if (array_key_exists($attribute->getAttributeCode(), $this->parserHelper->attributeOptions)) {
                        $contentOutput .= "&nbsp;&nbsp;<a href='https://www.wyomind.com/magento2/data-feed-manager-magento.html?section=documentation#{{" . $attribute->getAttributeCode() . "}}' target='_blank'>" . __('More information') . "</a>";
                    }
                    $contentOutput .= "</td></tr><tr class='attribute-sample'><td colspan='2'></td></tr>";
                    $i++;
                }
            }
            foreach ($empties as $attribute) {
                if (!empty($attribute->getAttributeCode())) {
                    $contentOutput .= "<tr class='attribute-documentation_" . ($i % 2) . "'><td title='" . __("Load sample") . "' class='label load-attr-sample' att_code='" . $attribute->getAttributeCode() . "'><span class=' tv closed'></span><i>Empty label</i></td><td class='pink'>{{product." . $attribute->getAttributeCode() . "}}";
                    if (array_key_exists($attribute->getAttributeCode(), $this->parserHelper->attributeOptions)) {
                        $contentOutput .= "&nbsp;&nbsp;<a href='https://www.wyomind.com/magento2/data-feed-manager-magento.html?section=documentation#{{" . $attribute->getAttributeCode() . "}}' target='_blank'>" . __('More information') . "</a>";
                    }
                    $contentOutput .= "</td></tr><tr class='attribute-sample'><td colspan='2'></td></tr>";
                    $i++;
                }
            }
            $contentOutput .="</tbody></table>";
            $contentOutput .="</div>";
            $data = $tabOutput . $contentOutput;
        } catch (\Exception $e) {
            $data = $e->getMessage();
        }
        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($data));
    }
}
