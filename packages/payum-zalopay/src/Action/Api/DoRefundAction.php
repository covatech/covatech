<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action\Api;

use CovaTech\Payum\ZaloPay\Request\Api\DoRefund;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class DoRefundAction extends AbstractAction
{
    public function execute($request)
    {
        /** @var $request DoRefund */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['zp_trans_id', 'm_refund_id', 'amount', 'description']);

        $details = $this->api->refund($model->toUnsafeArray());

        $model->replace($details);
    }

    public function supports($request): bool
    {
        return $request instanceof DoRefund && $request->getModel() instanceof \ArrayAccess;
    }
}