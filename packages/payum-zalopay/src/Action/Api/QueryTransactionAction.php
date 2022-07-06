<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action\Api;

use CovaTech\Payum\ZaloPay\Request\Api\QueryTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class QueryTransactionAction extends AbstractAction
{
    public function execute($request)
    {
        /** @var $request QueryTransaction */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['app_trans_id']) {
            throw new \LogicException('Can not query transaction without `app_trans_id`.');
        }

        $details = $this->api->queryTransaction($model->toUnsafeArray());

        $model->replace($details);
    }

    public function supports($request): bool
    {
        return $request instanceof QueryTransaction && $request->getModel() instanceof \ArrayAccess;
    }
}