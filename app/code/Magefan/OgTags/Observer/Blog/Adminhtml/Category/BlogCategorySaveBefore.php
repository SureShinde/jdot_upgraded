<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Blog\Adminhtml\Category;

use Magento\Framework\Event\ObserverInterface;
use Magefan\OgTags\Model\Blog\CategoryFactory as OgFactory;
use Magefan\OgTags\Observer\AbstractSaveBefore;

/**
 * Class BlogCategorySaveBefore
 * @package Magefan\OgTags\Observer\Blog\Adminhtml\Category
 */
class BlogCategorySaveBefore extends AbstractSaveBefore implements ObserverInterface
{
    /**
     * BlogCategorySaveBefore constructor.
     * @param OgFactory $pageFactory
     * @param string $field
     */
    public function __construct(
        OgFactory $pageFactory,
        $field = 'category_id'
    ) {
        parent::__construct($pageFactory, $field);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->save($observer->getBlogCategory());
    }
}
