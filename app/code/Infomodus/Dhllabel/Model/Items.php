<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Model;

class Items extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Infomodus\Dhllabel\Model\ResourceModel\Items');
    }
    public function getListStatuses()
    {
        return [0 => 'Success', 1 => 'Error'];
    }
}
