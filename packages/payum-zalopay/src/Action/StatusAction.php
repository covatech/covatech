<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        match ($model['return_code']) {
            1 => $request->markCaptured(),
            2 => $request->markFailed(),
            3 => $request->markPending(),
            default => $request->markUnknown()
        };
    }

    public function supports($request): bool
    {
        if (!$request instanceof GetStatusInterface) {
            return false;
        }

        $model = $request->getModel();

        return $model instanceof \ArrayAccess && !$model->offsetExists('m_refund_id');
    }
}