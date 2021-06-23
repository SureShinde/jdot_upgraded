<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Controller\Adminhtml\Blog\Upload;

use Magefan\OgTags\Controller\Adminhtml\Upload\Image\Action;

/**
 * OgTags Blog Category image upload controller
 */
class OgImg extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'magefan_og_image_ui';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_Blog::category');
    }
}
