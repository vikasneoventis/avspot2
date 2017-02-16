<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Edit\Tab;

class Info extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Aheadworks\Followupemail\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var Renderer\SendEmail
     */
    protected $sendEmailRenderer;

    /**
     * @var \Aheadworks\Followupemail\Model\Source\Event\Type
     */
    protected $typeSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Aheadworks\Followupemail\Model\Wysiwyg\Config $wysiwygConfig
     * @param Renderer\SendEmail $sendEmailRenderer
     * @param \Aheadworks\Followupemail\Model\Source\Event\Type $typeSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Followupemail\Model\Wysiwyg\Config $wysiwygConfig,
        Renderer\SendEmail $sendEmailRenderer,
        \Aheadworks\Followupemail\Model\Source\Event\Type $typeSource,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->sendEmailRenderer = $sendEmailRenderer;
        $this->typeSource = $typeSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Aheadworks\Followupemail\Model\Event */
        $model = $this->_coreRegistry->registry('followup_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('event_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Specify event type, email name, subject and content')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'event_type',
            'select',
            [
                'label' => __('Event'),
                'title' => __('Event'),
                'name' => 'event_type',
                'required' => true,
                'options' => $this->_getEventTypeOptions()
            ]
        );
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'subject',
            'text',
            [
                'name' => 'subject',
                'label' => __('Subject'),
                'title' => __('Subject'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'config' => $this->wysiwygConfig->getConfig(
                        [
                            'event_type' => $model->getEventType(),
                            'tab_id' => $this->getTabId(),
                            'cleanup' => false,
                            'height' => 550
                        ]),
                'after_element_html' => $this->_getEmailContentAfterHtml(),
                'required' => false
            ]
        );

        $fieldset = $form->addFieldset('send_fieldset', ['legend' => __('Specify when email should be sent')]);
        $field = $fieldset->addField(
            'email_send_days',
            'text',
            [
                'name' => 'email_send_days',
                'label' => __('Send email'),
                'title' => __('Send email'),
                'class' => 'validate-number',
                'days_only' => $model->getEventType() == 'customer_birthday',
                'after_label' => $model->getEventType() == 'customer_birthday' ? __('before') : __('later')
            ]
        )->setRenderer($this->sendEmailRenderer);
        $field->setData('email_send_hours', $model->getData('email_send_hours'));
        $field->setData('email_send_minutes', $model->getData('email_send_minutes'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getEventTypeOptions()
    {
        $options = [];
        foreach ($this->typeSource->toOptionArray() as $_option) {
            $options[$_option['value']] = __($_option['label']);
        }
        return $options;
    }

    protected function _getEmailContentAfterHtml()
    {
        $previewButton = $this->_layout->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'id' => 'preview_button',
                    'label' => __('Preview'),
                    'title' => __('Preview')
                ]
            )
        ;
        $sendTestButton = $this->_layout->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'id' => 'sendtest_button',
                    'label' => __('Send Test Email'),
                    'title' => __('Send Test Email')
                ]
            )
        ;
        $html = $previewButton->toHtml() . $sendTestButton->toHtml();
        return <<<HTML
        <div style="margin-top: 10px;">{$html}</div>
HTML;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Email Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Email Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
