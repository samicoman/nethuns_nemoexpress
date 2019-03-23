<?php

class Nethuns_Nemoexpress_Model_Api
{
    const ESTIMATE_COST = 'newawb.php';
    const CREATE_SHIPPING_LABEL = 'newawb.php';
    const DOWNLOAD_SHIPPING_LABEL = 'label.php';
    const TRACKING_STATUS = 'statusawb.php';

    /**
     * API Key
     * @var string $_apiKey
     */
    protected $_apiKey;

    /**
     * API URL
     * @var string $_apiUrl
     */
    protected $_apiUrl;

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if(!$this->_apiUrl) {
            $this->_apiUrl = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_API_URL);
        }
        return $this->_apiUrl;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        if(!$this->_apiKey) {
            $this->_apiKey = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_API_KEY);
        }
        return $this->_apiKey;
    }

    /**
     * @param string $path
     * @param string $type
     * @param array $params
     * @return string
     */
    public function getRequestUrl($path, $type, $params = array())
    {
        $url = rtrim($this->getApiUrl(), '/');
        $url .= '/' . $path;
        $url .= '?key=' . $this->getApiKey();
        if(!empty($params) && $type == \Zend_Http_Client::GET) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }

    /**
     * @param string $url
     * @param string $type
     * @param array $params
     * @return array
     */
    public function getRequestOptions($url, $type, $params)
    {
        $data["data"]=json_encode(array($params));
        $options = array();
        if($type == \Zend_Http_Client::POST) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        $options[CURLOPT_RETURNTRANSFER] = 1;
        $options[CURLOPT_HEADER] = 0;
        return $options;
    }

    /**
     * @param string $path
     * @param string $type
     * @param array $params
     * @param bool $decode
     * @return array|string
     */
    public function request($path, $type, $params = array(), $decode = true)
    {
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        $result = '';
        $url        = $this->getRequestUrl($path, $type, $params);
        $options    = $this->getRequestOptions($url, $type, $params);

        try {
            $curl = curl_init($url);
            curl_setopt_array($curl, $options);
            $result = $this->parseResponse(curl_exec($curl), $decode);
            if (curl_error($curl)) {
                Mage::logException(
                    new Exception(
                        sprintf(
                            'CURL connection error #%s: %s',
                            curl_errno($curl),
                            curl_error($curl)
                        )
                    )
                );
            }
            curl_close($curl);
        } catch (\Exception $e) {
            /** @var Mage_Adminhtml_Model_Session $session */
            $session = Mage::getSingleton('adminhtml/session');
            $session->addError($helper->__('Something went wrong. Please try again later!'));
        }
        return $result;
    }

    /**
     * @param string $input
     * @param bool $decode
     * @return string|array
     */
    public function parseResponse($input, $decode = true)
    {
        /** @var Nethuns_Nemoexpress_Helper_Data $helper */
        $helper = Mage::helper('nethuns_nemoexpress');

        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        try {
            $response = $decode ? json_decode($input, true) : $input;
        } catch(InvalidArgumentException $e) {
            Mage::logException($e);
            $session->addError($helper->__('Something went wrong. Please try again later!'));
            return '';
        } catch(Exception $e) {
            Mage::logException($e);
            $session->addError($helper->__('Something went wrong. Please try again later!'));
            return '';
        }
        return $response;
    }
}
