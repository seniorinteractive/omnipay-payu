<?php

namespace Omnipay\PayU\Message\Requests;

use Omnipay\PayU\Message\Responses\PurchaseResponse;

/**
 * Class PurchaseRequest
 * @package Omnipay\PayU\Message\Requests
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $liveEndpoint = 'https://secure.payu.ro/order/lu.php';

    /**
     * @var string
     */
    protected $testEndpoint = 'https://sandbox.payu.ro/order/lu.php';

    /**
     * @return mixed
     */
    public function getMerchantName()
    {
        return $this->getParameter('merchantName');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMerchantName($value)
    {
        return $this->setParameter('merchantName', $value);
    }

    /**
     * @return mixed
     */
    public function getOrderDate()
    {
        return $this->getParameter('orderDate');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setOrderDate($value)
    {
        return $this->setParameter('orderDate', $value);
    }

    /**
     * @return mixed
     */
    public function getOrderTimeout()
    {
        return $this->getParameter('orderTimeout');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setOrderTimeout($value)
    {
        return $this->setParameter('orderTimeout', $value);
    }

    /**
     * @return mixed
     */
    public function getOrderShipping()
    {
        return $this->getParameter('orderShipping');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setOrderShipping($value)
    {
        return $this->setParameter('orderShipping', $value);
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->getParameter('discount');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDiscount($value)
    {
        return $this->setParameter('discount', $value);
    }

    /**
     * @return mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionId', 'merchantName', 'orderDate', 'items');

        $data['MERCHANT'] = $this->getMerchantName();
        $data['ORDER_REF'] = $this->getTransactionId();
        $data['ORDER_DATE'] = $this->getOrderDate();
        $data['BACK_REF'] = $this->getReturnUrl();
        $data['ORDER_TIMEOUT'] = $this->getOrderTimeout();

        foreach ($this->getItems() as $key => $item) {
            $item->validate(null);

            $data['ORDER_PNAME[' . $key . ']'] = $item->getName();
            $data['ORDER_PCODE[' . $key . ']'] = $item->getCode();
            $data['ORDER_PINFO[' . $key . ']'] = $item->getDescription();
            $data['ORDER_PRICE[' . $key . ']'] = $item->getPrice();
            $data['ORDER_QTY[' . $key . ']'] = $item->getQuantity();
            $data['ORDER_VAT[' . $key . ']'] = $item->getVat();
        }

        $data['ORDER_SHIPPING'] = $this->getOrderShipping();
        $data['PRICES_CURRENCY'] = $this->getCurrency();
        $data['DISCOUNT'] = $this->getDiscount();
        $data['PAY_METHOD'] = $this->getPaymentMethod();

        foreach ($this->getItems() as $key => $item) {
            $data['ORDER_PRICE_TYPE[' . $key . ']'] = $item->getPriceType();
        }

        if ($card = $this->getCard()) {
            $data['BILL_LNAME'] = $card->getBillingLastName();
            $data['BILL_FNAME'] = $card->getBillingFirstName();
            $data['BILL_EMAIL'] = $card->getEmail();
            $data['BILL_PHONE'] = $card->getBillingPhone();
            $data['BILL_COUNTRYCODE'] = $card->getBillingCountry();
        }

        if ($this->getTestMode()) {
            $data['DEBUG'] = 'TRUE';
            $data['TESTORDER'] = 'TRUE';
        }

        $data = $this->filterNullValues($data);

        $data['ORDER_HASH'] = $this->generateHash($data);

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filterNullValues(array $data)
    {
        return array_filter($data, function ($value) {
            return !is_null($value);
        });
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|PurchaseResponse
     */
    public function sendData($data)
    {
        return new PurchaseResponse($this, $data);
    }
}