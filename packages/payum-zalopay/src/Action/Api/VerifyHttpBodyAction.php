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
                throw $this->makeReplyResponse(400, '`mac` field is invalid.');
            }
        } catch (InvalidArgumentException $e) {
            throw $this->makeReplyResponse(400, $e->getMessage());
        }

        $data = json_decode($request->getBody(), true);

        $model->replace($data);

        throw $this->makeReplyResponse(200, 'OK');
    }

    public function supports($request): bool
    {
        return $request instanceof VerifyHttpBody && $request->getModel() instanceof \ArrayAccess;
    }

    private function makeReplyResponse(int $httpStatus, string $message): HttpResponse
    {
        return new HttpResponse(
            json_encode(
                [
                    'return_message' => $message,
                    'return_code' => (int)(200 === $httpStatus),
                ]
            ),
            $httpStatus,
            [
                'content-type' => 'application/json'
            ]
        );
    }
}