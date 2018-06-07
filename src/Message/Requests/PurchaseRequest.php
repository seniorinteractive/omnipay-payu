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
    public $endpoint = 'https://secure.payu.ru/order/lu.php';

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
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionReference', 'merchantName', 'orderDate', 'items');

        $data['MERCHANT'] = $this->getMerchantName();
        $data['ORDER_REF'] = $this->getTransactionId();
        $data['ORDER_DATE'] = $this->getOrderDate();
        $data['PAY_METHOD'] = $this->getPaymentMethod();
        $data['BACK_REF'] = $this->getReturnUrl();
        $data['ORDER_TIMEOUT'] = $this->getOrderTimeout();
        $data['PRICES_CURRENCY'] = $this->getCurrency();

        foreach ($this->getItems() as $key => $item) {
            $item->validate();

            $data['ORDER_PNAME[' . $key . ']'] = $item->getName();
            $data['ORDER_PCODE[' . $key . ']'] = $item->getCode();
            $data['ORDER_PINFO[' . $key . ']'] = $item->getDescription();
            $data['ORDER_PRICE[' . $key . ']'] = $item->getPrice();
            $data['ORDER_QTY[' . $key . ']'] = $item->getQuantity();
            $data['ORDER_VAT[' . $key . ']'] = $item->getVat();
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
            $data['DEBUG'] = true;
            $data['TESTORDER'] = true;
        }

        $data = $this->filterEmptyValues($data);

        $data['ORDER_HASH'] = $this->generateHash($data);

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filterEmptyValues(array $data)
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