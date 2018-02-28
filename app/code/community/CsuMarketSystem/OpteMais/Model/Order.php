<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    CsuMarketSystem
 * @package     CsuMarketSystem_OpteMais
 * @copyright   Copyright (c) 2016 CSU MarketSystem [www.csu.com.br]
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsuMarketSystem_OpteMais_Model_Order
{

    const DEFAULT_COUNTRY_ID = 'BR';
    const CHANGE_STATUS_APPROVED = "APR";
    const CHANGE_STATUS_CANCELED = "CAN";

    private $_optemaisStatuses = array(
        '1' => 'Pedido Aprovado',
        '2' => 'Pedido Cancelado',
        '3' => 'Aguardando Liberação',
        '4' => 'Pedido Pendente',
        '5' => 'Pedido Entregue',
        '9' => 'Pedido Devolvido',
    );

    private $_configStatuses = array(
        '1' => 'status_approved',
        '2' => 'status_canceled',
        '3' => 'status_await_release',
        '4' => 'status_pending',
        '5' => 'status_delivered',
        '9' => 'status_refunded',
    );

    private $_validChangeStatus = array(
        self::CHANGE_STATUS_APPROVED,
        self::CHANGE_STATUS_CANCELED
    );

    private $_requiredOrderDataParams = array(
        'PedidoParceiro',
        'Destinatario'    => array(
            'CpfCnpj',
            'Email',
            'Nome',
            'Telefone'
        ),
        'EnderecoEntrega' => array(
            'Bairro',
            'CEP',
            'Cidade',
            'Estado',
            'Logradouro',
            'Numero'
        ),
        'ValorFrete',
        'Produtos',
        'ValorTotalPedido',
        'ValorTotal'
    );

    private $_statuses = null;
    private $_regions = null;

    public function createOrder($orderData)
    {
        /** @var CsuMarketSystem_OpteMais_Helper_Data $helper */
        $helper = Mage::helper('optemais');
        try {

            $validatesResult = $this->_validatesOrderData($orderData);
            if (!$validatesResult['status']) {
                throw new Exception($helper->__("Dados inválidos ou faltando: [%s]",implode(",",$validatesResult['rows'])));
            }

            $websiteId = Mage::app()->getWebsite()->getId();
            $storeId = Mage::getStoreConfig('optemais/config_order/default_store_id');
            $store = Mage::app()->getStore();

            if (is_null($storeId)) {
                $storeId = Mage::app()->getStore()->getId();
            }

            /** @var Mage_Sales_Model_Quote $quote */
            $quote = Mage::getModel('sales/quote')
                ->setStoreId($storeId);

            $customerCnpj = null;
            $customerTipopessoa = null;
            $customerRazaoSocial = null;

            $attributeCpf = null;
            $attributeCnpj = null;
            $attributeTipoPessoa = null;
            $attributeRazaoSocial = null;

            $customerDocument = $orderData['Destinatario']['CpfCnpj'];
            $customerName = $orderData['Destinatario']['Nome'];

            $customerTipopessoa = $this->_validateDocument($customerDocument);

            if ($customerTipopessoa == 'pj') {
                $attributeCnpj = Mage::getStoreConfig('optemais/config_order/attribute_cnpj');
                $attributeRazaoSocial = Mage::getStoreConfig('optemais/config_order/attribute_razao_social');
                if (isset($attributeCnpj)) {
                    $customerCnpj = $helper->formatCnpj($customerDocument);
                }
                $customerCpf = '';
            } else {
                $attributeCpf = Mage::getStoreConfig('optemais/config_order/attribute_cpf');
                $customerCpf = $helper->formatCpf($customerDocument);
            }

            $attributeTipoPessoa = Mage::getStoreConfig('optemais/config_order/attribute_tipo_pessoa');

            $separatorIndex = strpos($customerName, " ");
            if ($separatorIndex !== false) {
                $customerFirstName = substr($customerName, 0, $separatorIndex);
                $customerLastName = substr($customerName, $separatorIndex + 1);
            } else {
                $customerFirstName = $customerName;
                $customerLastName = $customerName;
            }

            $phoneNumber = $orderData['Destinatario']['Telefone'];
            $celNumber = $orderData['Destinatario']['TelefoneCelular'];
            $postalCode = $orderData['EnderecoEntrega']['CEP'];
            $street = $orderData['EnderecoEntrega']['Logradouro'];
            $number = $orderData['EnderecoEntrega']['Numero'];
            $additionalInfo = $orderData['EnderecoEntrega']['Complemento'];
            $district = $orderData['EnderecoEntrega']['Bairro'];
            $streetRows = $this->_getStreet($street, $number, $additionalInfo, $district);
            $city = $orderData['EnderecoEntrega']['Cidade'];
            $regionCode = $orderData['EnderecoEntrega']['Estado'];

            $countryId = $this->_getCountryId($city);
            $regionId = $this->_getRegionIdByCode($regionCode, $countryId);

            $address = array(
                'firstname'         => $customerFirstName,
                'lastname'          => $customerLastName,
                'street'            => $streetRows,
                'city'              => $city,
                'postcode'          => $postalCode,
                'country_id'        => $countryId,
                'telephone'         => $phoneNumber,
                'fax'               => $celNumber,
                'customer_password' => '',
                'confirm_password'  => '',
                'use_for_shipping'  => '1',
            );

            if ($regionId) {
                $address['region_id'] = $regionId;
            } else {
                $address['region'] = $regionCode;
            }

            try {
                $email = $orderData['Destinatario']['Email'];
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);
                if (!$customer->getId()) {
                    $customer->setId(null);
                    $password = $customer->generatePassword(8);
                    $customer->setWebsiteId($websiteId)
                        ->setStore($store)
                        ->setFirstname($customerFirstName)
                        ->setLastname($customerLastName)
                        ->setEmail($email)
                        ->setPassword($password);
                    if ($attributeRazaoSocial) {
                        $customer->setData($attributeRazaoSocial, $customerRazaoSocial);
                    }
                    if ($attributeTipoPessoa && ($valueTipoPessoa = Mage::getStoreConfig('optemais/config_config_order/tipo_pessoa_value_' . $customerTipopessoa))) {
                        $customer->setData($attributeTipoPessoa, $valueTipoPessoa);
                    }
                    if ($customerTipopessoa == 'pj' && $attributeCnpj) {
                        $customer->setData($attributeCnpj, $customerCnpj);
                    } elseif ($customerCpf && $attributeCpf) {
                        $customer->setData($attributeCpf, $customerCpf);
                    }
                    $customer->save();
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            try {
                /** @var Mage_Customer_Model_Address $addressCustomer */
                $addressCustomer = Mage::getModel('customer/address');
                $addressCustomer->setCustomerId($customer->getId())
                    ->setFirstname($customerFirstName)
                    ->setLastname($customerLastName)
                    ->setCountryId($countryId)
                    ->setPostcode($postalCode)
                    ->setCity($city)
                    ->setTelephone($phoneNumber)
                    ->setStreet($streetRows)
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                if ($regionId) {
                    $addressCustomer->setRegionId($regionId);
                } else {
                    $addressCustomer->setRegion($regionCode);
                }
                $addressCustomer->save();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            if (!isset($orderData['Produtos']) || !$orderData['Produtos']) {
                throw new Exception($helper->__('Não foi possível importar o pedido %s, não há produtos comprados nesse pedido.', $orderData['PedidoParceiro']));
            }

            foreach ($orderData['Produtos'] as $itemProduct) {
                $sku = trim($itemProduct['Sku']);
                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::getModel('catalog/product');
                $product->load($product->getIdBySku($sku));
                $buyInfo = array('qty' => $itemProduct['Quantidade']);
                if ($product->getId()) {
                    if ($parentId = $this->_getParentIds($product)) {
                        $attributes = $this->_getAttributesByParentId($parentId);
                        $options = array();
                        foreach ($attributes as $attribute) {
                            $options[(int)$attribute['id']] = (int)$product->getData($attribute['code']);
                        }
                        /** @var Mage_Catalog_Model_Product $parent */
                        $parent = Mage::getModel('catalog/product')->load($parentId);
                        $buyInfo = array(
                            'product'         => $parent->getId(),
                            'qty'             => $itemProduct['Quantidade'],
                            'super_attribute' => $options,
                        );
                        $product = $parent;
                    }
                    $item = $quote->addProduct($product, new Varien_Object($buyInfo));
                    if (is_object($item) && $item->getParentItem()) {
                        $item = $item->getParentItem();
                        $item->setOriginalCustomPrice($itemProduct['PrecoVenda']);
                        $item->setCustomPrice($itemProduct['PrecoVenda']);
                        $item->getProduct()->setIsSuperMode(true);
                    } elseif (is_string($item) && $item == 'Please specify the product required option(s).') {
                        throw new Exception($helper->__('Pedidos com produtos (%s) com opções customizáveis não serão integrados.', $itemProduct['Sku']));
                    } elseif (is_string($item)) {
                        throw new Exception($helper->__('Não foi possível adicionar o produto (%s) ao pedido. Erro: %s.', $itemProduct['Sku'], $item));
                    }
                } else {
                    throw new Exception($helper->__('Produto com SKU (%s) não existe na loja, cadastre para que o pedido seja importado.', $itemProduct['Sku']));
                }
            }

            $quote->getBillingAddress()
                ->addData($address);

            $shippingMethodCode = Mage::getStoreConfig('optemais/config_order/shipping_method');
            $shippingMethodData = $helper->getShippingMethodData($shippingMethodCode);
            $totalFreight = $orderData['ValorFrete'];

            $rate = Mage::getModel('sales/quote_address_rate')
                ->setCode($shippingMethodCode)
                ->setMethod($shippingMethodData['method'])
                ->setMethodTitle($shippingMethodData['method_title'])
                ->setCarrier($shippingMethodData['carrier'])
                ->setCarrierTitle($shippingMethodData['carrier_title'])
                ->setPrice($totalFreight);

            $quote->getShippingAddress()
                ->addData($address);

            $ccInstallmentsKey = null;
            if(isset($orderData['IdFormaPagamento']) && isset($orderData['DadosCartaoCredito'])) {
                $methodData = $this->_getMethodDataByCcType($orderData['IdFormaPagamento']);
                $ccInstallmentsKey = 'cc_installments';
                if($data = $this->_getConfigDataForPaymentMethod($methodData['code'])) {
                    if($data['param_installment']) $ccInstallmentsKey = $data['param_installment'];
                }
                $paymentData = array(
                    'store_id' => $storeId,
                    'cc_custom_payment_id' => 0,
                    'method' => $methodData['code'],
                    'cc_type' => $methodData['cc_type'],
                    'cc_number' => $orderData['DadosCartaoCredito']['Numero'],
                    'cc_number_enc' => Mage::getSingleton('payment/info')->encrypt($orderData['DadosCartaoCredito']['Numero']),
                    'cc_owner' => $orderData['DadosCartaoCredito']['Nome'],
                    'cc_exp_month' => $orderData['DadosCartaoCredito']['ValidadeMes'],
                    'cc_exp_year' => $orderData['DadosCartaoCredito']['ValidadeAno'],
                    'cc_last4' => substr($orderData['DadosCartaoCredito']['Numero'],-4),
                    'cc_cid' => $orderData['DadosCartaoCredito']['CodigoVerificador']
                );
            } else {
                $paymentData = array('method' => 'optemais_modelc');
            }

            $quote->getShippingAddress()
                ->addShippingRate($rate)
                ->setShippingMethod($shippingMethodCode)
                ->setPaymentMethod($paymentData['method'])
                ->setCollectShippingRates(false)
                ->collectTotals();

            $quote->assignCustomer($customer);

            Mage::unregister('order_created_by_optemais');
            Mage::register('order_created_by_optemais', true);

            $quote->getPayment()->setAdditionalInformation('created_by_optemais', true);
            $quote->getPayment()->importData($paymentData);

            if($ccInstallmentsKey) {
                $quote->getPayment()->setAdditionalInformation($ccInstallmentsKey,$orderData['DadosCartaoCredito']['QuantidadeParcelas']);
            }

            $quote->save();

            // Para os métodos de pagamento que pegam informações da Session
            Mage::getSingleton('checkout/session')->setQuoteId($quote->getId());
            Mage::getSingleton('checkout/session')->getQuote()->setCustomer($customer);

            /** @var Mage_Sales_Model_Service_Quote $service */
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            if ($service->getOrder()) {
                $service->getOrder()->addStatusHistoryComment($helper->__('Pedido importado da CSU OPTe+'));
                $service->getOrder()->save();
            } else {
                throw new Exception($helper->__('Não foi possível importar o pedido %s.', $orderData['PedidoParceiro']));
            }
            Mage::unregister('order_created_by_optemais');
            return array(
                'Status'         => true,
                'Mensagem'       => null,
                'CodigoPedido'   => $service->getOrder()->getIncrementId(),
                'PedidoParceiro' => $orderData['PedidoParceiro'],
            );
        } catch (Exception $e) {
            Mage::unregister('order_created_by_optemais');
            Mage::logException($e);
            return array(
                'Status'         => false,
                'Mensagem'       => Mage::helper('adminhtml')->__($e->getMessage()),
                'CodigoPedido'   => null,
                'PedidoParceiro' => $orderData['PedidoParceiro'],
            );
        }
    }

    public function _getConfigDataForPaymentMethod($method) {
        $items = unserialize(Mage::getStoreConfig('optemais/config_order/payment_method'));
        foreach($items['payment_method_code'] as $pos => $code) {
            if($code == $method) {
                return array(
                    'code'=>$code,
                    'qty_installments'=>$items['qty_installments'][$pos],
                    'interest'=>$items['interest'][$pos],
                    'param_installment'=>$items['param_installment'][$pos]
                );
            }
        }
        return array();
    }

    private function _validatesOrderData($orderData)
    {
        if (!$orderData) {
            return array('status' => false, 'rows' => array('JSON inválido'));
        }
        $missing = array();
        foreach ($this->_requiredOrderDataParams as $key => $value) {
            if (is_array($value)) {
                if (!isset($orderData[$key])) {
                    $missing[] = $key;
                }
                foreach ($value as $item) {
                    if (!isset($orderData[$key][$item])) {
                            $missing[] = $item;
                    }
                }
            } elseif (!isset($orderData[$value])) {
                $missing[] = $value;
            }
        }
        return array('status' => count($missing) == 0,'rows'=>$missing);
    }

    private function _validateDocument($document)
    {
        $document = str_replace(array(',', '-', '.', ' '), '', $document);
        if (strlen($document) > 11) {
            return 'pj';
        }
        return 'pf';
    }

    private function _getRegionIdByCode($code, $countryId)
    {
        if (!$this->_regions) {
            $collection = Mage::getModel('directory/region')->getCollection();
            $collection->addFieldToFilter('country_id', $countryId);
            /** @var Mage_Directory_Model_Region $item */
            foreach ($collection as $item) {
                $this->_regions[$item->getCode()] = $item->getId();
            }
        }
        return isset($this->_regions[$code]) ? $this->_regions[$code] : null;
    }


    protected function _getCountryId($city)
    {
        $collection = Mage::getModel('directory/region')->getResourceCollection();
        $collection->addFieldToFilter('name', $city);
        if (count($collection) < 1) {
            $collection = null;
            $collection = Mage::getModel('directory/region')->getResourceCollection();
            $collection->addFieldToFilter('default_name', $city);
        }
        foreach ($collection as $item) {
            return $item->getCountryId();
        }
        return self::DEFAULT_COUNTRY_ID;
    }

    protected function _getStreet($address, $number, $additionalInfo, $district)
    {
        $lines = Mage::getStoreConfig('customer/address/street_lines');
        $lines = $lines ? $lines : 2;
        if ($lines == 3) {
            return $address . "\n" . $number . " " . $additionalInfo . "\n" . $district;
        }
        if ($lines == 4) {
            return $address . "\n" . $number . "\n" . $additionalInfo . "\n" . $district;
        }
        return $address . " " . $number . "\n " . $additionalInfo . " " . $district;
    }

    private function _getParentIds(Mage_Catalog_Model_Product $product)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable $resource */
        $resource = Mage::getResourceSingleton('catalog/product_type_configurable');
        $parentIds = $resource->getParentIdsByChild($product->getId());
        return $parentIds ? $parentIds[0] : false;
    }

    private function _getAttributesByParentId($parentId)
    {
        $product = Mage::getModel('catalog/product')->load($parentId);
        $attributes = array();
        if (!$product->getId()) {
            return $attributes;
        }
        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        foreach ($productAttributeOptions as $attribute) {
            $attributes[] = array('id' => $attribute['attribute_id'], 'code' => $attribute['attribute_code']);
        }
        return $attributes;
    }

    public function changeStatus($params)
    {
        /** @var Mage_Sales_Model_Order $order */
        try {
            $orderIncrementId = isset($params['IdPedido']) && $params['IdPedido'] ? $params['IdPedido'] : false;
            $status = isset($params['Status']) && $params['Status'] ? $params['Status'] : false;
            if (!$orderIncrementId) {
                return array('status' => false, 'invalid_params' => array('IdPedido'), 'data' => array('Confirmado' => false));
            }
            if (!$status || !in_array($status, $this->_validChangeStatus)) {
                return array('status' => false, 'invalid_params' => array('Status'), 'data' => array('Confirmado' => false));
            }
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($orderIncrementId);
            if ($order->getId()) {
                if ($status == self::CHANGE_STATUS_APPROVED) {
                    if (!$this->_createInvoice($order)) {
                        throw new Exception(Mage::helper("optemais")->__('O status atual do pedido não permite aprovaçao'));
                    }
                } else {
                    if (!$this->_cancel($order)) {
                        throw new Exception(Mage::helper("optemais")->__('O status atual do pedido não permite cancelamento'));
                    }
                }
                return array('status' => true, 'not_found' => false, 'data' => array('Confirmado' => true));
            }
            return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Pedido'), 'data' => array('Confirmado' => false));
        } catch (Exception $e) {
            return array('status' => false, 'not_found' => false, 'message' => $e->getMessage(), 'data' => array('Confirmado' => false));
        }
    }

    public function getTrackingInfo($params)
    {
        /** @var Mage_Sales_Model_Order $order */
        try {
            $orderIncrementId = isset($params['IdPedido']) && $params['IdPedido'] ? $params['IdPedido'] : false;
            if (!$orderIncrementId) {
                return array('status' => false, 'invalid_params' => array('IdPedido'));
            }
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($orderIncrementId);
            if ($order->getId()) {
                $statusHistory = array();
                /** @var Mage_Sales_Model_Order_Status_History $status */
                foreach ($order->getAllStatusHistory() as $status) {
                    $statusData = $this->_getStatusData($status->getStatus());
                    if (!$statusData['code']) continue;
                    $statusHistory[$status->getStatus()] = array(
                        'CodDescricao' => $statusData['code'],
                        'Descricao'    => $statusData['label'],
                        'Data'         => Mage::helper('core')->formatDate($status->getCreatedAt(),Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true),
                    );
                }
                $trackingItems = array();
                /** @var Mage_Sales_Model_Order_Item $item */
                foreach ($order->getAllVisibleItems() as $item) {
                    $trackingItems[] = array(
                        'Sku'       => $item->getSku(),
                        'Trackings' => $statusHistory,
                    );
                }
                $statusData = $this->_getStatusData($order->getStatus());
                $data = array(
                    'ProdutosTracking' => $trackingItems,
                    'CodDescricao'     => $statusData['code'],
                    'Descricao'        => $statusData['label'],
                );
                return array('status' => true, 'not_found' => false, 'data' => $data);
            }
            return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Pedido'));
        } catch (Exception $e) {
            return array('status' => false, 'not_found' => false, 'message' => $e->getMessage());
        }
    }

    public function getPaymentMethods($campaignId = null)
    {
        /** @var Mage_Sales_Model_Order $order */
        try {
            if($campaignId) {
                $campaign = Mage::getModel('optemais/campaign')->load($campaignId);
                if ($campaign->getId()) {
                    $data = array('FormaPagamento'=>$this->_getPaymentMethodsData($campaign->getPaymentMethods()));
                    return array('status' => true, 'not_found' => false, 'data' => $data);
                }
                return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Campanha'));
            } else {
                $items = unserialize(Mage::getStoreConfig('optemais/config_order/payment_method'));
                $data = array('FormaPagamento'=>$this->_getPaymentMethodsData($items));
                return array('status' => true, 'not_found' => false, 'data' => $data);
            }
        } catch (Exception $e) {
            return array('status' => false, 'not_found' => false, 'message' => $e->getMessage());
        }
    }

    private function _getPaymentMethodsData($items)
    {
        $data = array();
        if($items) {
            $paymentMethods = array();
            foreach($items['payment_method_code'] as $value) {
                if(!$value) continue;
                $ccTypes = $this->_getCcTypes($value);
                if(count($ccTypes)>0) {
                    foreach($ccTypes as $ccType => $ccTypeCode) {
                        $paymentMethods[] = array('IdFormaPagamento' => $ccType, 'Nome' => Mage::getStoreConfig('payment/'.$value.'/title'), 'CodigoBandeira' => $ccTypeCode['cc_type']);
                    }
                } else {
                    $paymentMethods[] = array('IdFormaPagamento' => $value, 'Nome' => Mage::getStoreConfig('payment/'.$value.'/title'), 'CodigoBandeira' => '');
                }
            }
            $data = array($paymentMethods);
        }
        return $data;
    }

    private function _getMethodDataByCcType($ccType)
    {
        $ccTypes = $this->_getCcTypes();
        return isset($ccTypes[$ccType]) ? $ccTypes[$ccType] : array('code'=>$ccType,'cc_type'=>null);
    }

    private function _getCcTypes($methodCode = null)
    {
        $items = array();
        if($methodCode)  {
            $availableMethods = array($methodCode);
        } else {
            $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
            $availableMethods = array_keys($allAvailablePaymentMethods);
        }
        foreach($availableMethods as $methodCode) {
            if ($method = Mage::helper('payment')->getMethodInstance($methodCode)) {
                $availableTypes = $method->getConfigData('cctypes');
                if ($availableTypes) {
                    $availableTypes = explode(',', $availableTypes);
                    foreach ($availableTypes as $availableType) {
                        $items[$methodCode .'_' . $availableType] = array('code' => $methodCode,'cc_type'=>$availableType);
                    }
                }
            }
        }
        return $items;
    }

    public function getInstallments($params)
    {
        try {
            $paymentMethodId = isset($params['IdFormaPagamento']) && $params['IdFormaPagamento'] ? $params['IdFormaPagamento'] : false;
            $campaignId = isset($params['IdCampanha']) && $params['IdCampanha'] ? $params['IdCampanha'] : false;
            $queryValue = isset($params['ValorAParcelar']) && $params['ValorAParcelar'] ? $params['ValorAParcelar'] : false;
            if (!$queryValue) {
                return array('status' => false, 'invalid_params' => array('ValorAParcelar'));
            }
            if($campaignId) {
                $campaign = Mage::getModel('optemais/campaign')->load($campaignId);
                if ($campaign->getId()) {
                    $paymentMethods = $this->_getInstallmentsData($campaign->getPaymentMethods(),$queryValue,$paymentMethodId);
                    $data = array('OpcoesParcelamento'=>$paymentMethods);
                    return array('status' => true, 'not_found' => false, 'data' => $data);
                }
                return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Campanha'));
            } else {
                $data = array('OpcoesParcelamento'=>array());
                if($config = Mage::getStoreConfig('optemais/config_order/payment_method')) {
                    if($items = unserialize($config)) {
                        $paymentMethods = $this->_getInstallmentsData($items,$queryValue,$paymentMethodId);
                        if(!$paymentMethods && $paymentMethodId) {
                            return array('status' => false, 'not_found' => true, 'message' => Mage::helper('optemais')->__('Forma de Pagamento'));
                        }
                        $data = array('OpcoesParcelamento'=>$paymentMethods);
                    }
                }
                return array('status' => true, 'not_found' => false, 'data' => $data);
            }
        } catch (Exception $e) {
            return array('status' => false, 'not_found' => false, 'message' => $e->getMessage());
        }
    }

    private function _getInstallmentsData($items,$queryValue,$paymentMethodId = null) {
        $paymentMethods = array();
        /** @var CsuMarketSystem_OpteMais_Helper_Data $helper */
        $helper = Mage::helper('optemais');
        foreach($items['payment_method_code'] as $key => $value) {
            if(!$value) continue;
            $queryValue = (float)str_replace(array(',','%'),array('.',''),$queryValue);
            $qtyInstallments = $items['qty_installments'][$key] ? (integer)$items['qty_installments'][$key] : 1;
            $interest = number_format((float)str_replace(array(',','%'),array('.',''),$items['interest'][$key]), 2, '.', '');
            $priceWithInterest = (float)$interest > 0
                ? $helper->getPriceWithInterest($queryValue,$interest,$qtyInstallments)
                : $queryValue;
            $ccTypes = $this->_getCcTypes($value);
            if(count($ccTypes)>0) {
                foreach($ccTypes as $ccType => $ccTypeCode) {
                    if($paymentMethodId && $ccType != $paymentMethodId) continue;
                    $paymentMethods[] = array(
                        'IdFormaPagamento' => $ccType,
                        'QuantidadeParcelas' => $items['qty_installments'][$key],
                        'TaxaJurosAoMes' => $interest,
                        'ValorParcela' => number_format((float)$priceWithInterest,2,'.',''),
                        'ValorTotal' => number_format((float)$priceWithInterest*$qtyInstallments,2,'.',''),
                    );
                }
            } else {
                if($paymentMethodId && $value != $paymentMethodId) continue;
                $paymentMethods[] = array(
                    'IdFormaPagamento' => $value,
                    'QuantidadeParcelas' => $items['qty_installments'][$key],
                    'TaxaJurosAoMes' => $interest,
                    'ValorParcela' => number_format((float)$priceWithInterest,2,'.',''),
                    'ValorTotal' => number_format((float)$priceWithInterest*$qtyInstallments,2,'.',''),
                );
            }
        }
        return $paymentMethods;
    }

    private function _getStatusData($orderStatusCode)
    {
        if (!$this->_statuses) {
            foreach ($this->_optemaisStatuses as $code => $label) {
                if ($config = Mage::getStoreConfig('optemais/config_order_status/' . $this->_configStatuses[$code])) {
                    $this->_statuses[$config] = array('code' => $code, 'label' => Mage::helper('optemais')->__($label));
                }
            }
        }
        $statusCode = isset($this->_statuses[$orderStatusCode]['code'])
            ? $this->_statuses[$orderStatusCode]['code']
            : null;
        $statusLabel = isset($this->_statuses[$orderStatusCode]['label'])
            ? $this->_statuses[$orderStatusCode]['label']
            : null;
        return array('code' => $statusCode, 'label' => $statusLabel);
    }

    /**
     * Create invoice
     *
     * @param Mage_Sales_Model_Order @order
     *
     * @return int|bool
     */
    protected function _createInvoice(Mage_Sales_Model_Order $order)
    {
        if (!$order->canInvoice()) {
            return false;
        }
        $invoice = $order->prepareInvoice(array());
        if ($invoice) {
            $invoice->register()->pay();
            $invoice->addComment(Mage::helper('optemais')->__("Pedido Aprovado pela OPTe+"));
            $invoice->getOrder()->setIsInProcess(true);
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            $order->addStatusHistoryComment(Mage::helper('optemais')->__("Pedido Aprovado pela OPTe+"));
            $order->save();
            return $invoice->getIncrementId();
        }
        return true;
    }

    /**
     * Create new credit memo for order
     *
     * @param Mage_Sales_Model_Order @order
     *
     * @return int|bool
     */
    protected function _createCreditMemo(Mage_Sales_Model_Order $order)
    {
        /** @var $service Mage_Sales_Model_Service_Order */
        $service = Mage::getModel('sales/service_order', $order);
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $service->prepareCreditmemo(array());
        if ($creditmemo) {
            $creditmemo->setPaymentRefundDisallowed(true)->register();
            $creditmemo->addComment(Mage::helper('optemais')->__("Pedido Cancelado/Devolvido pela OPTe+"));
            Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($creditmemo->getOrder())
                ->save();
            return $creditmemo->getIncrementId();
        }
        return false;
    }

    /**
     * Cancel order
     *
     * @param Mage_Sales_Model_Order @order
     *
     * @return bool
     */
    protected function _cancel(Mage_Sales_Model_Order $order)
    {
        if (($order->hasInvoices() || $order->hasShipments()) && $order->canCreditmemo()) {
            return $this->_createCreditMemo($order, Mage::helper('optemais')->__("Pedido Cancelado pela OPTe+"));
        }
        if (!$order->canCancel()) {
            return false;
        }
        $order->addStatusHistoryComment(Mage::helper('optemais')->__("Pedido Cancelado pela OPTe+"), Mage_Sales_Model_Order::STATE_CANCELED);
        $order->cancel();
        $order->save();
        return true;
    }

}
