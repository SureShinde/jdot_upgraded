<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Setup;

use Infomodus\Dhllabel\Model\Address;
use Infomodus\Dhllabel\Model\AddressFactory;
use Infomodus\Dhllabel\Model\BoxesFactory;
use Infomodus\Dhllabel\Model\ResourceModel\Address\CollectionFactory as AddressCollection;
use Infomodus\Dhllabel\Model\ResourceModel\Boxes\CollectionFactory as BoxCollection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeData implements UpgradeDataInterface
{
    public $scopeConfig;
    public $address;
    public $addressCollection;
    public $configWriter;
    public $box;
    public $boxCollection;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        AddressFactory $address,
        AddressCollection $addressCollection,
        BoxesFactory $box,
        BoxCollection $boxCollection

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->address = $address;
        $this->addressCollection = $addressCollection;
        $this->configWriter = $configWriter;
        $this->box = $box;
        $this->boxCollection = $boxCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.8.1', '<')) {
            $this->addAddresses($setup);
            $this->addBoxes($setup);
        }

        $setup->endSetup();
    }

    public function addAddresses(ModuleDataSetupInterface $setup)
    {
        $collection = $this->addressCollection->create();
        if ($collection->getSize() == 0) {
            for ($i = 1; $i <= 20; $i++) {
                if ($this->scopeConfig->getValue('dhllabel/address_' . $i . '/addressname') != "") {
                    /**
                     * @var Address
                     */
                    $address = $this->address->create();
                    $address->setName($this->scopeConfig->getValue('dhllabel/address_' . $i . '/addressname'));
                    $address->setCompany($this->scopeConfig->getValue('dhllabel/address_' . $i . '/companyname'));
                    $address->setAttention($this->scopeConfig->getValue('dhllabel/address_' . $i . '/attentionname'));
                    $address->setPhone($this->scopeConfig->getValue('dhllabel/address_' . $i . '/phonenumber'));
                    $address->setStreetOne($this->scopeConfig->getValue('dhllabel/address_' . $i . '/addressline1'));
                    $address->setStreetTwo($this->scopeConfig->getValue('dhllabel/address_' . $i . '/addressline2'));
                    $address->setRoom($this->scopeConfig->getValue('dhllabel/address_' . $i . '/room'));
                    $address->setFloor($this->scopeConfig->getValue('dhllabel/address_' . $i . '/floor'));
                    $address->setCity($this->scopeConfig->getValue('dhllabel/address_' . $i . '/city'));
                    $address->setProvinceCode($this->scopeConfig->getValue('dhllabel/address_' . $i . '/stateprovincecode'));
                    $address->setUrbanization($this->scopeConfig->getValue('dhllabel/address_' . $i . '/urbanization'));
                    $address->setPostalCode($this->scopeConfig->getValue('dhllabel/address_' . $i . '/postalcode'));
                    $address->setCountry($this->scopeConfig->getValue('dhllabel/address_' . $i . '/countrycode'));
                    $address->setResidential($this->scopeConfig->getValue('dhllabel/address_' . $i . '/residential'));
                    $address->setPickupPoint($this->scopeConfig->getValue('dhllabel/address_' . $i . '/pickup_point'));
                    $address->save();

                    if ($this->scopeConfig->getValue('dhllabel/shipping/defaultshipper') == $i) {
                        $this->configWriter->save('dhllabel/shipping/defaultshipper', $address->getId());
                    }
                }
            }
        }
    }

    public function addBoxes(ModuleDataSetupInterface $setup)
    {
        $collection = $this->boxCollection->create();
        if ($collection->getSize() == 0) {
            for ($i = 1; $i <= 20; $i++) {
                if ($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/dimansionname') != "") {
                    /**
                     * @var Address
                     */
                    $box = $this->box->create();
                    $box->setEnable($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/enable'));
                    $box->setName($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/dimansionname'));
                    $box->setOuterWidth($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/outer_width'));
                    $box->setOuterLengths($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/outer_length'));
                    $box->setOuterHeight($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/outer_height'));
                    $box->setEmptyWeight($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/emptyWeight'));
                    $box->setWidth($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/width'));
                    $box->setLengths($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/length'));
                    $box->setHeight($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/height'));
                    $box->setMaxWeight($this->scopeConfig->getValue('dhllabel/dimansion_' . $i . '/maxWeight'));
                    $box->save();
                }
            }
        }
    }
}
