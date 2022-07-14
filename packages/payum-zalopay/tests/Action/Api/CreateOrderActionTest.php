<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\CreateOrderAction;
use CovaTech\Payum\ZaloPay\ApiV2;
use CovaTech\Payum\ZaloPay\Request\Api\CreateOrder;
use Nyholm\Psr7\Factory\HttplugFactory;

class CreateOrderActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return CreateOrderAction::class;
    }

    public function testExecuteMissingMandatoryFields(): array
    {
        $act = new CreateOrderAction();

        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage(
            'The app_user, amount, app_trans_id, embed_data, item, description fields are required'
        );

        $act->execute(new CreateOrder([]));
    }

    public function testExecuteCallApi()
    {
        $req = new CreateOrder(
            [
                'app_user' => '1',
                'amount' => '2',
                'app_trans_id' => '3',
                'description' => '4',
                'embed_data' => '{}',
                'item' => '[]',
            ]
        );
        $act = new CreateOrderAction();
        $apiResponse = (new HttplugFactory())->createResponse(body: json_encode(['return_code' => ApiV2::SUCCESS]));

        $this
            ->apiClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($apiResponse);

        $apiResponse->getBody()->rewind();

        $act->setApi($this->getApiClass());
        $act->execute($req);

        $this->assertEquals(ApiV2::SUCCESS, $req->getModel()['return_code']);
    }
}