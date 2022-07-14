<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\DoRefundAction;
use CovaTech\Payum\ZaloPay\Request\Api\DoRefund;
use Nyholm\Psr7\Factory\HttplugFactory;

class DoRefundActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return DoRefundAction::class;
    }

    public function testExecuteMissingMandatoryFields(): array
    {
        $act = new DoRefundAction();

        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage(
            'The zp_trans_id, m_refund_id, amount, description fields are required'
        );

        $act->execute(new DoRefund([]));
    }

    public function testExecuteCallApi()
    {
        $req = new DoRefund(
            [
                'zp_trans_id' => '1',
                'amount' => '2',
                'm_refund_id' => '3',
                'description' => '4',
            ]
        );
        $act = new DoRefundAction();
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