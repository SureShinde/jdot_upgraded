<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Cms\ResourceModel;

/**
 * Class Page
 * @package Magefan\OgTags\Model\Cms\ResourceModel
 */
class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialization of table
     */
    protected function _construct()
    {
        $this->_init('magefan_og_cms_page', 'entity_id');
    }
}
