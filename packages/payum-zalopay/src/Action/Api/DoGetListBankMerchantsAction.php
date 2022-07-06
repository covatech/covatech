<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action\Api;

use CovaTech\Payum\ZaloPay\Request\Api\DoGetListMerchantBanks;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class DoGetListBankMerchantsAction extends AbstractAction
{
    public function execute($request)
    {
        /** @var $request DoGetListMerchantBanks */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $details = $this->api->getListMerchantBanks($model->toUnsafeArray());

        $model->replace($details);
    }

    public function supports($request): bool
    {
        return $request instanceof DoGetListMerchantBanks && $request->getModel() instanceof \ArrayAccess;
    }
}