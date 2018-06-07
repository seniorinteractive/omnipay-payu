<?php

namespace Omnipay\PayU\Message\Responses;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Class CompletePurchaseResponse
 * @package Omnipay\PayU\Message\Responses
 */
class CompletePurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatus() === $this->getCompleteStatus();
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if (isset($this->getData()['ORDERSTATUS'])) {
            return $this->getData()['ORDERSTATUS'];
        }
    }

    /**
     * @return string
     */
    protected function getCompleteStatus()
    {
        return $this->request->getTestMode() ? 'TEST' : 'COMPLETE';
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        if (isset($this->getData()['REFNOEXT'])) {
            return $this->getData()['REFNOEXT'];
        }
    }

    /**
     * @return null|string
     */
    public function getTransactionReference()
    {
        if (isset($this->getData()['REFNO'])) {
            return $this->getData()['REFNO'];
        }
    }

    /**
     * @return string
     * @throws InvalidResponseException
     */
    public function getReturn()
    {
        $ipnPid = isset($this->getData()['IPN_PID'][0]) ? $this->getData()['IPN_PID'][0] : null;
        $ipnName = isset($this->getData()['IPN_PNAME'][0]) ? $this->getData()['IPN_PNAME'][0] : null;
        $ipnDate = isset($this->getData()['IPN_DATE']) ? $this->getData()['IPN_DATE'] : null;

        if (!$ipnPid || !$ipnName || !$ipnDate) {
            throw new InvalidResponseException('IPN_PID, IPN_PNAME or IPN_DATE is empty.');
        }

        $date = date('YmdHis');

        $hash = $this->request->generateHash(compact('ipnPid', 'ipnName', 'ipnDate', 'date'));

        return '<EPAYMENT>' . $date . '|' . $hash . '</EPAYMENT>';
    }
}
