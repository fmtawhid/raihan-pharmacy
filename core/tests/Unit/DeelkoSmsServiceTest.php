<?php

namespace Tests\Unit;

use App\Services\DeelkoSmsService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class DeelkoSmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $app = new Container();
        $app->instance('config', new Repository([
            'services' => [
                'deelko_sms' => [
                    'api_key' => 'test-key',
                    'masking_id' => 'Rayhan Pharmacy',
                ],
            ],
        ]));
        $app->instance('http', new Factory());
        Container::setInstance($app);
        Facade::setFacadeApplication($app);

        Http::fake([
            'sms.deelko.net/*' => Http::response(['code' => 1000], 200),
        ]);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);

        parent::tearDown();
    }

    public function test_sms_is_sent_through_the_masking_endpoint(): void
    {
        $result = app(DeelkoSmsService::class)->sendMasking('8801700000000', 'Test message');

        $this->assertTrue($result['success']);
        Http::assertSent(fn (Request $request) =>
            str_contains($request->url(), '/smsapi/v2/masking?')
            && $request['api_key'] === 'test-key'
            && $request['mobileNo'] === '8801700000000'
            && $request['smsContent'] === 'Test message'
            && $request['maskingId'] === 'Rayhan Pharmacy'
        );
    }

    public function test_non_masking_service_method_keeps_using_its_existing_endpoint(): void
    {
        $result = app(DeelkoSmsService::class)->send('8801700000000', 'Test message');

        $this->assertTrue($result['success']);
        Http::assertSent(fn (Request $request) =>
            str_contains($request->url(), '/smsapi/v2/non-masking?')
            && !isset($request['maskingId'])
        );
    }
}
