<?php

class Nethuns_Nemoexpress_Model_Carrier_Nemoexpress
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'nemoexpress';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Package types
     */
    const PACKAGE_TYPE_ENVELOPE = 'envelope';
    const PACKAGE_TYPE_PACKAGE = 'package';
    const PACKAGE_TYPE_PALLET = 'pallet';

    /**
     * Service ID
     */
    const SAME_DAY_DELIVERY = 'SDD';
    const NEXT_DAY_DELIVERY = 'NDD';

    /**
     * Payer
     */
    const CLIENT = 'EXP';
    const RECIPIENT = 'DEST';

    /**
     * Cash on delivery type
     */
    const TYPE_BANK_ACCOUNT = 'CONT';
    const TYPE_CASH = 'CASH';
    const TYPE_TICKET = 'BO';
    const TYPE_CHEQUE = 'CEC';
    const TYPE_TICKET_OR_CHEQUE = 'BOCEC';

    /**
     * Additional delivery services
     */
    const SERVICE_DOCUMENTS_RETURNS = 'RDOC';
    const SERVICE_SATURDAY_DELIVERY = 'SAT';
    const SERVICE_MONDAY_DELIVERY = 'MON';
    const SERVICE_OFFICE_DELIVERY = 'OFFICE';

    /**
     * XML Config paths
     */
    const XML_PATH_TITLE = 'carriers/nemoexpress/title';
    const XML_PATH_PAYER = 'carriers/nemoexpress/payer';
    const XML_PATH_COD_TYPE = 'carriers/nemoexpress/cod_type';
    const XML_PATH_BANK = 'carriers/nemoexpress/bank';
    const XML_PATH_IBAN = 'carriers/nemoexpress/iban';
    const XML_PATH_SPECIAL_CONDITIONS = 'carriers/nemoexpress/special_conditions';
    const XML_PATH_API_URL = 'carriers/nemoexpress/api_url';
    const XML_PATH_API_KEY = 'carriers/nemoexpress/api_key';
    const XML_PATH_PACKAGE_TYPE = 'carriers/nemoexpress/default_package_type';
    const XML_PATH_DEFAULT_WEIGHT = 'carriers/nemoexpress/default_weight';
    const XML_PATH_DEFAULT_HEIGHT = 'carriers/nemoexpress/default_height';
    const XML_PATH_DEFAULT_WIDTH = 'carriers/nemoexpress/default_width';
    const XML_PATH_DEFAULT_DEPTH = 'carriers/nemoexpress/default_depth';

    /**
     * Rate request data
     *
     * @var Mage_Shipping_Model_Rate_Request|null
     */
    protected $_request = null;

    /**
     * Raw rate request data
     *
     * @var Nethuns_Nemoexpress_Model_Rate_Request|null
     */
    protected $_rawRequest = null;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;

    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    protected $_activeFlag = 'active';

    /**
     * @return bool
     */
    public function canCollectRates()
    {
       return $this->getConfigFlag($this->_activeFlag);
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        if (!$this->canCollectRates()) {
            return false;
        }

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     *
     */
    protected function _getQuotes()
    {
        $rawRequest = $this->getRawRequest();

        $rawRequest->setServiceType(self::NEXT_DAY_DELIVERY);
        $response = $this->_getQuoteFromServer();
        $this->_parseResponse($response);

        $rawRequest->setServiceType(self::SAME_DAY_DELIVERY);
        $response = $this->_getQuoteFromServer();
        $this->_parseResponse($response);
    }

    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Nethuns_Nemoexpress_Model_Carrier_Nemoexpress
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $rawRequest = $this->getRawRequest();

        $rawRequest->setRecipient($request->getDestPersonName() ? $request->getDestPersonName() : 'Dummy');
        $rawRequest->setContactPerson($rawRequest->getRecipient());
        $rawRequest->setPostalCode($request->getDestPostcode());
        $rawRequest->setAddress($request->getDestStreet());
        $rawRequest->setPhone($request->getDestPhoneNumber() ? $request->getDestPersonName() : '0123456789');
        $rawRequest->setEmail('');
        $rawRequest->setDefaultPackageNumber();
        $rawRequest->setWeight(
            $request->getPackageWeight() ?
            $request->getPackageWeight() :
            $rawRequest->getDefaultWeight()
        );
        $rawRequest->setVolume($request);
        $rawRequest->setPayer(Mage::getStoreConfig(self::XML_PATH_PAYER, $this->getStore()));
        $rawRequest->setCashOnDeliveryType(
            Mage::getStoreConfig(self::XML_PATH_COD_TYPE, $this->getStore())
        );
        $rawRequest->setCashOnDelivery(
            $request->getBaseSubtotalInclTax() ? $request->getBaseSubtotalInclTax() : 0
        );
        $rawRequest->setBank(
            Mage::getStoreConfig(self::XML_PATH_BANK, $this->getStore())
        );
        $rawRequest->setIban(
            Mage::getStoreConfig(self::XML_PATH_IBAN, $this->getStore())
        );
        $rawRequest->setDeclaredValue(
            $request->getPackageValue() ? $request->getPackageValue() : 0
        );
        $rawRequest->setContent('');
        $rawRequest->setSpecialConditions(
            Mage::getStoreConfig(self::XML_PATH_SPECIAL_CONDITIONS, $this->getStore())
        );
        $rawRequest->setObservations('');


        return $this;
    }

    /**
     *
     */
    protected function _getQuoteFromServer()
    {
        /** @var Nethuns_Nemoexpress_Model_Api $api */
        $api = Mage::getSingleton('nethuns_nemoexpress/api');

        return $api->request(
            $api::ESTIMATE_COST,
            Zend_Http_Client::POST,
            $this->_rawRequest->getData()
        );
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return void|null
     */
    protected function _updateFreeMethodQuote($request)
    {
        $freeShipping = false;
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct() instanceof Mage_Catalog_Model_Product) {
                if ($item->getFreeShipping()) {
                    $freeShipping = true;
                } else {
                    return;
                }
            }
        }

        if ($freeShipping) {
            $request->setFreeShipping(true);
        }
    }

    /**
     * @param array $response
     */
    protected function _parseResponse($response)
    {
        $result = $this->getResult();

        if (isset($response['isError']) && $response['isError'] == 'true') {

            $message = implode(' ', $response['messages']);

            /** @var Mage_Shipping_Model_Rate_Result_Error $error */
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setErrorMessage($message);

            $result->append($error);

            return;
        }

        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->getMethodById($this->getRawRequest()->getServiceType(), 'code'));
        $method->setMethodTitle($this->getMethodById($this->getRawRequest()->getServiceType(), 'title'));
        $method->setPrice($response['amount']);
        $method->setCost($response['amount']);

        $result->append($method);
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }

    /**
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Return container types of carrier
     *
     * @param Varien_Object|null $params
     * @return array
     */
    public function getContainerTypes(Varien_Object $params = null)
    {
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        return array(
            self::PACKAGE_TYPE_ENVELOPE => $helper->__('Envelope'),
            self::PACKAGE_TYPE_PACKAGE => $helper->__('Package'),
            self::PACKAGE_TYPE_PALLET=> $helper->__('Pallet'),
        );
    }

    /**
     * @param $methodId
     * @param $key
     * @return string|array
     */
    public static function getMethodById($methodId, $key)
    {
        $methods = array(
            self::SAME_DAY_DELIVERY => array(
                'code' => 'sdd',
                'title' => Mage::helper('nethuns_nemoexpress')->__('Same Day Delivery')
            ),
            self::NEXT_DAY_DELIVERY => array(
                'code' => 'ndd',
                'title' => Mage::helper('nethuns_nemoexpress')->__('Next Day Delivery')
            )
        );

        return $methods[$methodId][$key];
    }

    /**
     * @param $method
     * @param $key
     * @return string|array
     */
    public static function getMethodByCode($method, $key)
    {
        $methods = array(
            'nemoexpress_sdd' => array(
                'id' => self::SAME_DAY_DELIVERY,
                'title' => Mage::helper('nethuns_nemoexpress')->__('Same Day Delivery')
            ),
            'nemoexpress_ndd' => array(
                'id' => self::NEXT_DAY_DELIVERY,
                'title' => Mage::helper('nethuns_nemoexpress')->__('Next Day Delivery')
            )
        );

        return $methods[$method][$key];
    }

    /**
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function getResult()
    {
        if (!$this->_result) {
            /** @var Mage_Shipping_Model_Rate_Result $result */
            $this->_result = Mage::getModel('shipping/rate_result');
        }
        return $this->_result;
    }

    /**
     * @return Nethuns_Nemoexpress_Model_Rate_Request
     */
    public function getRawRequest()
    {
        if (!$this->_rawRequest ) {
            /** @var Nethuns_Nemoexpress_Model_Rate_Request $this ->_rawRequest */
            $this->_rawRequest = Mage::getModel('nethuns_nemoexpress/rate_request');
        }
        return $this->_rawRequest;
    }

    /**
     * Do request to shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Varien_Object $response
     * @throws Mage_Core_Exception
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = $request->getPackages();

        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('usa')->__('No packages for request'));
        }

        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }

        $data = array();
        $shipmentRequest = $this->_prepareShipmentRequest($request);
        $result = $this->_doShipmentRequest($shipmentRequest);

        if (!$result->hasErrors()) {
            $data[] = array(
                'tracking_number' => $result->getTrackingNumber(),
                'label_content'   => $this->_getShippingLabelContent($result->getTrackingNumber())
            );
        }

        $request->setMasterTrackingId($result->getTrackingNumber());

        $response = new Varien_Object(array(
            'info'   => $data
        ));

        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }

        return $response;
    }
    
    /**
     * Prepare shipment request.
     * Validate and correct request information
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Varien_Object $request
     */
    protected function _prepareShipmentRequest($request)
    {
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');
        /** @var Mage_Sales_Model_Order $order */
        $order = $request->getOrderShipment()->getOrder();
        $rawRequest = $this->getRawRequest();

        $rawRequest->setRecipient($request->getRecipientContactPersonName());
        $rawRequest->setContactPerson($rawRequest->getRecipient());
        $rawRequest->setPostalCode($request->getRecipientAddressPostalCode());
        $rawRequest->setAddress($request->getRecipientAddressStreet());
        $rawRequest->setPhone($request->getRecipientContactPhoneNumber());
        $rawRequest->setEmail($request->getRecipientEmail());
        $rawRequest->setServiceType($this->getMethodByCode($order->getShippingMethod(), 'id'));
        $rawRequest->setPackagesDimensions($request);
        $rawRequest->setPayer(Mage::getStoreConfig(self::XML_PATH_PAYER, $request->getStoreId()));
        $rawRequest->setCashOnDeliveryType(Mage::getStoreConfig(self::XML_PATH_COD_TYPE, $request->getStoreId()));
        $rawRequest->setCashOnDelivery(
        $order->getPayment()->getMethod() == 'cashondelivery' ? $order->getGrandTotal()  : 0
        );
        $rawRequest->setBank(Mage::getStoreConfig(self::XML_PATH_BANK, $request->getStoreId()));
        $rawRequest->setIban(Mage::getStoreConfig(self::XML_PATH_IBAN, $request->getStoreId()));
        $rawRequest->setDeclaredValue($order->getGrandTotal());
        $rawRequest->setContent($helper->__('Order no. %s from %s', $order->getIncrementId(), $request->getShipperContactCompanyName()));
        $rawRequest->setSpecialConditions(
            Mage::getStoreConfig(self::XML_PATH_SPECIAL_CONDITIONS, $request->getStoreId())
        );
        $rawRequest->setObservations($order->getCustomerNote());

        return $request;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param Varien_Object $request
     * @return Varien_Object $result;
     */
    protected function _doShipmentRequest(Varien_Object $request)
    {
        $result = new Varien_Object();

        /** @var Nethuns_Nemoexpress_Model_Api $api */
        $api = Mage::getSingleton('nethuns_nemoexpress/api');
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        $response = $api->request(
            $api::CREATE_SHIPPING_LABEL,
            Zend_Http_Client::POST,
            $this->_rawRequest->getData()
        );

        if (empty($response['results'])) {
            $result->setErrors($helper->__('Unexpected error. Please try again later or create the AWB manually'));
            return $result;
        }

        $result->setTrackingNumber($response['results'][0]['awb']);
        return $result;
    }
    /**
     * @param $trackingNumber
     * @return array|string
     */
    protected function _getShippingLabelContent($trackingNumber)
    {
        /** @var Nethuns_Nemoexpress_Model_Api $api */
        $api = Mage::getSingleton('nethuns_nemoexpress/api');
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        $response = $api->request(
            $api::DOWNLOAD_SHIPPING_LABEL,
            Zend_Http_Client::GET,
            array(
                'code'      => $trackingNumber,
                'awb-only'  => 'true',
                'output'    => 'pdf'
            ),
            false
        );

        return $response;
    }

    /**
     * @param $track_id
     * @return bool|false|Mage_Core_Model_Abstract
     */
    public function getTrackingInfo($track_id)
    {
        $result = $this->getTracking($track_id);

        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @param $trackings
     * @return false|Mage_Core_Model_Abstract
     */
    public function getTracking($trackings)
    {
        /** @var Nethuns_Nemoexpress_Model_Api $api */
        $api = Mage::getSingleton('nethuns_nemoexpress/api');
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        if (is_array($trackings)) {
            $trackings = implode(',', $trackings);
        }

        $response = $api->request(
            $api::TRACKING_STATUS,
            Zend_Http_Client::POST,
            array(
                'awb'   => $trackings
            )
        );

        $result = Mage::getModel('shipping/tracking_result');

        if (!empty($response['data'])) {
            foreach ($response['data'] as $t => $data) {
                /** @var Mage_Shipping_Model_Tracking_Result_Status $error */
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($data['barcode']);
                $tracking->setStatus($data['status']);
                $tracking->setDeliveryDate($data['date']);

                $result->append($tracking);
            }
        }

        return $result;
    }
}