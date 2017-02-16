<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eventConfig = $eventConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Aheadworks_Followupemail';
        $this->_controller = 'adminhtml_event';

        parent::_construct();

        if ($this->_authorization->isAllowed('Aheadworks_Followupemail::home_actions_edit')) {
            /* @var $model \Aheadworks\Followupemail\Model\Event */
            $model = $this->coreRegistry->registry('followup_event');
            if ($model && $model->getId()) {
                $this->buttonList->update('save', 'class_name', 'Magento\Backend\Block\Widget\Button\SplitButton');
                $this->buttonList->update('save', 'options', $this->_getSaveButtonOptions());
            }
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->update('delete', 'label', __('Delete'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
            $this->buttonList->remove('delete');
            $this->buttonList->remove('reset');
        }
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = '
            function toggleEditor() {
                if (tinyMCE.getInstanceById("post_content") == null) {
                    tinyMCE.execCommand("mceAddControl", false, "event_content");
                } else {
                    tinyMCE.execCommand("mceRemoveControl", false, "event_content");
                }
            };
            require([
                "jquery",
                "mage/mage"
            ], function($){
                var $form = $("#edit_form");
                    $form.mage("form", {
                    handlersData: {
                        saveAndNew: {
                            action: {
                                args: {back: "new"}
                            }
                        },
                    }
                });
            });';
        return parent::_prepareLayout();
    }

    protected function _getSaveButtonOptions()
    {
        return [
            [
                'label' => __('Save'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ],
                'default' => true
            ],
            [
                'label' => __('Save as new'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndNew', 'target' => '#edit_form']],
                ],
                'default' => false
            ]
        ];
    }

    protected function _afterToHtml($html)
    {
        return parent::_afterToHtml($html . $this->_getJsInitScripts());
    }

    protected function _getJsInitScripts()
    {
        $refreshUrls = [];
        $model = $this->coreRegistry->registry('followup_event');
        foreach ($this->eventConfig->get() as $type => $data) {
            $urlParams = ['event_type' => $type];
            if ($model->getId()) {
                $urlParams['id'] = $model->getId();
            }
            $refreshUrls[$type] = $this->getUrl('*/*/*', $urlParams);
        }
        $options = \Zend_Json::encode([
            'typeChooserSelector' => '#event_event_type',
            'previewBtnSelector' => '#preview_button',
            'sendTestBtnSelector' => '#sendtest_button',
            'previewUrl' => $this->getUrl('*/*/preview'),
            'sendTestUrl' => $this->getUrl('*/*/sendtest'),
            'refreshUrls' => $refreshUrls
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksFueEventForm'], function($){
            $(document).ready(function() {
                $.awfue.eventForm({$options}, $('#edit_form'));
            });
        });
    </script>
HTML;
    }
}
