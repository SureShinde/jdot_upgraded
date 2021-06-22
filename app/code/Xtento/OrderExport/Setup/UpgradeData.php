<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-08-17T12:56:26+00:00
 * File:          app/code/Xtento/OrderExport/Setup/UpgradeData.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        $this->objectManager = $objectManager;
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Convert data from serialized to JSON format
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     *
     * @return void
     */
    private function convertSerializedDataToJson(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory */
        $fieldDataConverterFactory = $this->objectManager->create('\Magento\Framework\DB\FieldDataConverterFactory');
        /** @var \Magento\Framework\DB\FieldDataConverter $fieldDataConverter */
        $fieldDataConverter = $fieldDataConverterFactory->create('\Xtento\OrderExport\Test\SerializedToJsonDataConverter');
        $fieldsToConvert = ['conditions_serialized'];
        foreach ($fieldsToConvert as $fieldName) {
            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable('xtento_orderexport_profile'),
                'profile_id',
                $fieldName
            );
        }
    }
}