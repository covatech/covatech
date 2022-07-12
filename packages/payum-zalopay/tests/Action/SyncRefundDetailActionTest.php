<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action;

use CovaTech\Payum\ZaloPay\Action\SyncRefundDetailAction;
use CovaTech\Payum\ZaloPay\Request\Api\QueryRefund;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class SyncRefundDetailActionTest extends GenericActionTest
{
    protected $actionClass = SyncRefundDetailAction::class;

    protected $requestClass = Sync::class;

    public function testExecuteQueryTransaction()
    {
        /** @var MockObject|GatewayInterface $gateway */
        $gateway = $this->createGatewayMock();
        $act = new SyncRefundDetailAction();

        $act->setGateway($gateway);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($req) {
                    $this->assertInstanceOf(QueryRefund::class, $req);
                    $this->assertEquals(999, $req->getModel()['m_refund_id']);
                }
            );

        $act->execute(new Sync(['m_refund_id' => 999]));
    }

    public function provideSupportedRequests(): \Iterator
    {
        foreach (parent::provideSupportedRequests() as $args) {
            $args[0]->getModel()['m_refund_id'] = 1;

            yield $args;
        }
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield from parent::provideNotSupportedRequests();
        yield [new Sync(['app_trans_id' => '1'])]; // not support sync transaction.
    }
}