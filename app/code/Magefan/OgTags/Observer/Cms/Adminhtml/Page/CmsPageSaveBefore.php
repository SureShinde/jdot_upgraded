<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Cms\Adminhtml\Page;

use Magento\Framework\Event\ObserverInterface;
use Magefan\OgTags\Model\Cms\PageFactory as OgFactory;
use Magefan\OgTags\Observer\AbstractSaveBefore;

/**
 * Class CmsPageSaveBefore
 * @package Magefan\OgTags\Observer\Cms\Adminhtml\Page
 */
class CmsPageSaveBefore extends AbstractSaveBefore implements ObserverInterface
{
    /**
     * CmsPageSaveBefore constructor.
     * @param OgFactory $pageFactory
     * @param string $field
     */
    public function __construct(
        OgFactory $pageFactory,
        $field = 'page_id'
    ) {
        parent::__construct($pageFactory, $field);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->save($observer->getObject());
    }
}
