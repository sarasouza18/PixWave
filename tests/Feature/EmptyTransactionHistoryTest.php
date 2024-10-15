<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmptyTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving empty transaction history for a user.
     */
    public function test_empty_transaction_history_retrieval()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/transactions");

        $response->assertStatus(200);

        $response->assertJsonCount(0, 'transactions');
    }
}
