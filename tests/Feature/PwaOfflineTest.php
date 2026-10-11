<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaOfflineTest extends TestCase
{
    public function test_ping_is_no_store_and_does_not_start_a_session(): void
    {
        $response = $this->get('/ping');

        $response->assertNoContent();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control', ''));
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }
}
