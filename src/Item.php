<?php

namespace Omnipay\PayU;

use Omnipay\Common\Item as BaseItem;
use Omnipay\Common\Exception\InvalidRequestException;

class Item extends BaseItem
{
    /**
     * @return mixed
     */
    public function getVat()
    {
        return $this->getParameter('vat');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setVat($value)
    {
        return $this->setParameter('vat', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriceType()
    {
        return $this->getParameter('priceType');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setOrderPriceType($value)
    {
        return $this->setParameter('priceType', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return $this->getParameter('code');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCode($value)
    {
        return $this->setParameter('code', $value);
    }

    /**
     * @throws InvalidRequestException
     */
    public function validate()
    {
        $requiredParameters = [
            'name',
            'code',
            'price',
            'quantity',
            'vat',
        ];

        foreach ($requiredParameters as $key) {
            if (!isset($this->getParameter($key))) {
                throw new InvalidRequestException("The Item $key parameter is required");
            }
        }
    }
}