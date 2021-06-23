<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Blog\Adminhtml\Category;

use Magento\Framework\Event\ObserverInterface;
use Magefan\OgTags\Model\Blog\CategoryFactory as OgFactory;
use Magefan\OgTags\Observer\AbstractLoadAfter;

/**
 * Class BlogCategoryLoadAfter
 * @package Magefan\OgTags\Observer\Blog\Adminhtml\Category
 */
class BlogCategoryLoadAfter extends AbstractLoadAfter implements ObserverInterface
{
    /**
     * BlogCategoryLoadAfter constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OgFactory $ogFactory
     * @param string $field
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        OgFactory $ogFactory,
        $field = 'category_id'
    ) {
        parent::__construct($storeManager, $ogFactory, $field);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->load($observer->getBlogCategory());
    }
}
