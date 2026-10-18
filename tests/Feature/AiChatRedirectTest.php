<?php

namespace Tests\Feature;

use Tests\TestCase;

class AiChatRedirectTest extends TestCase
{
    /** @test */
    public function it_redirects_legacy_ai_chat_to_catalog_with_301_and_info_flash(): void
    {
        $response = $this->get('/ai-chat');

        $response->assertStatus(301);
        $response->assertRedirect(route('books.catalog'));
        $response->assertSessionHas('info', 'AI endi har bir kitob ichida');
    }
}
