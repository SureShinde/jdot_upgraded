<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer;

/**
 * Class AbstractLoadAfter
 * @package Magefan\OgTags\Observer\Blog\Adminhtml\Category
 */
abstract class AbstractLoadAfter
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var mixed
     */
    protected $ogFactory;

    /**
     * @var String
     */
    protected $field;

    /**
     * AbstractLoadAfter constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $ogFactory
     * @param null $field
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        $ogFactory,
        $field = null
    ) {
        $this->storeManager = $storeManager;
        $this->ogFactory = $ogFactory;
        $this->field = $field;
    }

    public function load(
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $ogModel = $this->ogFactory->create();
        $ogModel->load($model->getId(), $this->field);
        if ($ogModel->getId()) {
            $model->setData('magefan_og_title', $ogModel->getMagefanOgTitle());
            $model->setMagefanOgDescription($ogModel->getMagefanOgDescription());

            if ($ogImage = $ogModel->getMagefanOgImage()) {
                $model->setMagefanOgImage($ogModel->getMagefanOgImage());
                $model->setMagefanOgImageUrl(
                    $this->storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $ogImage

                );

                $model->setMagefanOgImageUi([[
                    'name' => $ogImage,
                    'url' => $model->getMagefanOgImageUrl()
                ]]);
            }

        }
    }
}
