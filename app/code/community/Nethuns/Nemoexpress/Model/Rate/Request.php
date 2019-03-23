<?php

/**
 * @method string getServiceType()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setServiceType(string $value)
 * @method string getRecipient()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setRecipient(string $value)
 * @method string getContactPerson()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setContactPerson(string $value)
 * @method string getPostalCode()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setPostalCode(string $value)
 * @method string getAddress()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setAddress(string $value)
 * @method string getPhone()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setPhone(string $value)
 * @method string getEmail()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setEmail(string $value)
 * @method float getWeight()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setWeight(float $value)
 * @method string getPayer()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setPayer(string $value)
 * @method string getCashOnDeliveryType()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setCashOnDeliveryType(string $value)
 * @method string getBank()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setBank(string $value)
 * @method string getIban()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setIban(string $value)
 * @method float getPackageWeight()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setPackageWeight(float $value)
 * @method float getCashOnDelivery()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setCashOnDelivery(float $value)
 * @method float getDeclaredValue()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setDeclaredValue(float $value)
 * @method float getContent()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setContent(float $value)
 * @method string getObservations()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setObservations(string $value)
 * @method string getSpecialConditions()
 * @method Nethuns_Nemoexpress_Model_Rate_Request setSpecialConditions(string $value)

 *
 */
class Nethuns_Nemoexpress_Model_Rate_Request extends Varien_Object
{
    protected $_packageType;

    const VOLUMETRIC_CONSTANT = 60;

    public function setDefaultPackageNumber()
    {
        $envelopesNumber = 0;
        $packagesNumber = 0;
        $palletsNumber = 0;

        switch ($this->getDefaultPackageType()) {
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                $envelopesNumber = 1;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                $packagesNumber = 1;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                $palletsNumber = 1;
                break;
            default:
                $packagesNumber = 1;
                break;
        }

        $this->setData('envelope_nr', $envelopesNumber);
        $this->setData('package_nr', $packagesNumber);
        $this->setData('pallet_nr', $palletsNumber);
    }

    /**
     * @param Mage_Shipping_Model_Shipment_Request $request
     */
    public function setPackagesDimensions($request)
    {
        $packages = $request->getPackages();
        /*$packages = [
            1 => [
                'params' => [
                    'container' => '',
                    'weight'    => 1,
                    'length'    => 2,
                    'width'     => 3,
                    'height'    => 4,
                    'weight_units'  => 'KILOGRAM',
                    'dimension_units'   => 'CENTIMETER',
                    'content_type'  => '',
                    'content_type_other' => ''
                ],
                'items' => [
                    3 => [
                        'qty' => 1,
                        'customs_value' => 45,
                        'price' => '45.000',
                        'name'  => 'prod name',
                        'weight' => '',
                        'product_id' => '14',
                        'order_item_id' => 3,
                    ]
                ]
            ]
        ];*/

        $envelopesNumber = 0;
        $packagesNumber = 0;
        $palletsNumber = 0;
        $totalWeight = 0;
        $totalVolume = 0;

        foreach($packages as $package) {
            $params = $package['params'];
            switch($params['container']) {
                case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                    $envelopesNumber++;
                    break;
                case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                    $packagesNumber++;
                    break;
                case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                    $palletsNumber++;
                    break;
                default:
                    $packagesNumber++;
            }

            $totalWeight += $params['weight'];
            $totalVolume += $params['length'] * $params['width'] * $params['height'];
        }

        $this->setData('envelope_nr', $envelopesNumber);
        $this->setData('package_nr', $packagesNumber);
        $this->setData('pallet_nr', $palletsNumber);
        $this->setData('weight', $totalWeight);
        $this->setData('volume', $totalVolume / self::VOLUMETRIC_CONSTANT);
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     */
    public function setVolume($request)
    {
        $height = $request->getPackageHeight() ? $request->getPackageHeight() : $this->getDefaultHeight();
        $width = $request->getPackageWidth() ? $request->getPackageWidth() : $this->getDefaultWidth();
        $depth = $request->getPackageDepth() ? $request->getPackageDepth() : $this->getDefaultDepth();

        $this->setData('volume', $height * $width * $depth / self::VOLUMETRIC_CONSTANT);
    }

    /**
     * @return string
     */
    public function getDefaultPackageType()
    {
        return $this->_packageType
            ? $this->_packageType
            : Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_PACKAGE_TYPE);
    }

    /**
     * @return int
     */
    public function getDefaultHeight()
    {
        $default = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_DEFAULT_HEIGHT);

        if ($default) {
            return $default;
        }

        switch ($this->getDefaultPackageType()) {
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                $default = 1;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                $default = 25;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                $default = 150;
                break;
            default:
                $default = 50;
                break;
        }

        return $default;
    }

    /**
     * @return int
     */
    public function getDefaultDepth()
    {
        $default = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_DEFAULT_DEPTH);

        if ($default) {
            return $default;
        }

        switch ($this->getDefaultPackageType()) {
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                $default = 30;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                $default = 25;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                $default = 150;
                break;
            default:
                $default = 50;
                break;
        }

        return $default;
    }

    /**
     * @return int
     */
    public function getDefaultWidth()
    {
        $default = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_DEFAULT_WIDTH);

        if ($default) {
            return $default;
        }

        switch ($this->getDefaultPackageType()) {
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                $default = 20;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                $default = 25;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                $default = 150;
                break;
            default:
                $default = 50;
                break;
        }

        return $default;
    }

    /**
     * @return int|float
     */
    public function getDefaultWeight()
    {
        $default = Mage::getStoreConfig(Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::XML_PATH_DEFAULT_WEIGHT);

        if ($default) {
            return $default;
        }

        switch ($this->getDefaultPackageType()) {
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE:
                $default = 0.5;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE:
                $default = 3;
                break;
            case Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET:
                $default = 50;
                break;
            default:
                $default = 5;
                break;
        }

        return $default;
    }
}
