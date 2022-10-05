<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\DHL\DHL;
use ParcelTrap\DTOs\TrackingDetails;
use ParcelTrap\Enums\Status;
use ParcelTrap\ParcelTrap;

it('can add the DHL driver to ParcelTrap', function () {
    /** @var ParcelTrap $client */
    $client = $this->app->make(Factory::class);

    $client->extend('dhl_other', fn () => new DHL(clientId: 'abcdefg'));

    expect($client)->driver(DHL::IDENTIFIER)->toBeInstanceOf(DHL::class)
        ->and($client)->driver('dhl_other')->toBeInstanceOf(DHL::class);
});

it('can retrieve the DHL driver from ParcelTrap', function () {
    expect($this->app->make(Factory::class)->driver(DHL::IDENTIFIER))->toBeInstanceOf(DHL::class);
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

    $this->app->make(Factory::class)->extend(DHL::IDENTIFIER, fn () => new DHL(
        clientId: 'abcdefg',
        client: $httpClient,
    ));

    expect($this->app->make(Factory::class)->driver(DHL::IDENTIFIER)->find('7777777770'))
        ->toBeInstanceOf(TrackingDetails::class)
        ->identifier->toBe('7777777770')
        ->status->toBe(Status::Pre_Transit)
        ->status->description()->toBe('Pre-Transit')
        ->summary->toBe('The shipment is pending completion of customs inspection.')
        ->estimatedDelivery->toEqual(new DateTimeImmutable('2022-01-01T00:00:00+00:00'))
        ->raw->toBe($trackingDetails);
});
