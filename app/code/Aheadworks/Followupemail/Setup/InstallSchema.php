<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'aw_followup_event'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_followup_event'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Event Id'
            )
            ->addColumn(
                'event_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Event Type'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subject'
            )
            ->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Content'
            )
            ->addColumn(
                'email_send_days',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Email Send Days'
            )
            ->addColumn(
                'email_send_hours',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Email Send Hours'
            )
            ->addColumn(
                'email_send_minutes',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Email Send Minutes'
            )
            ->addColumn(
                'store_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1000,
                ['nullable' => false],
                'Store Ids'
            )
            ->addColumn(
                'product_type_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product Type IDs'
            )
            ->addColumn(
                'cart_conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Cart Conditions Serialized'
            )
            ->addColumn(
                'product_conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Product Conditions Serialized'
            )
            ->addColumn(
                'customer_groups',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Groups'
            )
            ->addColumn(
                'order_statuses',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order Statuses'
            )
            ->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                'Is Active'
            )
            ->setComment('Followupemail Event');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_followup_event_history'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_followup_event_history'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Event History Id'
            )
            ->addColumn(
                'reference_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Reference Id'
            )
            ->addColumn(
                'event_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Event Type'
            )
            ->addColumn(
                'event_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Event Data'
            )
            ->addColumn(
                'triggered_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Triggered At'
            )
            ->addColumn(
                'processed',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Processed'
            )
            ->setComment('Followupemail Event History');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'aw_followup_queue'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_followup_queue'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Queue Id'
            )
            ->addColumn(
                'event_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Event Type'
            )
            ->addColumn(
                'event_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Event Id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'scheduled_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Scheduled At'
            )
            ->addColumn(
                'sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Sent At'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'recipient_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Recipient Name'
            )
            ->addColumn(
                'recipient_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Recipient Email'
            )
            ->addColumn(
                'event_history_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Event History Id'
            )
            ->addColumn(
                'saved_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Saved Subject'
            )
            ->addColumn(
                'saved_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Saved Content'
            )
            ->setComment('Followupemail Queue');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_followup_config'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_followup_config'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Parameter Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Parameter Name'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Value'
            )
            ->setComment('Followupemail Config');
        $installer->getConnection()->createTable($table);

        $installer->getConnection()
            ->insert($installer->getTable('aw_followup_config'),
                [
                    'name' => \Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME,
                    'value' => time()
                ]
            );
        $installer->getConnection()
            ->insert($installer->getTable('aw_followup_config'),
                [
                    'name' => \Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME_DAILY,
                    'value' => time() - 3600
                ]
            );

        $installer->endSetup();

    }
}
