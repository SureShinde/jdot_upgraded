<?php

namespace Tkxel\UsernameOnHistoryComment\Plugin;

class OrderPlugin
{
    public $_authSession;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_authSession = $authSession;
    }

    public function beforeAddStatusHistoryComment(\Magento\Sales\Model\Order $subject, $comment, $status = false)
    {
    	$user = $this->_authSession->getUser();

    	if($user->getUsername()){

	    	$comment = $comment . " <b>( by ". $user->getUsername() ." ) </b>";   		

    	}
    	
    	$resp = array( $comment, $status );

        return $resp;
    }

}