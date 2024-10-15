<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ProcessPaymentJob;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class PaymentJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful dispatch of a payment job.
     */
    public function test_payment_job_success()
    {
        $user = User::factory()->create();

        Queue::fake();

        $response = $this->actingAs($user)->postJson('/api/payments', [
            'amount' => 200.00,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(201);

        Queue::assertPushed(ProcessPaymentJob::class);
    }
}
