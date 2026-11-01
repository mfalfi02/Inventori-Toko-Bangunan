<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_deactivate_an_existing_product(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $product = Product::create(['sku' => 'CAT-001', 'name' => 'Cat', 'unit' => 'kaleng', 'minimum_stock' => 2]);

        $this->actingAs($owner)->put(route('products.update', $product), ['sku' => 'CAT-001', 'name' => 'Cat Interior', 'unit' => 'kaleng', 'minimum_stock' => 3, 'is_active' => false])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Cat Interior', 'is_active' => false]);
    }

    public function test_staff_cannot_update_a_product(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create(['sku' => 'BES-001', 'name' => 'Besi', 'unit' => 'batang', 'minimum_stock' => 2]);

        $this->actingAs($staff)->put(route('products.update', $product), ['sku' => 'BES-001', 'name' => 'Besi', 'unit' => 'batang', 'minimum_stock' => 2, 'is_active' => true])->assertForbidden();
    }

    public function test_product_list_filters_empty_low_and_available_stock(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Product::create(['sku' => 'EMP-001', 'name' => 'Kosong', 'unit' => 'pcs', 'minimum_stock' => 1]);
        $low = Product::create(['sku' => 'REN-001', 'name' => 'Menipis', 'unit' => 'pcs', 'minimum_stock' => 5]);
        $available = Product::create(['sku' => 'ADA-001', 'name' => 'Tersedia', 'unit' => 'pcs', 'minimum_stock' => 2]);
        $low->batches()->create(['quantity_received' => 3, 'quantity_remaining' => 3, 'unit_cost' => 0, 'received_at' => now()]);
        $available->batches()->create(['quantity_received' => 6, 'quantity_remaining' => 6, 'unit_cost' => 0, 'received_at' => now()]);

        $this->actingAs($owner)->get(route('products.index', ['stock_status' => 'low']))->assertSee('Menipis')->assertDontSee('Tersedia')->assertDontSee('Kosong');
    }

    public function test_owner_can_create_a_category(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->post(route('categories.store'), ['name' => 'Material Bangunan'])->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Material Bangunan']);
    }
}
