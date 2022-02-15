<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ParcelTrap\DHL\DHL;
use ParcelTrap\DTOs\TrackingDetails;
use ParcelTrap\Enums\Status;
use ParcelTrap\ParcelTrap;

it('can add the DHL driver to ParcelTrap', function () {
    $client = ParcelTrap::make(['dhl' => DHL::make(['client_id' => 'abcdefg'])]);
    $client->addDriver('dhl_other', DHL::make(['client_id' => 'abcdefg']));

    expect($client)->hasDriver('dhl')->toBeTrue();
    expect($client)->hasDriver('dhl_other')->toBeTrue();
});

it('can retrieve the DHL driver from ParcelTrap', function () {
    expect(ParcelTrap::make(['dhl' => DHL::make(['client_id' => 'abcdefg'])]))
        ->hasDriver('dhl')->toBeTrue()
        ->driver('dhl')->toBeInstanceOf(DHL::class);
});

it('can call `find` on the DHL driver', function () {
    $trackingDetails = [
        'id' => '7777777770',
        'status' => [
            'statusCode' => 'pre-transit',
            'description' => 'JESSICA',
            'remark' => 'The shipment is pending completion of customs inspection.',
        ],
        'estimatedTimeOfDelivery' => '2022-01-01T00:00:00+00:00',
        'events' => [
            [
                'timestamp' => '2018-03-02T07:53:47Z',
                'location' => [
                    'address' => [
                        'countryCode' => 'NL',
                        'postalCode' => '1043 AG',
                        'addressLocality' => 'Oderweg 2, AMSTERDAM',
                    ],
                ],
                'statusCode' => 'pre-transit',
                'status' => 'DELIVERED',
                'description' => 'JESSICA',
                'pieceIds' => [
                    'JD014600006281230701',
                    'JD014600006281230702',
                    'JD014600006281230703',
                ],
                'remark' => 'The shipment is pending completion of customs inspection.',
                'nextSteps' => 'The status will be updated following customs inspection.',
            ],
        ],
    ];

    $httpMockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode(['shipments' => [$trackingDetails]])),
    ]);

    $handlerStack = HandlerStack::create($httpMockHandler);

    $httpClient = new Client([
        'handler' => $handlerStack,
    ]);

    expect(ParcelTrap::make(['dhl' => DHL::make(['client_id' => 'abcdefg'], $httpClient)])->driver('dhl')->find('7777777770'))
        ->toBeInstanceOf(TrackingDetails::class)
        ->identifier->toBe('7777777770')
        ->status->toBe(Status::Pre_Transit)
        ->status->description()->toBe('Pre-Transit')
        ->summary->toBe('The shipment is pending completion of customs inspection.')
        ->estimatedDelivery->toEqual(new DateTimeImmutable('2022-01-01T00:00:00+00:00'))
        ->raw->toBe($trackingDetails);
});
