<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Dhllabel\Controller\Pdf;

class One extends \Infomodus\Dhllabel\Controller\Pdf
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $label_name = $this->getRequest()->getParam('label_name', null);
        if ($label_name !== null) {
            $path_dir = $this->_conf->getBaseDir('media') . 'dhllabel/label/';
            $data = \file_get_contents($path_dir . $label_name);
            if ($data !== false) {
                return $this->fileFactory->create(
                    'dhl_shipping_labels.pdf',
                    $data,
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } else {
                $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('*/*/'));
            }
        } else {
            $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('*/*/'));
        }
    }
}
