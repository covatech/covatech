<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\QueryRefundAction;
use CovaTech\Payum\ZaloPay\Request\Api\QueryRefund;
use Nyholm\Psr7\Factory\HttplugFactory;

class QueryRefundActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return QueryRefundAction::class;
    }

    public function testExecuteMissingMandatoryFields(): array
    {
        $act = new QueryRefundAction();

        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage(
            'The m_refund_id fields are required'
        );

        $act->execute(new QueryRefund([]));
    }

    public function testExecuteCallApi()
    {
        $req = new QueryRefund(
            [
                'm_refund_id' => '1',
            ]
        );
        $act = new QueryRefundAction();
        $apiResponse = (new HttplugFactory())->createResponse(body: json_encode(['return_code' => 9999]));

        $this
            ->apiClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($apiResponse);

        $apiResponse->getBody()->rewind();

        $act->setApi($this->getApiClass());
        $act->execute($req);

        $this->assertEquals(9999, $req->getModel()['return_code']);
    }
}