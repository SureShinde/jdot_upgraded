<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Address;

class NewAction extends \Infomodus\Dhllabel\Controller\Adminhtml\Address
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
