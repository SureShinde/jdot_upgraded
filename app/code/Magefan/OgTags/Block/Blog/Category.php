<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Block\Blog;

use Magefan\OgTags\Block\AbstractOg;

/**
 * Class Category
 * @package Magefan\OgTags\Block\Blog
 */
class Category extends AbstractOg
{
    /**
     * @var string
     */
    protected $entityType = 'blog_category';

    /**
     * @return string
     */
    public function getOgType()
    {
        return 'category';
    }
}
