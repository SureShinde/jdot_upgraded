define(
	[
		'uiComponent',
		'Magento_Checkout/js/model/payment/renderer-list'
	],
	function (
		Component,
		rendererList
	) {
		'use strict';
		rendererList.push(
			{
				type: 'etisalatpay',
				component: 'Magento_Etisalatpay/js/view/payment/method-renderer/etisalatpay'
			}
		);
		/** Add view logic here if needed */
		return Component.extend({});
	}
);