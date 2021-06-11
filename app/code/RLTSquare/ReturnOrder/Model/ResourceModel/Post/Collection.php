<?php
namespace RLTSquare\ReturnOrder\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('RLTSquare\ReturnOrder\Model\Post', 'RLTSquare\ReturnOrder\Model\ResourceModel\Post');
	}

}