<?php

/**
 * @category    LCS
 * @package     Lcs_Cod
 * @author      AAlogics team <team@aalogics.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Aalogics\Lcs\Model\Carrier;

use \Aalogics\Lcs\Helper\Data;
class Lcs extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
	  protected $_code = 'aalcs';

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $shippingTrackingResultStatusFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Aalogics\Lcs\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $shippingTrackingResultStatusFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    	\Aalogics\Lcs\Helper\Data $helper	
    ) {
    	$this->_helper = $helper;
        $this->shippingTrackingResultStatusFactory = $shippingTrackingResultStatusFactory;
        $this->_scopeConfig = $scopeConfig;

    }
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
	{
		return false;
	}
 
	  public function getAllowedMethods()
	  {
	    return array(
	      'lcs' => 'LCS',
	    );
	  }
	 
		public function getTrackingInfo($tracking)
		{
			$track = $this->shippingTrackingResultStatusFactory->create();

			$externalUrl =  $this->_helper->getAdminField('lcs_cod/tracking_url');
			$api_key =  $this->_helper->getAdminField('lcs_cod/api_key');
			$api_password =  $this->_helper->getAdminField('lcs_cod/api_password');
			
			preg_match('/\d{1,9}$/', $tracking,$track_num);

			$lcs_track_num = (isset($track_num[0])) ? $track_num[0] : '';

			$tracking_url = $externalUrl.$lcs_track_num;

			$track->setUrl($tracking_url)
			->setTracking($lcs_track_num)
			->setCarrierTitle('LCS');

			return $track;
		}
		
		public function isTrackingAvailable()
		{
			return true;
		}
}
