<?php
namespace RLTSquare\ConfirmOrder\Model\ResourceModel\Post;

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
		$this->_init('RLTSquare\ConfirmOrder\Model\Post', 'RLTSquare\ConfirmOrder\Model\ResourceModel\Post');
	}

}