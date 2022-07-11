<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests;

use CovaTech\Payum\ZaloPay\GatewayFactory;
use Payum\Core\Tests\AbstractGatewayFactoryTest;

class GatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new GatewayFactory(
            [
                'foo' => 'fooVal',
                'bar' => 'barVal',
            ]
        );

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            [
                'app_id' => '',
                'key1' => '',
                'key2' => '',
                'public_key' => null,
                'sandbox' => true
            ],
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('zalopay', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('ZaloPay', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The app_id, key1, key2 fields are required.');
        $factory = new GatewayFactory();

        $factory->create();
    }

    protected function getGatewayFactoryClass(): string
    {
        return GatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'app_id' => 'a',
            'key1' => 'b',
            'key2' => 'c'
        ];
    }
}