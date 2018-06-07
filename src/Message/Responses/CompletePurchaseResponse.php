<?php

namespace Omnipay\PayU\Message\Responses;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

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
        return isset($this->getData()['ORDERSTATUS'])
            && $this->getData()['ORDERSTATUS'] === $this->getCompleteStatus();
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
    public function getReturn()
    {
        $ipnPid = isset($this->getData()['IPN_PID'][0]) ? $this->getData()['IPN_PID'][0] : null;
        $ipnName = isset($this->getData()['IPN_PNAME'][0]) ? $this->getData()['IPN_PNAME'][0] : null;
        $ipnDate = isset($this->getData()['IPN_DATE']) ? $this->getData()['IPN_DATE'] : null;
        $date = date('YmdHis');

        $hash = $this->request->generateHash(compact('ipnPid', 'ipnName', 'ipnDate', 'date'));

        return '<EPAYMENT>' . $date . '|' . $hash . '</EPAYMENT>';
    }
}
