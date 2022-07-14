<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\QueryTransactionAction;
use CovaTech\Payum\ZaloPay\Request\Api\QueryTransaction;
use Nyholm\Psr7\Factory\HttplugFactory;

class QueryTransactionActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return QueryTransactionAction::class;
    }

    public function testExecuteMissingMandatoryFields(): array
    {
        $act = new QueryTransactionAction();

        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage(
            'Can not query transaction without `app_trans_id`.'
        );

        $act->execute(new QueryTransaction([]));
    }

    public function testExecuteCallApi()
    {
        $req = new QueryTransaction(
            [
                'app_trans_id' => '1',
            ]
        );
        $act = new QueryTransactionAction();
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