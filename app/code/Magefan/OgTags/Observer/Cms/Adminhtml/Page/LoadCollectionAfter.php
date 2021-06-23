<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Cms\Adminhtml\Page;

/**
 * Class LoadCollectionAfter
 * @package Magefan\OgTags\Observer\Cms\Adminhtml\Page
 */
class LoadCollectionAfter extends CmsPageLoadAfter
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $pages = $observer->getPageCollection();
        foreach ($pages as $page) {
            $this->load($page);
        }
    }
}
