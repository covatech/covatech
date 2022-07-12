<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\NotifyAction;
use CovaTech\Payum\ZaloPay\Request\Api\VerifyHttpBody;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

    public function testExecuteDoGetListMerchantBanks()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new NotifyAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(
                    function ($req) {
                        $this->assertInstanceOf(GetHttpRequest::class, $req);
                        $req->content = 'test';
                    }
                ),
                $this->returnCallback(
                    function ($req) {
                        $this->assertInstanceOf(VerifyHttpBody::class, $req);
                        $this->assertEquals('test', $req->getBody());
                    }
                )
            );

        $act->execute(new Notify([]));
    }
}