<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('products', 'is_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('minimum_stock');
                $table->index(['is_active', 'name']);
            });
        }

        if (! Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->enum('type', ['in', 'out']);
                $table->enum('status', ['draft', 'pending', 'submitted_for_review', 'discrepancy', 'completed', 'cancelled'])->default('draft');
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('transaction_date');
                $table->text('notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                $table->index(['type', 'status', 'transaction_date']);
            });
        }

        if (! Schema::hasTable('inventory_transaction_items')) {
            Schema::create('inventory_transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_transaction_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('requested_quantity');
                $table->unsignedInteger('actual_quantity')->nullable();
                $table->text('discrepancy_notes')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasIndex('inventory_transaction_items', 'inv_transaction_product_unique')) {
            Schema::table('inventory_transaction_items', fn (Blueprint $table) => $table->unique(['inventory_transaction_id', 'product_id'], 'inv_transaction_product_unique'));
        }

        if (! Schema::hasColumn('stock_movements', 'inventory_transaction_item_id')) {
            Schema::table('stock_movements', fn (Blueprint $table) => $table->foreignId('inventory_transaction_item_id')->nullable()->after('stock_batch_id')->constrained()->nullOnDelete());
        }
        if (! Schema::hasColumn('stock_movements', 'stock_before')) {
            Schema::table('stock_movements', fn (Blueprint $table) => $table->unsignedInteger('stock_before')->nullable()->after('quantity'));
        }
        if (! Schema::hasColumn('stock_movements', 'stock_after')) {
            Schema::table('stock_movements', fn (Blueprint $table) => $table->unsignedInteger('stock_after')->nullable()->after('stock_before'));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_transaction_item_id');
            $table->dropColumn(['stock_before', 'stock_after']);
        });
        Schema::dropIfExists('inventory_transaction_items');
        Schema::dropIfExists('inventory_transactions');
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'name']);
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('is_active');
        });
        Schema::dropIfExists('categories');
    }
};
