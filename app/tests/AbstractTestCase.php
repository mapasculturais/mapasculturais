<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractTestCase extends TestCase
{
    public HttpClientInterface $client;

    protected function setUp(): void
    {
        $this->client = HttpClient::create()->withOptions([
            'base_uri' => 'http://localhost',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        parent::setUp();
    }

    public function dump(mixed $content): void
    {
        fwrite(STDERR, print_r($content, return: true));
    }
}
