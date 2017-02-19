<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Helper;

class Parser extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_regexAttributeCalls = "
            /(
                (?<attribute_calls>{{.*}})
              |
                (?<start_php>\<\?php)
              |
                (?<end_php>\?\>)
              )/xsU";
    protected $_regexInsideAttributeCalls = "
        /(?<pattern>
            \s*
            (?<object>[a-z]+)
            \.
            (?<property>[a-z0-9_]+)
            \s?
            (?:
                \s*
                (?<parameters>[^\|}]*)
            )?
            \s*
            (?<or>\|)?
        )/sx";
    protected $_regexParameters = '/(?<name>\b\w+\b)\s*=\s*(?<value>"[^"]*"|\'[^\']*\'|[^"\'<>\s]+)/';
    protected $_regexIf = "
                /\s*
                (
                    (
                        (?<object>(product|parent|configurable|bundle|grouped))
                        \.
                        (?<property>[a-z0-9_]+)
                    ) 
                    |
                    (?<alias>[^\.]+)
                )
                \s?
                (?<condition>(<|>|<=|>=|==|!=))
                \s*
                (?<value>[^=]+)
                \s*
                /xs";

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

        $priceAttributesOptions = [
            'currency', 'vat_rate'
        ];

        $this->attributeOptions = [
            'price' => $priceAttributesOptions,
            'normal_price' => $priceAttributesOptions,
            'final_price' => $priceAttributesOptions,
            'special_price' => $priceAttributesOptions,
            'price_rules' => $priceAttributesOptions,
            'has_special_price' => ['yes', 'no'],
            'min_price' => $priceAttributesOptions,
            'max_price' => $priceAttributesOptions,
            'image_link' => ['index'],
            'qty' => ['float'],
            'is_in_stock' => ['in_stock', 'out_of_stock', 'backorderable'],
            'categories' => ['nb_path', 'from_level', 'nb_cat_in_each_path', 'path_separator', 'cat_separator'],
            'category_mapping' => ['index'],
            'SC:EAN' => ['index'],
            'SC:URL' => ['html', 'index'],
        ];
    }

    /**
     * @param string $template
     * @return array
     */
    public function extractAttributeCalls($template)
    {
        $result = [];
        $matches = [];
        // first step : get all occurrences of {{....}}
        preg_match_all($this->_regexAttributeCalls, $template, $matches);
        
        $attributeCalls = $matches['attribute_calls'];
        $startPhp = $matches['start_php'];
        $endPhp = $matches['end_php'];
        $count = max([
            count($attributeCalls),
            count($startPhp),
            count($endPhp)
        ]);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($attributeCalls[$i]) || $attributeCalls[$i] == "") {
                if (isset($startPhp[$i]) && $startPhp[$i] != "") {
                    $attributeCalls[$i] = $startPhp[$i];
                } elseif (isset($endPhp[$i]) && $endPhp[$i] != "") {
                    $attributeCalls[$i] = $endPhp[$i];
                }
            }
        }
        
        $inPhp = false;
        
        $call = 0;
        
        foreach ($attributeCalls as $attributeCall) {
            $call++;
            if ($attributeCall == "<?php") {
                $inPhp = true;
                continue;
            } elseif ($attributeCall == "?>") {
                $inPhp = false;
                continue;
            }
            $matchesTwo = [];
            // second step : parse the content of {{....}}
            
            $into = str_replace("\\\"", "\"", $attributeCall);
            $into = str_replace("\\\\", "\\", $into);
            preg_match_all($this->_regexInsideAttributeCalls, $into, $matchesTwo);
            $objects = $matchesTwo['object'];
            $properties = $matchesTwo['property'];
            $parameters = $matchesTwo['parameters'];
            $ors = $matchesTwo['or'];
            $i = 0;
            if ($inPhp) {
                $attributeCall = "PHP_".$attributeCall;
            }
            $attributeCall = $call."_".$attributeCall;
            $result[$attributeCall] = [];
            foreach ($objects as $object) {
                $tmp = [];
                $tmp['object'] = $object;
                $tmp['property'] = $properties[$i];
                $parametersTmp = trim($parameters[$i]);
                if ($parametersTmp != "") {
                    $matchesThree = [];
                    // third step : parse the parameters value="xxx"
                    preg_match_all($this->_regexParameters, $parametersTmp, $matchesThree);
                    $names = $matchesThree['name'];
                    $values = $matchesThree['value'];
                    $j = 0;
                    
                    foreach ($names as $name) {
                        if ($name == "if") {
                            $ifMatches = [];
                            preg_match_all($this->_regexIf, trim($values[$j], "\"'"), $ifMatches);
                            if (isset($ifMatches['object'])) {
                                if (isset($ifMatches['alias']) && $ifMatches['alias'][0] != "") {
                                    $if = [
                                        'alias' => $ifMatches['alias'][0]
                                    ];
                                } else {
                                    $if = [
                                        'object' => $ifMatches['object'][0],
                                        'property' => $ifMatches['property'][0]
                                    ];
                                }
                                $if['condition'] = $ifMatches['condition'][0];
                                $if['value'] = $ifMatches['value'][0];
                                if (!isset($tmp['parameters']["if"])) {
                                    $tmp['parameters']["if"] = [$if];
                                } else {
                                    $tmp['parameters']["if"][] = $if;
                                }
                            }
                        } else {
                            $tmp['parameters'][$name] = trim($values[$j], "\"'");
                        }
                        $j++;
                    }
                }
                if (!isset($tmp['parameters'])) {
                    $tmp['parameters'] = "";
                }
                $tmp['or'] = $ors[$i] == "|";
                $result[$attributeCall][] = $tmp;
                $i++;
            }
        }
        return $result;
    }
}
