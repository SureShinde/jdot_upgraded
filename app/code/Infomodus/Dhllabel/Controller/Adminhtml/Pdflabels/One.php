<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Pdflabels;

class One extends \Infomodus\Dhllabel\Controller\Adminhtml\Pdflabels
{
    protected $_conf;
    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Infomodus\Dhllabel\Helper\Config $conf
    ) {
        $this->_conf = $conf;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory);
    }

    public function execute()
    {
        $label_name = $this->getRequest()->getParam('label_name', null);
        if ($label_name !== null) {
            $path_dir = $this->_conf->getBaseDir('media') . 'dhllabel/label/';
            if (file_exists($path_dir.$label_name)) {
                $data = file_get_contents($path_dir . $label_name);
                if ($data !== false) {
                    return $this->fileFactory->create(
                        'dhl_shipping_labels.pdf',
                        $data,
                        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                        'application/pdf',
                        strlen($data)
                    );
                }
            }
        }
    }
}
