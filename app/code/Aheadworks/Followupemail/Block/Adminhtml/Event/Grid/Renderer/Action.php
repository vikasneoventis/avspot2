<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $params = ['_current' => true, 'id' => $row->getData('id')];

        $content = $this->_getActionHtml($row, [
                'id' => 'status_change',
                'class' => 'btn action-status',
                'content' => $row->getData('active') ? __('Disable') : __('Enable'),
                'url' => $this->getUrl('*/*/changeStatus', $params),
                'message' => $this->_getMessage($row)
            ]) . $this->_getActionHtml($row, [
                'id' => 'delete',
                'class' => 'btn action-delete',
                'content' => "<span class=\"action-delete-icon\"></span>",
                'url' => $this->getUrl('*/*/delete', $params),
                'confirmation' => 'Are you sure?'
            ])
        ;
        return "<div class=\"btn-group\">{$content}</div>";
    }

    /**
     * @param array $options
     * @return string
     */
    protected function _getJsInitScripts(array $options)
    {
        $id = $options['id'];
        $options = \Zend_Json::encode(
            array_merge($options, [
                'gridSelector' => '#' . $options['gridContainerId']
            ])
        );
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksFueEventGridAction'], function($){
            $.awfue.eventGridAction({$options}, $('#{$id}'));
        });
    </script>
HTML;
    }

    protected function _getActionHtml(\Magento\Framework\DataObject $row, array $data)
    {
        $id = 'followup_event_grid_action_' . $data['id'] . '_' . $row->getData('id');
        $class = isset($data['class']) ? $data['class'] : '';
        $url = $data['url'];
        $content = $data['content'];
        $result = "<a id=\"{$id}\" class=\"{$class}\" data-url=\"{$url}\">{$content}</a>";
        return $result . $this->_getJsInitScripts(
            array_merge($data, [
                'id' => $id,
                'gridContainerId' => 'folloup_event_grid_' . $row->getData('event_type')
            ])
        );
    }

    protected function _getMessage(\Magento\Framework\DataObject $row)
    {
        $message = false;
        $className = 'success';
        if ($row->getId() ==  $this->coreRegistry->registry('followup_event_id_update_success')) {
            $message = __('Saved');
        } else if ($row->getId() ==  $this->coreRegistry->registry('followup_event_id_update_error')) {
            $message = __('Error');
            $className = 'error';
        }
        if ($message) {
            $message = "<span class=\"{$className}\">{$message}</span>";
        }
        return $message;
    }
}
