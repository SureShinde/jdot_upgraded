<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Model;

//use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magedelight\Storepickup\Api\Data\StorelocatorInterface;
use Magedelight\Storepickup\Model\Source\IsActive;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\App\ResourceConnection;

class Storelocator extends AbstractModel
{

    /**
     * status enabled
     *
     * @var int
     */
    const STATUS_ENABLED = 1;

    /**
     * status disabled
     *
     * @var int
     */
    const STATUS_DISABLED = 0;

    /**
     * @var IsActive
     */
    protected $statusList;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Filesystem instance.
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    
    /**
     *
     * @var resourceConnection 
     */
    protected $_resourceConnection;

    /**
     *
     * @param FilterManager $filter
     * @param IsActive $statusList
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    /*function __construct(
    FilterManager $filter, 
    IsActive $statusList, 
    \Magento\Framework\Model\Context $context, 
    \Magento\Framework\Registry $registry, 
    \Magento\Framework\UrlInterface $urlBuilder, 
    \Magedelight\Storepickup\Model\Rule\Condition\CombineFactory $_ConditionCombineFactory, 
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, 
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = []) 
    {
        $this->filter = $filter;
        $this->_ConditionCombineFactory = $_ConditionCombineFactory;
        $this->statusList = $statusList;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }*/


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magedelight\Storepickup\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magedelight\Storepickup\Model\Rule\Condition\CombineFactory $condProdCombineF,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        FilterManager $filter,
        IsActive $statusList,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_condCombineFactory = $condCombineFactory;
        $this->_condProdCombineF = $condProdCombineF;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->statusList = $statusList;
        $this->serialize = $serialize;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Storepickup\Model\ResourceModel\Storelocator');
    }

    /**
     * sanitize the url key
     *
     * @param $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    public function getAvailableStatuses()
    {

        return $this->statusList->getOptions();
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->_condProdCombineF->create();
    }

    /**
     * 
     * @param type $urlKey
     * @param type $storeId
     * @return type
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }
    
    
    /**
     * 
     * @param \Magento\Framework\DataObject $object
     * @return \Magedelight\Storepickup\Model\Storelocator
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        try {
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'storeimport']);
            $uploader->setAllowedExtensions(['csv']);
        } catch (\Exception $e) {
            if ($e->getCode() == '666') {
                return $this;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
        $csvFile = $uploader->validateFile()['tmp_name'];
        $website = $this->storeManager->getWebsite($object->getScopeId());

        $this->_importWebsiteId = (int) $website->getId();
        $this->_importUniqueHash = [];
        $this->_importErrors = [];
        $this->_importedRows = 0;

        $tmpDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($csvFile);
        $stream = $tmpDirectory->openFile($path);

        // check and skip headers
        $headers = $stream->readCsv();
        
        $_columns = array(
            'storename',
            'url_key',
            'description',
            'website_url',
            'facebook_url',
            'twitter_url',
            'address',
            'city',
            'state',
            'country',
            'zipcode',
            'longitude',
            'latitude',
            'phone_frontend_status',
            'telephone',
            'storeimage',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'is_active',
            'region_id',
            'store_parent_id',
            'store_id',
            'storeemail',
            'days',
            'day_status',
            'open_hour',
            'open_minute',
            'close_hour',
            'close_minute',
            'delete',
        );
       
        if ($headers !== $_columns) {
            $this->_importErrors[] = "Headers do not match ";
            foreach ($headers as $header) {
                if(!in_array($header, $_columns)) {
                    $this->_importErrors[] = 'Headers do not match: ' . $header;
                }
            }
            if ($this->_importErrors) {
                $erros = __('We couldn\'t import this file because of these errors:  %1', implode(" \n", $this->_importErrors));
            }
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($erros));
        }
        
        if ($headers === false || count($headers) < 1) {
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct Storelocater File Format.'));
        }
        
        $connection = $this->_resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connection->beginTransaction();
        
        try {
            $rowNumber = 1;
            $importData = [];

            $temp = [];
            $csv= [];
            while (false !== ($csvLine = $stream->readCsv())) {
                ++$rowNumber;

                if (empty($csvLine)) {
                    continue;
                }
                //echo count($csvLine).'</br>';
                $row = array();
                foreach ($csvLine as $key => $field)  {
                    $row[$headers[$key]] = $field;
                }

                //$row = array_filter($row);
                $row = array_filter($row, function($value) {
                    return ($value !== null && $value !== false && $value !== '');
                });

                /*if($row['store_parent_id']==0){
                    $row['store_parent_id'] = NULL;
                }*/

                $data[] = $row;
            }

            $newarray = [];
            $starttimearray = [];
            $count = 1;
            $currentRow = [];
            foreach ($data as $csvdata) {
                if(isset($csvdata['storename'])) {
                    if(isset($currentRow) AND !empty($currentRow)){
                        $currentRow['storetime'] = $starttimearray;
                        $newarray[] = $currentRow;                        
                    }                    
                    $currentRow = $csvdata;
                    $starttimearray = [];                    
                } else {
                    $starttimearray[] = $csvdata;
                    if(count($data) == $count){
                        $currentRow['storetime'] = $starttimearray;
                        $newarray[] = $currentRow;
                    }
                }
                $count++;
            }

            foreach ($newarray as $storelocater) {
                if (!empty($storelocater['storetime'])) {
                    $storetime = $storelocater['storetime'];

                    $i=0;
                    foreach ($storetime as $key => $value) {
                        if(!array_key_exists('day_status', $value) &&
                            !array_key_exists('open_hour', $value) &&
                            !array_key_exists('open_minute', $value) &&
                            !array_key_exists('close_hour', $value) &&
                            !array_key_exists('close_minute', $value)){
                            unset($storetime[$i]);
                        }
                        $i++;
                    }
                    //echo "<pre>"; print_r($storetime);die;

                    $storelocater["storetime"] = $this->serialize->serialize($storetime);
                } else {
                    $storelocater["storetime"] = '';
                }

                if (!empty($storelocater['store_ids'])) {
                    $webstores = explode('-' , $storelocater['store_ids']);
                    $storelocater['store_ids'] = implode(',', $webstores);
                }

                if(array_key_exists('store_parent_id',$storelocater)){
                    if($storelocater['store_parent_id']==0){
                        $storelocater['store_parent_id'] = NULL;
                    }
                }

                $this->setData($storelocater);
                $this->save();

                if($this->getId()){
                    $loadData = $this->load($this->getId());
                    if(empty($loadData['store_parent_id'])){
                        $loadData->setStoreParentId($this->getId());
                        $loadData->setStoreId(0);
                        $loadData->save();
                    }
                }

            }
            $stream->close();
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            $stream->close();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $connection->rollback();
            $stream->close();
            $this->_logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
            __('Something went wrong while importing Storelocater.')
            );
        }

        $connection->commit();
        
        if ($this->_importErrors) {
            $error = __('We couldn\'t import this file because of these errors: %1', implode(" \n", $this->_importErrors)
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }

        return $this;
    }
}
