<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\ApiV2;
use Nyholm\Psr7\Factory\HttplugFactory;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Psr\Http\Client\ClientInterface;

abstract class AbstractActionTest extends GenericApiAwareActionTest
{
    protected ClientInterface $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ClientInterface::class);
    }

    protected function getApiClass()
    {
        static $publicKey;
        $publicKey ??= openssl_pkey_get_details(openssl_pkey_new())['key'];

        return new ApiV2(
            [
                'app_id' => '1',
                'key1' => '2',
                'key2' => '3',
                'sandbox' => true,
                'public_key' => $publicKey
            ],
            $this->apiClient,
            new HttplugFactory()
        );
    }
}