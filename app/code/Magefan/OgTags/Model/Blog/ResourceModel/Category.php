<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Blog\ResourceModel;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialization of table
     */
    protected function _construct()
    {
        $this->_init('magefan_og_blog_category', 'entity_id');
    }
}
