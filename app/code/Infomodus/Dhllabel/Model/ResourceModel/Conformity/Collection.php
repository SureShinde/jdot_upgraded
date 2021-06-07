<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Model\ResourceModel\Conformity;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Infomodus\Dhllabel\Model\Conformity', 'Infomodus\Dhllabel\Model\ResourceModel\Conformity');
    }
}
