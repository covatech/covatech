<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\GetListMerchantBanksAction;
use CovaTech\Payum\ZaloPay\Request\Api\DoGetListMerchantBanks;
use CovaTech\Payum\ZaloPay\Request\GetListMerchantBanks;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class GetListMerchantBanksActionTest extends GenericActionTest
{
    protected $actionClass = GetListMerchantBanksAction::class;

    protected $requestClass = GetListMerchantBanks::class;

    public function testExecuteDoGetListMerchantBanks()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new GetListMerchantBanksAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(DoGetListMerchantBanks::class, $req);
                }
            );

        $act->execute(new GetListMerchantBanks([]));
    }
}