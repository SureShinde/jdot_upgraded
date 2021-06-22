<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-11-27T20:10:50+00:00
 * File:          app/code/Xtento/OrderExport/Controller/Adminhtml/Profile/ValidateXslTemplate.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

use Magento\Framework\App\Filesystem\DirectoryList;

class ValidateXslTemplate extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDir;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * ValidateXslTemplate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
        $this->systemTmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->exportFactory = $exportFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        $xslTemplate = $this->getRequest()->getPost('xsl_template', false);
        if (!$xslTemplate || empty($xslTemplate)) {
            $resultPage->setContents(__('No XSL Template supplied.'));
            return $resultPage;
        }
        $exportId = $this->getRequest()->getPost('increment_id', false);
        $profileId = $this->getRequest()->getPost('profile_id', false);
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('orderexport_profile');
        $this->registry->register('orderexport_profile', $profile);

        $profile->setXslTemplate($xslTemplate);
        // Export
        try {
            $output = "";
            $outputFiles = $this->exportFactory->create()->setProfile($profile)->testExport($exportId);
            if (!is_array($outputFiles)) {
                $output = $outputFiles;
            } else {
                $count = 0;
                foreach ($outputFiles as $filename => $outputFile) {
                    $count++;
                    if ($count > 1) {
                        $output .= "\n";
                    }
                    $output .= "File: " . $filename . "\n\n" . $outputFile;
                }
                // Store file so it can be served to the browser
                if ($this->getRequest()->getParam('serve_to_browser', false)) {
                    $serializedArray = @json_encode($outputFiles);
                    if (!$this->systemTmpDir->writeFile('profile_' . $profileId, $serializedArray)) {
                        $output .= __(
                            "\n\nAttention: Could not save temporary file to store test export for serving the file to the browser."
                        );
                    }
                }
            }
            $resultPage->setContents($output);
            return $resultPage;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (preg_match('/have been exported/', $e->getMessage())) {
                $errorMsg .= "\n\nIf the ID you tried to export exists in Magento, make sure you set up no filters in the 'Filters / Actions' tab that stop the object from being exported.";
            }
            $resultPage->setContents(__('Error: %1', $errorMsg));
        }

        return $resultPage;
    }
}
