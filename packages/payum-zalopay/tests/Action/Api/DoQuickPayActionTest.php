<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\DoQuickPayAction;
use CovaTech\Payum\ZaloPay\Request\Api\DoQuickPay;
use Nyholm\Psr7\Factory\HttplugFactory;

class DoQuickPayActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return DoQuickPayAction::class;
    }

    public function testExecuteMissingMandatoryFields(): array
    {
        $act = new DoQuickPayAction();

        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage(
            'The app_user, amount, app_trans_id, embed_data, item, description, payment_code fields are required'
        );

        $act->execute(new DoQuickPay([]));
    }

    public function testExecuteCallApi()
    {
        $req = new DoQuickPay(
            [
                'app_user' => '1',
                'amount' => '2',
                'app_trans_id' => '3',
                'description' => '4',
                'payment_code' => '5',
                'embed_data' => '{}',
                'item' => '[]',
            ]
        );
        $act = new DoQuickPayAction();
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