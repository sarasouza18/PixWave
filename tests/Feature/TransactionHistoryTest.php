<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving transaction history for a user.
     */
    public function test_transaction_history_retrieval()
    {
        $user = User::factory()->create();

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'currency' => 'BRL',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}/transactions");

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'transactions');
    }
}
