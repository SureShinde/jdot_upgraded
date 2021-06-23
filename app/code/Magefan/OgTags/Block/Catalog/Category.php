<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Block\Catalog;

use Magefan\OgTags\Block\AbstractOg;

/**
 * Class Category
 * @package Magefan\OgTags\Block\Catalog
 */
class Category extends AbstractOg
{
    /**
     * @var string
     */
    protected $entityType = 'category';

    /**
     * @return string
     */
    public function getOgType()
    {
        return 'category';
    }

    /**
     * @return mixed
     */
    public function getOgImage()
    {
        $ogImage = $this->getEntity()->getMagefanOgImage() ?: $this->getEntity()->getImage();
        if ($ogImage) {
            return  $this->getMediaUrl('catalog/category/' . $ogImage);
        } else {
            return $this->getDefaultOgImage();
        }
    }

    /**
     * @return mixed
     */
    public function getOgImagePath()
    {
        $ogImage = $this->getEntity()->getMagefanOgImage() ?: $this->getEntity()->getImage();
        if ($ogImage) {
            return  $this->getMediaPath('catalog/category/' . $ogImage);
        } else {
            return $this->getDefaultOgImagePath();
        }
    }
}
