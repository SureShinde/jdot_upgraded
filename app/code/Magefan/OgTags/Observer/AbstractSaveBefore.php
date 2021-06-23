<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Observer;

/**
 * Class AbstractSaveBefore
 * @package Magefan\OgTags\Observer\Blog\Adminhtml\Category
 */
abstract class AbstractSaveBefore
{
    /**
     * @var strign
     */
    const BASE_MEDIA_PATH = 'magefan_og';

    /**
     * @var mixed
     */
    protected $ogFactory;

    /**
     * @var String
     */
    protected $field;

    /**
     * AbstractSaveBefore constructor.
     * @param null $ogFactory
     * @param null $field
     */
    public function __construct(
        $ogFactory,
        $field = null
    ) {
        $this->ogFactory = $ogFactory;
        $this->field = $field;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magento\Framework\Model\AbstractModel $model
    ) {

        $ogModel = $this->ogFactory->create();
        $ogModel->load($model->getId(), $this->field);
        $ogModel->setData($this->field, $model->getId());

        $ogModel->setMagefanOgTitle($model->getMagefanOgTitle());
        $ogModel->setMagefanOgDescription($model->getMagefanOgDescription());

        $file = $model->getMagefanOgImageUi();

        if (empty($file)) {
            $ogModel->setMagefanOgImage('');
        } elseif (is_array($file)) {
            if (!empty($file['delete'])) {
                $ogModel->setMagefanOgImage('');
            } else {
                if (isset($file[0]['name']) && isset($file[0]['tmp_name'])) {
                    $image = $file[0]['name'];
                    $ogModel->setMagefanOgImage(self::BASE_MEDIA_PATH . '/' . $image);
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $imageUploader = $objectManager->get(
                        \Magefan\OgTags\ImageUpload::class
                    );
                    $imageUploader->moveFileFromTmp($image);
                } else {
                    if (isset($file[0]['name'])) {
                        $ogModel->setMagefanOgImage($file[0]['name']);
                    }
                }
            }
        }

        $ogModel->save();
    }
}
