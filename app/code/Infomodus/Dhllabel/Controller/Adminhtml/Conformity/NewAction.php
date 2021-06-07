<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Conformity;

class NewAction extends \Infomodus\Dhllabel\Controller\Adminhtml\Conformity
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
