<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action;

use CovaTech\Payum\ZaloPay\Request\Api\DoGetListMerchantBanks;
use CovaTech\Payum\ZaloPay\Request\GetListMerchantBanks;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;

final class GetListMerchantBanksAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Refund $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new DoGetListMerchantBanks($model));
    }

    public function supports($request)
    {
        return $request instanceof GetListMerchantBanks && $request->getModel() instanceof \ArrayAccess;
    }
}