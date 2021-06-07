<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Items;

class Price extends \Infomodus\Dhllabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $content = '';
        if ($this->getRequest()->getPostValue()) {
            $order_id = $this->getRequest()->getParam('order_id');
            $type = $this->getRequest()->getParam('type');
            $params = $this->getRequest()->getParams();
            $order = $this->orderRepository->get($order_id);

            if(isset($params['package'])) {
                $arrPackagesOld = $params['package'];
                if (!empty($arrPackagesOld)) {
                    foreach ($arrPackagesOld as $k => $v) {
                        $i = 0;
                        foreach ($v as $d => $f) {
                            $arrPackages[$i][$k] = $f;
                            $i += 1;
                        }
                    }
                    unset($v, $k, $i, $d, $f);
                    $params['package'] = $arrPackages;
                }
            }

            $prices = $this->_handy->getLabel($order, 'ajaxprice_'.$type, null, $params);
            if (is_array($prices) && count($prices) > 0) {
                foreach ($prices as $price) {
                    $priceTotal = 0;
                    $priceCurrency = '';
                    if(!is_array($price)) {
                        if ($params['serviceCode'] !== $price->getProductGlobalCode()) {
                            continue;
                        }

                        $priceTotal = $price->getTotalAmount();
                        $priceCurrency = $price->getCurrencyCode();
                    } else {
                        foreach ($price as $pr) {
                            if ($params['serviceCode'] !== $pr->getProductGlobalCode()) {
                                continue;
                            }

                            $priceTotal += $pr->getTotalAmount();
                            $priceCurrency = $pr->getCurrencyCode();
                        }
                    }

                    $content .= __('Price') . ': ' . $priceTotal . '' . $priceCurrency;
                }
                $this->getResponse()
                    ->setContent($content);
                return;
            } else {
                $this->getResponse()
                    ->setContent(__('Error (price 1001)'));
                return;
            }


        }
    }
}
