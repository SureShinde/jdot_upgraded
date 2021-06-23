<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;

class CategorySaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     * @param \Magento\Framework\App\Request\Http  $request
     */
    public function __construct(
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->imageUploader = $imageUploader;
        $this->request = $request;
    }

    /**
     * Before save catalog category
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventImage = $this->request->getParam('magefan_og_image');
        $category = $observer->getEvent()->getCategory();
        if ($eventImage && is_array($eventImage) && isset($eventImage[0]['tmp_name'])) {
            $category->setData('magefan_og_image', $eventImage[0]['name']);
            $this->imageUploader->moveFileFromTmp($eventImage[0]['name']);
        } elseif (empty($eventImage)) {
            $category->setData('magefan_og_image', '');
        }
    }
}
