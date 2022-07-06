<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action;

use CovaTech\Payum\ZaloPay\Request\Api\QueryRefund;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;

final class SyncRefundDetailAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new QueryRefund($model));
    }

    public function supports($request): bool
    {
        if (!$request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();

        return $model instanceof \ArrayAccess
            && !$model->offsetExists('app_trans_id')
            && $model->offsetExists('m_refund_id');
    }
}