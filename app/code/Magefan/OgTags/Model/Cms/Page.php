<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Cms;

/**
 * Class Page
 * @package Magefan\OgTags\Model\Cms
 */
class Page extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialization of Resource Model
     */
    protected function _construct()
    {
        $this->_init(\Magefan\OgTags\Model\Cms\ResourceModel\Page::class);
    }
}
