<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class QueueActions
 */
class QueueActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['preview'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'followupemail_admin/queue/preview',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Preview'),
                    'callback' => ([
                        'target' => 'preview',
                        'provider' => 'preview'
                    ])
                ];
                $item[$this->getData('name')]['cancel'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'followupemail_admin/queue/cancel',
                        ['id' => $item['id'],]
                    ),
                    'label' => __('Cancel'),
                    'confirm' => [
                        'message' => __('Cancel the email?')
                    ]
                ];
                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'followupemail_admin/queue/delete',
                        ['id' => $item['id'],]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'message' => __('Delete the email?')
                    ]
                ];
                $item[$this->getData('name')]['send'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'followupemail_admin/queue/send',
                        ['id' => $item['id'],]
                    ),
                    'label' => __('Send now'),
                    'confirm' => [
                        'message' => __('Send the email immediately?')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
