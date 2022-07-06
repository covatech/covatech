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
use CovaTech\Payum\ZaloPay\Request\Api\CreateOrder;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;

/**
 * @property ApiV2 $api
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();

        $this->gateway->execute(new CreateOrder($model));
    }

    public function supports($request): bool
    {
        if (!$request instanceof Capture) {
            return false;
        }

        $model = $request->getModel();

        return $model instanceof \ArrayAccess;
    }
}