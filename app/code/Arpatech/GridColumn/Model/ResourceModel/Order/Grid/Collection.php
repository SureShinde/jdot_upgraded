<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\GridColumn\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /*protected function _renderFiltersBefore() 
    {
        $joinTable = $this->getTable('sales_order_address');
        $this->getSelect()->joinLeft($joinTable.' as addresstable','main_table.entity_id = addresstable.entity_id', array('telephone', 'telephone'=> 'addresstable.telephone'));

        parent::_renderFiltersBefore();
    } */
   
    /*protected function _initSelect()
	{
		parent::_initSelect();

	    $this->getSelect()->joinLeft(
	        ['addressTable' => $this->getTable('sales_order_address')],
	        'main_table.entity_id= addressTable.parent_id',
	        '*'
	    );
	} */

	protected function _initSelect()
	{
		parent::_initSelect();

	    $this->getSelect()->joinLeft(
	        ['addressTable' => $this->getTable('sales_order_address')],
	        'main_table.entity_id = addressTable.parent_id',
	        [
	         'address_type' => 'addressTable.address_type',
	         'telephone' => 'addressTable.telephone'
     		]
	    )->where( "address_type = 'billing'" );
	}
}
