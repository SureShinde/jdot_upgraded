<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Block\Cms;

use Magefan\OgTags\Block\AbstractOg;
use \Magento\Framework\View\Element\AbstractBlock;
use \Magento\Framework\View\Element\Context;

/**
 * Class Page
 * @package Magefan\OgTags\Block\Cms
 */
class Page extends AbstractOg
{
    /**
     * @var string
     */
    protected $entityType = 'cms_page';
}
