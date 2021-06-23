<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\OgTags\Controller\Adminhtml\Category\Upload;

use Magefan\OgTags\Controller\Adminhtml\Upload\Image\Action;

/**
 * OgTags Category image upload controller
 */
class OgImg extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'magefan_og_image';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }
}
