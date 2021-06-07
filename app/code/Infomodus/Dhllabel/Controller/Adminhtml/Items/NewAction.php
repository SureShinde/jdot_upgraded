<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Items;

class NewAction extends \Infomodus\Dhllabel\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('editone');
    }
}
