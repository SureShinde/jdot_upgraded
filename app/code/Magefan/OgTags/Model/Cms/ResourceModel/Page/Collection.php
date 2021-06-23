<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Cms\ResourceModel\Page;

/**
 * Class Collection
 * @package Magefan\OgTags\Model\Cms\ResourceModel\Page
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialization of collection
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magefan\OgTags\Model\Cms\Page::class,
            \Magefan\OgTags\Model\Cms\ResourceModel\Page::class
        );
    }
}
