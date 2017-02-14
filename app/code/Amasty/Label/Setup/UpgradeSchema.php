<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

namespace Amasty\Label\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Amasty\Label\Helper\Deploy
     */
    protected $pubDeployer;

    public function __construct(
        \Amasty\Label\Helper\Deploy $pubHelper
    ) {
        $this->pubDeployer = $pubHelper;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('am_label'),
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 1],
                'Label Status'
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('am_label'),
                'product_stock_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Low stock condition'
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->pubDeployer->deployPubFolder();
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->_createExampleLabels($setup);
        }
        $setup->endSetup();
    }

    protected function _createExampleLabels(SchemaSetupInterface $setup){
        $columns  = ['pos', 'is_single', 'name', 'stores', 'prod_txt', 'prod_img',
            'prod_image_size', 'prod_pos', 'prod_style', 'prod_text_style', 'cat_txt',
            'cat_img', 'cat_pos', 'cat_style', 'cat_image_size', 'cat_text_style',
            'is_new', 'is_sale', 'special_price_only', 'stock_less', 'stock_more',
            'stock_status', 'from_date', 'to_date', 'date_range_enabled', 'from_price',
            'to_price', 'by_price', 'price_range_enabled', 'customer_group_ids',
            'cond_serialize', 'customer_group_enabled', 'use_for_parent', 'status', 'product_stock_enabled'];

        $setup->getConnection()->insertArray(
            $setup->getTable('am_label'),
            $columns,
            [
                [
                    0, 0, 'New Label', '1', '', 'new-arrival.png', '', 0, 'margin: 5px;', '', '', 'new-green.png',
                    2, '', '', '', 2, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0', '0', 0, 0, '',
                    'a:6:{s:4:"type";s:48:"Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
                    0, 0, 0, 0
                ],
                [
                    2, 0, 'On Sale Label', '1', '', 'sale-red.png', '', 0, '', '', 'Sale', 'label-red.png', 2,
                    'font-size: 14px;color: #ffffff;', '', '', 0, 2, 1, 0, 0, 0, '0000-00-00 00:00:00',
                    '0000-00-00 00:00:00', 0, '0', '0', 0, 0, '',
                    'a:6:{s:4:"type";s:48:"Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
                    0, 0, 0, 0
                ]
            ]
        );
    }
}
