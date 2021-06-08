<?php

namespace Tkxel\UsernameOnHistoryComment\Plugin;

class OrderStatePlugin
{
    public $_authSession;
	
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_authSession = $authSession;
    }

    public function afterSetState(\Magento\Sales\Model\Order $subject, $result){
        
        $user = $this->_authSession->getUser();

        if($user->getUsername()){

            if (  $result->getState() !== $subject::STATE_NEW ) {

                $subject->addStatusToHistory($result->getStatus(), $result->getState());
                $subject->save(); 

            }

        }

        return $result;

    }
}