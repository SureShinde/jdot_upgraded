<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

/**
 * Class Pages
 * @package Magefan\OgTags\Model\Config\Source
 */
class Pages implements ArrayInterface
{
    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => 'cms_page', 'label' => __('CMS Pages')],
            ['value' => 'category', 'label' => __('Category Pages')],
            ['value' => 'product', 'label' => __('Product Pages')],
            ['value' => 'blog_category', 'label' => __('Magefan Blog Category Pages')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}