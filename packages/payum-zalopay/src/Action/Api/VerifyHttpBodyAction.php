<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Action\Api;

use CovaTech\Payum\ZaloPay\Request\Api\VerifyHttpBody;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;

final class VerifyHttpBodyAction extends AbstractAction
{
    public function execute($request)
    {
        /** @var $request VerifyHttpBody */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            if (false === $this->api->verifyHttpBody($request->getBody())) {
                throw new HttpResponse('`mac` field is invalid.', 400);
            }
        } catch (InvalidArgumentException $e) {
            throw new HttpResponse($e->getMessage(), 400);
        }

        $data = json_decode($request->getBody(), true);

        $model->replace($data);

        throw new HttpResponse('OK', 200);
    }

    public function supports($request): bool
    {
        return $request instanceof VerifyHttpBody && $request->getModel() instanceof \ArrayAccess;
    }
}