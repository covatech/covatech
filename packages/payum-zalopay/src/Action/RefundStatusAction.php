<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action;

use CovaTech\Payum\ZaloPay\ApiV2;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class RefundStatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        match ($model['return_code']) {
            ApiV2::SUCCESS => $request->markRefunded(),
            ApiV2::FAIL => $request->markFailed(),
            ApiV2::PROCESSING => $request->markPending(),
            default => $request->markUnknown()
        };
    }

    public function supports($request)
    {
        if (!$request instanceof GetStatusInterface) {
            return false;
        }

        $model = $request->getModel();

        return $model instanceof \ArrayAccess && $model->offsetExists('m_refund_id');
    }
}