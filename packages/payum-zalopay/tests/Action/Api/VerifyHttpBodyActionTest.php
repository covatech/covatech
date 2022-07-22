<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace CovaTech\Payum\ZaloPay\Tests\Action\Api;

use CovaTech\Payum\ZaloPay\Action\Api\VerifyHttpBodyAction;
use CovaTech\Payum\ZaloPay\ApiV2;
use CovaTech\Payum\ZaloPay\Request\Api\VerifyHttpBody;
use Nyholm\Psr7\Factory\HttplugFactory;
use Payum\Core\Reply\HttpResponse;
use Psr\Http\Client\ClientInterface;

class VerifyHttpBodyActionTest extends AbstractActionTest
{
    protected function getActionClass(): string
    {
        return VerifyHttpBodyAction::class;
    }

    /**
     * @dataProvider throwHttpBadRequestProvider
     */
    public function testExecuteThrowHttpBadRequest(string $body, string $responseBody)
    {
        $act = new VerifyHttpBodyAction();
        $req = new VerifyHttpBody($body, []);

        $act->setApi($this->getApiClass());

        try {
            $act->execute($req);

            throw new \LogicException();
        } catch (HttpResponse $response) {
            $this->assertEquals(400, $response->getStatusCode());
            $this->assertEquals($responseBody, $response->getContent());
        }
    }

    public function throwHttpBadRequestProvider(): array
    {
        return [
            'invalid json' => [
                'body' => '',
                'responseBody' => json_encode(
                    [
                        'return_message' => 'Http body must be a json string.',
                        'return_code' => 0
                    ]
                )
            ],
            'missing data or mac fields' => [
                'body' => json_encode([]),
                'responseBody' => json_encode(
                    [
                        'return_message' => '`mac` and `data` fields should be exist in http body.',
                        'return_code' => 0
                    ]
                )
            ],
            'invalid mac' => [
                'body' => json_encode(['mac' => '1', 'data' => '2']),
                'responseBody' => json_encode(
                    [
                        'return_message' => '`mac` field is invalid.',
                        'return_code' => 0
                    ]
                )
            ]
        ];
    }

    public function testExecuteSuccessful()
    {
        $data = base64_encode(random_bytes(32));
        $key = random_bytes(32);
        $api = new ApiV2(
            [
                'app_id' => '1',
                'key1' => 'key1',
                'key2' => $key,
                'sandbox' => true
            ],
            $this->createMock(ClientInterface::class),
            new HttplugFactory()
        );
        $body = json_encode([
            'mac' => hash_hmac('sha256', $data, $key),
            'data' => $data
        ]);
        $act = new VerifyHttpBodyAction();
        $req = new VerifyHttpBody($body, []);

        $act->setApi($api);

        try {
            $act->execute($req);

            throw new \LogicException();
        } catch (HttpResponse $response) {
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertJson($json = $response->getContent());

            $data = json_decode($json, true);

            $this->assertEquals('OK', $data['return_message']);
            $this->assertEquals(1, $data['return_code']);
        }
    }
}