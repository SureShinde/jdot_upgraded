<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Cms\Adminhtml\Page;

use Magento\Framework\Event\ObserverInterface;
use Magefan\OgTags\Model\Cms\PageFactory as OgFactory;
use Magefan\OgTags\Observer\AbstractLoadAfter;

/**
 * Class CmsPageLoadAfter
 * @package Magefan\OgTags\Observer\Cms\Adminhtml\Page
 */
class CmsPageLoadAfter extends AbstractLoadAfter implements ObserverInterface
{
    /**
     * CmsPageLoadAfter constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OgFactory $ogFactory
     * @param string $field
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        OgFactory $ogFactory,
        $field = 'page_id'
    ) {
        parent::__construct($storeManager, $ogFactory, $field);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->load($observer->getDataObject());
    }
}
