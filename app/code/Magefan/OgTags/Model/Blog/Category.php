<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Blog;

/**
 * Class Category
 * @package Magefan\OgTags\Model\Blog
 */
class Category extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialization of Resource Model
     */
    protected function _construct()
    {
        $this->_init(\Magefan\OgTags\Model\Blog\ResourceModel\Category::class);
    }
}
