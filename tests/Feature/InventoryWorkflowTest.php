<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_confirms_a_note_and_stock_is_reduced_only_once(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create(['sku' => 'SEM-001', 'name' => 'Semen', 'unit' => 'sak', 'minimum_stock' => 2]);
        StockBatch::create(['product_id' => $product->id, 'quantity_received' => 10, 'quantity_remaining' => 10, 'unit_cost' => 0, 'received_at' => now()]);

        $this->actingAs($owner)->post(route('stock-out.store'), ['product_id' => $product->id, 'quantity' => 4, 'moved_at' => now()->toDateString()])->assertSessionHas('success');

        $transaction = InventoryTransaction::firstOrFail();
        $this->actingAs($staff)->post(route('stock-out.confirm', $transaction), ['actual_quantity' => 4])->assertSessionHas('success');

        $this->assertDatabaseHas('stock_batches', ['id' => 1, 'quantity_remaining' => 6]);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->actingAs($staff)->post(route('stock-out.confirm', $transaction), ['actual_quantity' => 4])->assertSessionHasErrors('transaction');
        $this->assertDatabaseHas('stock_batches', ['id' => 1, 'quantity_remaining' => 6]);
    }

    public function test_staff_cannot_create_an_outgoing_note(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create(['sku' => 'PAS-001', 'name' => 'Pasir', 'unit' => 'karung', 'minimum_stock' => 1]);

        $this->actingAs($staff)->post(route('stock-out.store'), ['product_id' => $product->id, 'quantity' => 1, 'moved_at' => now()->toDateString()])->assertForbidden();
    }
}
