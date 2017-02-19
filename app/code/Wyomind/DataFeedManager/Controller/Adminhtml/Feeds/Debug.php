<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Generate data feed action
 */
class Debug extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        $request = $this->getRequest();

        $id = $request->getParam('datafeedmanager_id');
        $limit = $request->getParam('limit');

        $model = $this->_objectManager->create('Wyomind\DataFeedManager\Model\Feeds');
        $model->limit = $limit;
        $model->debugEnabled = true;
        $model->logEnabled = true;

        $model->load($id);
        
        $resultRaw = $this->resultRawFactory->create();
        
        try {
            $model->generateFile($request);

            return $resultRaw->setContents($model->debugData);
        } catch (\Exception $e) {
            return $resultRaw->setContents(__('Unable to generate the data feed.') . '<br/><br/>' . $e->getMessage());
        }
        
        return $resultRaw->setContents("");
    }
}
