<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Plugin\Catalog\Category;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class DataProviderPlugin
{
    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * After getting category data
     * @param  \Magento\Catalog\Model\Category\DataProvider
     * @param  array
     * @return array
     */
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $category = $subject->getCurrentCategory();

        $imageField = 'magefan_og_image';

        if ($category) {
            $categoryData = $result[$category->getId()];
            if (isset($categoryData[$imageField])) {
                unset($categoryData[$imageField]);
                $categoryData[$imageField][0]['name'] = $category->getData($imageField);
                $categoryData[$imageField][0]['url'] = $this->getImageUrl($category->getData($imageField));
            }
            $result[$category->getId()] = $categoryData;
        }
        return $result;
    }

    /**
     * Retrieve image url
     * @param  string $image
     * @return string
     */
    protected function getImageUrl($image)
    {
        if (is_array($image) && isset($image[0]['url'])) {
            return $image[0]['url'];
        }
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) 
            . 'catalog/category/' . $image;
    }
}