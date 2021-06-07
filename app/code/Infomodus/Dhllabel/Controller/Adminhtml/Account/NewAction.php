<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Account;

class NewAction extends \Infomodus\Dhllabel\Controller\Adminhtml\Account
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
