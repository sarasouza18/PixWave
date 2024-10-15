<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ProcessPaymentJob;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class PaymentJobRetryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retry mechanism in a payment job failure scenario.
     */
    public function test_payment_job_retry_on_failure()
    {
        $user = User::factory()->create();

        Queue::fake();

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 300.00,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(500);

        Queue::assertPushed(ProcessPaymentJob::class, 3);
    }
}
