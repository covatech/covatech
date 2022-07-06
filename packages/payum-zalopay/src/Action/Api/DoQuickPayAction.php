<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action\Api;

use CovaTech\Payum\ZaloPay\Request\Api\DoQuickPay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class DoQuickPayAction extends AbstractAction
{
    public function execute($request)
    {
        /** @var $request DoQuickPay */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['app_user', 'amount', 'app_trans_id', 'embed_data', 'item', 'payment_code']);

        $details = $this->api->quickPay($model->toUnsafeArray());

        $model->replace($details);
    }

    public function supports($request): bool
    {
        return $request instanceof DoQuickPay && $request->getModel() instanceof \ArrayAccess;
    }
}