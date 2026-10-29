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
                    'masking_id' => 'RN PHARMACY',
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
            && $request['maskingID'] === 'RN PHARMACY'
        );
    }

    public function test_non_masking_service_method_keeps_using_its_existing_endpoint(): void
    {
        $result = app(DeelkoSmsService::class)->send('8801700000000', 'Test message');

        $this->assertTrue($result['success']);
        Http::assertSent(fn (Request $request) =>
            str_contains($request->url(), '/smsapi/v2/non-masking?')
            && !isset($request['maskingID'])
        );
    }

    public function test_masking_sms_normalizes_a_local_bangladesh_mobile_number(): void
    {
        $result = app(DeelkoSmsService::class)->sendMasking('01603747235', 'Test message');

        $this->assertTrue($result['success']);
        Http::assertSent(fn (Request $request) =>
            $request['mobileNo'] === '8801603747235'
        );
    }

    public function test_masking_sms_rejects_an_invalid_mobile_without_calling_the_provider(): void
    {
        $result = app(DeelkoSmsService::class)->sendMasking('0160374723', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('valid Bangladesh mobile number', $result['message']);
        Http::assertNothingSent();
    }
}
