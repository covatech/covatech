<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\SyncAction;
use CovaTech\Payum\ZaloPay\Request\Api\QueryTransaction;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class SyncActionTest extends GenericActionTest
{
    protected $actionClass = SyncAction::class;

    protected $requestClass = Sync::class;

    public function testExecuteQueryTransaction()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new SyncAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(QueryTransaction::class, $req);
                    $this->assertEquals(999, $req->getModel()['app_trans_id']);
                }
            );

        $act->execute(new Sync(['app_trans_id' => 999]));
    }

    public function provideSupportedRequests(): \Iterator
    {
        foreach (parent::provideSupportedRequests() as $args) {
            $args[0]->getModel()['app_trans_id'] = 1;

            yield $args;
        }
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield from parent::provideNotSupportedRequests();
        yield [new Sync(['m_refund_id' => '1'])]; // not support sync refund.
    }
}