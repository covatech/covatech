<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\DoGetListBankMerchantsAction;
use CovaTech\Payum\ZaloPay\ApiV2;
use CovaTech\Payum\ZaloPay\Request\Api\DoGetListMerchantBanks;
use Nyholm\Psr7\Factory\HttplugFactory;

class DoGetListBankMerchantsActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return DoGetListBankMerchantsAction::class;
    }

    public function testExecuteCallApi()
    {
        $req = new DoGetListMerchantBanks([]);
        $act = new DoGetListBankMerchantsAction();
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