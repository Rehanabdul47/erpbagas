<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance accounts table for COA hierarchy & segmentation
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('accounts')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1)->after('description');
            $table->boolean('is_header')->default(false)->after('level');
            $table->boolean('is_control')->default(false)->after('is_header');
            $table->boolean('is_active')->default(true)->after('is_control');
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit')->after('is_active');
            $table->string('entity_code', 20)->nullable()->after('normal_balance');
            $table->string('department_code', 20)->nullable()->after('entity_code');
            $table->string('cost_center_code', 20)->nullable()->after('department_code');
        });

        // 2. Fiscal Periods
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Maret 2026", "Q1 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Enhance journal_entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('fiscal_period_id')->nullable()->after('branch_id')->constrained('fiscal_periods')->nullOnDelete();
            $table->enum('status', ['draft', 'posted', 'approved', 'void'])->default('posted')->after('description');
            $table->string('source_type')->nullable()->after('status'); // e.g. App\Models\Sale, App\Models\CashTransaction
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->foreignId('approved_by')->nullable()->after('source_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('cost_center', 50)->nullable()->after('approved_at');
            $table->string('project_code', 50)->nullable()->after('cost_center');
        });

        // 4. Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Anggaran Q1 2026 - Divisi Marketing"
            $table->foreignId('fiscal_period_id')->constrained('fiscal_periods')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('cost_center', 50)->nullable();
            $table->enum('status', ['draft', 'approved', 'active', 'closed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('planned_amount', 18, 2)->default(0);
            $table->decimal('q1_amount', 18, 2)->default(0);
            $table->decimal('q2_amount', 18, 2)->default(0);
            $table->decimal('q3_amount', 18, 2)->default(0);
            $table->decimal('q4_amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Approval Workflows
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // e.g. 'journal_entry', 'budget', 'cash_transaction'
            $table->string('description')->nullable();
            $table->decimal('min_amount', 18, 2)->default(0);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->string('required_role'); // Role name needed for approval
            $table->decimal('auto_approve_below', 18, 2)->default(0); // Auto approve if under this
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('priority')->default(0); // lower = checked first
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type'); // polymorphic
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['status', 'requested_by']);
        });

        // 6. Audit Trails
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // polymorphic
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 50); // created, updated, deleted, posted, voided, approved, rejected
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['fiscal_period_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'fiscal_period_id', 'status', 'source_type', 'source_id',
                'approved_by', 'approved_at', 'cost_center', 'project_code'
            ]);
        });

        Schema::dropIfExists('fiscal_periods');

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id', 'level', 'is_header', 'is_control', 'is_active',
                'normal_balance', 'entity_code', 'department_code', 'cost_center_code'
            ]);
        });
    }
};
