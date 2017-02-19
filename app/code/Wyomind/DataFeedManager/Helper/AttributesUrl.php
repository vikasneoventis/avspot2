<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Helper;

/**
 * Attributes management
 */
class AttributesUrl extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    public function host($model, $options, $product, $reference)
    {
        unset($options);
        unset($product);
        unset($reference);
        return $model->getStoreUrl();
    }
    
    /**
     *
     * @param type $model
     * @param type $options
     * @param type $product
     * @param type $reference
     * @return string
     */
    public function url($model, $options, $product, $reference)
    {
        unset($options);
        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }

        if ($item->getRequest_path()) {
            // shortest
            if ($model->urlRewrites == \Wyomind\DataFeedManager\Model\Config\UrlRewrite::SHORTEST_URL) {
                $arr = explode(",", $item->getRequest_path());
                usort($arr, ['\Wyomind\DataFeedManager\Helper\Attributes', 'cmp']);
                $value = $model->storeUrl . array_pop($arr);
            } elseif ($model->urlRewrites == \Wyomind\DataFeedManager\Model\Config\UrlRewrite::LONGEST_URL) { // longest
                $arr = explode(",", $item->getRequest_path());
                usort($arr, ['\Wyomind\DataFeedManager\Helper\Attributes', 'cmp']);
                $value = $model->storeUrl . array_shift($arr);
            } else {
                $value = $model->storeUrl . $item->getRequest_path();
            }
        } else {
            $value = "";
        }
        return $value;
    }

    /**
     *
     * @param type $model
     * @param type $options
     * @param type $product
     * @param type $reference
     * @return string
     */
    public function uri($model, $options, $product, $reference)
    {
        unset($options);
        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }

        if ($item->getRequest_path()) {
            // shortest
            if ($model->urlRewrites == \Wyomind\DataFeedManager\Model\Config\UrlRewrite::SHORTEST_URL) {
                $arr = explode(",", $item->getRequest_path());
                usort($arr, ['\Wyomind\DataFeedManager\Helper\Attributes', 'cmp']);
                $value = array_pop($arr);
            } elseif ($model->urlRewrites == \Wyomind\DataFeedManager\Model\Config\UrlRewrite::LONGEST_URL) { // longest
                $arr = explode(",", $item->getRequest_path());
                usort($arr, ['\Wyomind\DataFeedManager\Helper\Attributes', 'cmp']);
                $value = array_shift($arr);
            } else {
                $value = $item->getRequest_path();
            }
        } else {
            $value = str_replace($model->storeUrl, '', $item->getProductUrl());
        }
        return $value;
    }
}
