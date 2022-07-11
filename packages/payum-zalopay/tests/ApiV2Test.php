<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests;

use CovaTech\Payum\ZaloPay\ApiV2;
use Nyholm\Psr7\Factory\HttplugFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiV2Test extends TestCase
{
    public function testConstructorMissingRequireOptions()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The app_id, key1, key2, sandbox fields are required.');

        new ApiV2([], $this->createMock(ClientInterface::class), new HttplugFactory());
    }

    /**
     * @dataProvider mandatoryFieldsMissingProvider
     */
    public function testCallApisMissingMandatoryFields(string $method, string $exceptionMessage)
    {
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => '1',
                'key2' => '2',
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $this->assertTrue(method_exists($api, $method));
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage($exceptionMessage);

        call_user_func([$api, $method], []);
    }

    public function mandatoryFieldsMissingProvider(): array
    {
        return [
            'create order' => [
                'createOrder',
                'The app_trans_id, app_user, amount, embed_data, item fields are required.'
            ],
            'quick pay' => [
                'quickPay',
                'The app_trans_id, app_user, amount, embed_data, item, payment_code fields are required.'
            ],
            'refund' => [
                'refund',
                'The zp_trans_id, amount, description fields are required.'
            ],
            'query transaction' => [
                'queryTransaction',
                'The app_trans_id fields are required.'
            ],
            'query refund' => [
                'queryRefund',
                'The m_refund_id fields are required.'
            ]
        ];
    }

    /**
     * @dataProvider httpRequestAssertionProvider
     */
    public function testAssertHttpRequests(string $method, array $fields, callable $sendRequestReturnCallback)
    {
        static $publicKey;

        $publicKey ??= openssl_pkey_get_details(openssl_pkey_new())['key'];
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => '1',
                'key2' => '2',
                'sandbox' => true,
                'public_key' => $publicKey
            ],
            $client = $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $this->assertTrue(method_exists($api, $method));

        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback($sendRequestReturnCallback);

        $details = call_user_func([$api, $method], $fields);

        $this->assertSame(ApiV2::SUCCESS, $details['return_code']);
    }

    public function httpRequestAssertionProvider(): array
    {
        $cases = [
            'get list merchant banks successful' => [
                'getListMerchantBanks',
                [],
                function (array $fields) {
                    $this->assertArrayHasKey('appid', $fields);
                    $this->assertArrayHasKey('reqtime', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'get list merchant banks can not replace `appid`' => [
                'getListMerchantBanks',
                [
                    'appid' => 99,
                ],
                function (array $fields) {
                    $this->assertNotEquals(99, $fields['appid']);
                }
            ],
            'get list merchant banks can replace `reqtime`' => [
                'getListMerchantBanks',
                [
                    'reqtime' => 99,
                ],
                function (array $fields) {
                    $this->assertEquals(99, $fields['reqtime']);
                }
            ],
            'quick pay successful' => [
                'quickPay',
                [
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'payment_code' => 'code',
                    'embed_data' => '{}',
                    'item' => '[]',
                ],
                function (array $fields) {
                    $this->assertEquals('id', $fields['app_id']);
                    $this->assertEquals(10000, $fields['amount']);
                    $this->assertEquals(3, $fields['app_trans_id']);
                    $this->assertNotEquals('code', $fields['payment_code']);
                    $this->assertEquals('{}', $fields['embed_data']);
                    $this->assertEquals('[]', $fields['item']);
                    $this->assertArrayHasKey('app_id', $fields);
                    $this->assertArrayHasKey('app_time', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'quick pay can not replace `app_id`' => [
                'quickPay',
                [
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'payment_code' => 'code',
                    'embed_data' => '{}',
                    'item' => '[]',
                    'app_id' => 4,
                ],
                function (array $fields) {
                    $this->assertNotEquals(4, $fields['app_id']);
                }
            ],
            'quick pay can replace `app_time`' => [
                'quickPay',
                [
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'payment_code' => 'code',
                    'embed_data' => '{}',
                    'item' => '[]',
                    'app_time' => 4,
                ],
                function (array $fields) {
                    $this->assertEquals(4, $fields['app_time']);
                }
            ],
            'refund successful' => [
                'refund',
                [
                    'zp_trans_id' => 1,
                    'amount' => 2,
                    'description' => 3
                ],
                function (array $fields) {
                    $this->assertEquals(1, $fields['zp_trans_id']);
                    $this->assertEquals(2, $fields['amount']);
                    $this->assertEquals(3, $fields['description']);
                    $this->assertArrayHasKey('app_id', $fields);
                    $this->assertArrayHasKey('timestamp', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'refund can not replace `app_id`' => [
                'refund',
                [
                    'zp_trans_id' => 1,
                    'amount' => 2,
                    'description' => 3,
                    'app_id' => 4,
                ],
                function (array $fields) {
                    $this->assertNotEquals(4, $fields['app_id']);
                }
            ],
            'refund can replace `timestamp`' => [
                'refund',
                [
                    'zp_trans_id' => 1,
                    'amount' => 2,
                    'description' => 3,
                    'timestamp' => 4,
                ],
                function (array $fields) {
                    $this->assertEquals(4, $fields['timestamp']);
                }
            ],
            'query refund successful' => [
                'queryRefund',
                [
                    'm_refund_id' => 1,
                ],
                function (array $fields) {
                    $this->assertEquals(1, $fields['m_refund_id']);
                    $this->assertArrayHasKey('app_id', $fields);
                    $this->assertArrayHasKey('timestamp', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'query refund can not replace `app_id`' => [
                'queryRefund',
                [
                    'app_id' => 5,
                    'm_refund_id' => 1,
                ],
                function (array $fields) {
                    $this->assertNotEquals(5, $fields['app_id']);
                }
            ],
            'query refund can replace `timestamp`' => [
                'queryRefund',
                [
                    'timestamp' => 5,
                    'm_refund_id' => 1,
                ],
                function (array $fields) {
                    $this->assertEquals(5, $fields['timestamp']);
                }
            ],
            'query transaction successful' => [
                'queryTransaction',
                [
                    'app_trans_id' => 3,
                ],
                function (array $fields) {
                    $this->assertEquals(3, $fields['app_trans_id']);
                    $this->assertArrayHasKey('app_id', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'query transaction can not replace `app_id`' => [
                'queryTransaction',
                [
                    'app_id' => 5,
                    'app_trans_id' => 3,
                ],
                function (array $fields) {
                    $this->assertNotEquals(5, $fields['app_id']);
                }
            ],
            'create order successful' => [
                'createOrder',
                [
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'embed_data' => '{}',
                    'item' => '[]',
                ],
                function (array $fields) {
                    $this->assertEquals('id', $fields['app_id']);
                    $this->assertEquals(10000, $fields['amount']);
                    $this->assertEquals(3, $fields['app_trans_id']);
                    $this->assertEquals('{}', $fields['embed_data']);
                    $this->assertEquals('[]', $fields['item']);
                    $this->assertArrayHasKey('app_id', $fields);
                    $this->assertArrayHasKey('app_time', $fields);
                    $this->assertArrayHasKey('mac', $fields);
                }
            ],
            'create order can replace `app_time`' => [
                'createOrder',
                [
                    'createOrder',
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'embed_data' => '{}',
                    'item' => '[]',
                    'app_time' => 1
                ],
                function (array $fields) {
                    $this->assertEquals(1, $fields['app_time']);
                }
            ],
            'create order can not replace `app_id`' => [
                'createOrder',
                [
                    'app_id' => 2,
                    'app_user' => 1,
                    'amount' => 10000,
                    'app_trans_id' => 3,
                    'embed_data' => '{}',
                    'item' => '[]',
                    'app_time' => 1
                ],
                function (array $fields) {
                    $this->assertNotEquals(2, $fields['app_id']);
                }
            ]
        ];

        return array_map(function (array $case) {
            $asserter = $case[2];
            $case[2] = function (RequestInterface $request) use ($asserter): ResponseInterface {
                $body = $request->getBody();

                $body->rewind();

                parse_str($body->getContents(), $fields);

                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('application/x-www-form-urlencoded', $request->getHeader('content-type')[0]);

                $asserter($fields);

                $response = (new HttplugFactory())->createResponse(body: json_encode(['return_code' => 1]));

                $response->getBody()->rewind();

                return $response;
            };

            return $case;
        }, $cases);
    }

    public function testGenerateMac()
    {
        $key = random_bytes(32);
        $expected = hash_hmac('sha256', 'a|b|c', $key);
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => $key,
                'key2' => $key,
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $ref = new \ReflectionMethod($api, 'generateMac');
        $ref->setAccessible(true);

        $allFieldsMac = $ref->getClosure($api)->call($api, ['a', 'b', 'c']);

        $this->assertSame($expected, $allFieldsMac);

        $someFieldsMac = $ref->getClosure($api)->call($api, ['b', 'a', 'd', 'c', 'e'], [1, 0, 3]);

        $this->assertSame($expected, $someFieldsMac);
    }

    public function testPublicEncryptWithoutPublicKeyOptionExceptionWillBeThrow()
    {
        $api = new ApiV2(
            [
                'app_id' => '1',
                'key1' => '2',
                'key2' => '3',
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $ref = new \ReflectionMethod($api, 'publicEncrypt');
        $ref->setAccessible(true);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("`public_key` must be set to encrypt data.");

        $ref->getClosure($api)->call($api, 'test');
    }

    public function testPublicEncryptWithInvalidPublicKeyOptionExceptionWillBeThrow()
    {
        $api = new ApiV2(
            [
                'app_id' => '1',
                'key1' => '2',
                'key2' => '3',
                'public_key' => '4',
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $ref = new \ReflectionMethod($api, 'publicEncrypt');
        $ref->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Fail to encrypt data with `public_key` given.");

        $ref->getClosure($api)->call($api, 'test');
    }

    public function testPublicEncrypt()
    {
        $data = random_bytes(32);
        $privateKey = openssl_pkey_new();
        $publicKey = openssl_pkey_get_details($privateKey)['key'];
        $api = new ApiV2(
            [
                'app_id' => '1',
                'key1' => '2',
                'key2' => '3',
                'public_key' => $publicKey,
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $ref = new \ReflectionMethod($api, 'publicEncrypt');
        $ref->setAccessible(true);

        $dataEncrypted = base64_decode($ref->getClosure($api)->call($api, $data));
        $isDecryptedSuccessful = openssl_private_decrypt($dataEncrypted, $dataDecrypted, $privateKey);

        $this->assertTrue($isDecryptedSuccessful);
        $this->assertSame($data, $dataDecrypted);
    }

    public function testVerifyHttpRequest()
    {
        $key = random_bytes(32);
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => 'key1',
                'key2' => $key,
                'sandbox' => true,
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $data = 'test';
        $mac = hash_hmac('sha256', $data, $key);
        $validMacResult = $api->verifyHttpBody(json_encode(['data' => $data, 'mac' => $mac]));
        $invalidMacResult = $api->verifyHttpBody(json_encode(['data' => $data, 'mac' => 'invalid']));

        $this->assertTrue($validMacResult);
        $this->assertFalse($invalidMacResult);
    }

    /**
     * @dataProvider verifyHttpRequestHadInvalidBodyProvider
     */
    public function testVerifyHttpRequestWithInvalidBody(string $content, string $exceptionMessage)
    {
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => 'key1',
                'key2' => 'key2',
                'sandbox' => true,
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $api->verifyHttpBody($content);
    }

    public function verifyHttpRequestHadInvalidBodyProvider(): array
    {
        return [
            'invalid json' => [
                'invalid',
                'Http body must be a json string.'
            ],
            'missing mac field' => [
                json_encode(['data' => 'data']),
                '`mac` and `data` fields should be exist in http body.'
            ],
            'missing data field' => [
                json_encode(['mac' => 'mac']),
                '`mac` and `data` fields should be exist in http body.'
            ]
        ];
    }

    public function testDoRequest()
    {
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => 'key1',
                'key2' => 'key2',
                'sandbox' => true,
            ],
            $client = $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );
        $response = (new HttplugFactory())->createResponse(body: json_encode(['return_code' => ApiV2::SUCCESS]));
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $response->getBody()->rewind();

        $ref = new \ReflectionMethod($api, 'doRequest');
        $ref->setAccessible(true);
        $result = $ref->getClosure($api)->call($api, $this->createMock(RequestInterface::class));

        $this->assertArrayHasKey('return_code', $result);
        $this->assertEquals(ApiV2::SUCCESS, $result['return_code']);
    }

    public function testDoRequestError()
    {
        $api = new ApiV2(
            [
                'app_id' => 'id',
                'key1' => 'key1',
                'key2' => 'key2',
                'sandbox' => true,
            ],
            $client = $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );
        $response = (new HttplugFactory())->createResponse(400);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $this->expectException(\Payum\Core\Exception\Http\HttpException::class);

        $ref = new \ReflectionMethod($api, 'doRequest');
        $ref->setAccessible(true);
        $ref->getClosure($api)->call($api, $this->createMock(RequestInterface::class));
    }
}