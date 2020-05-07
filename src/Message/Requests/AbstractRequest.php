<?php

namespace Omnipay\PayU\Message\Requests;

use Omnipay\Common\Message\AbstractRequest as OmnipayRequest;

/**
 * Class AbstractRequest
 * @package Omnipay\PayU\Message\Requests
 */
abstract class AbstractRequest extends OmnipayRequest
{
    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateHash(array $data)
    {
        return hash_hmac('md5', $this->hasher($data), $this->getSecretKey());
    }

    /**
     * @param array $data
     * @return string
     */
    public function hasher(array $data)
    {
        $ignoredKeys = [
            'AUTOMODE',
            'BACK_REF',
            'DEBUG',
            'BILL_FNAME',
            'BILL_LNAME',
            'BILL_EMAIL',
            'BILL_PHONE',
            'BILL_ADDRESS',
            'BILL_CITY',
            'DELIVERY_FNAME',
            'DELIVERY_LNAME',
            'DELIVERY_PHONE',
            'DELIVERY_ADDRESS',
            'DELIVERY_CITY',
            'LU_ENABLE_TOKEN',
            'LU_TOKEN_TYPE',
            'LANGUAGE',
            'HASH',
        ];

        $hash = '';
        $dataSorted = $this->hashDataSorted($data);
        foreach ($dataSorted as $dataKey => $dataValue) {
            if (is_array($dataValue)) {
                $hash .= $this->hasher($dataValue);
            } elseif (!in_array($dataKey, $ignoredKeys, true)) {
                $hash .= strlen($dataValue) . $dataValue;
            }
        }

        return $hash;
    }

    /**
     * @param array $data
     * @return array
     */
    public function hashDataSorted($data) {

        $exactOrder = [
            'MERCHANT',
            'ORDER_REF',
            'ORDER_DATE',
            'ORDER_PNAME',
            'ORDER_PCODE',
            'ORDER_PINFO',
            'ORDER_PRICE',
            'ORDER_QTY',
            'ORDER_VAT',
            'ORDER_SHIPPING',
            'PRICES_CURRENCY',
            'DISCOUNT',
            'PAY_METHOD',
            'ORDER_PRICE_TYPE',
            'TESTORDER'
        ];
        $sortedData = [];
        foreach ($exactOrder as $value) {
            if(isset($data[$value]) || array_key_exists($value, $data)) {
                $sortedData[$value] = $data[$value];
                continue;
            }

            $likeKey = $this->preg_grep_keys("/$value/", $data);
            if(!empty($likeKey)){
                $sortedData = $sortedData + $likeKey;
            }
        }

        return $sortedData;
    }


    /**
     * @param string $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    function preg_grep_keys($pattern, $input, $flags = 0) {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

}