<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\CompanyFile;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\DepotTransferItem;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTier;
use App\Models\OtpCode;
use App\Models\Plan;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User    $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->company = Company::factory()->create([
            'slug'                => 'test-advanced',
            'subscription_status' => 'active',
        ]);

        $this->user = User::factory()->create(['phone' => '+221770000001']);
        $this->company->users()->attach($this->user->id, ['role' => 'owner']);
    }

    // ══════════════════════════════════════════════════════════
    // 4E.1 — OTP Authentication
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function otp_send_creates_code_for_existing_phone(): void
    {
        $response = $this->post('/otp/send', ['phone' => '+221770000001']);
        $response->assertRedirect();

        $this->assertDatabaseHas('otp_codes', [
            'phone' => '+221770000001',
        ]);

        $otp = OtpCode::where('phone', '+221770000001')->first();
        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp->code));
        $this->assertTrue($otp->expires_at->isFuture());
    }

    #[Test]
    public function otp_send_fails_for_unknown_phone(): void
    {
        $response = $this->post('/otp/send', ['phone' => '+221999999999']);
        $response->assertSessionHasErrors('phone');
    }

    #[Test]
    public function otp_verify_authenticates_with_valid_code(): void
    {
        $otp = OtpCode::generate('+221770000001');

        $response = $this->post('/otp/verify', [
            'phone' => '+221770000001',
            'code'  => $otp->code,
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->user);

        // Code should be marked as used
        $otp->refresh();
        $this->assertNotNull($otp->used_at);
    }

    #[Test]
    public function otp_verify_fails_with_expired_code(): void
    {
        $otp = OtpCode::create([
            'phone'      => '+221770000001',
            'code'       => '123456',
            'expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->post('/otp/verify', [
            'phone' => '+221770000001',
            'code'  => '123456',
        ]);

        $response->assertSessionHasErrors('code');
    }

    #[Test]
    public function otp_verify_fails_with_wrong_code(): void
    {
        OtpCode::generate('+221770000001');

        $response = $this->post('/otp/verify', [
            'phone' => '+221770000001',
            'code'  => '000000',
        ]);

        $response->assertSessionHasErrors('code');
    }

    #[Test]
    public function otp_generate_invalidates_previous_codes(): void
    {
        $first = OtpCode::generate('+221770000001');
        $second = OtpCode::generate('+221770000001');

        $this->assertDatabaseMissing('otp_codes', ['id' => $first->id]);
        $this->assertDatabaseHas('otp_codes', ['id' => $second->id]);
    }

    // ══════════════════════════════════════════════════════════
    // 4E.2 — Storage / File Management
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function storage_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/app/storage?_tenant=test-advanced')
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p->component('App/Storage/Index'));
    }

    #[Test]
    public function file_upload_creates_record(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($this->user)
            ->post('/app/storage/upload?_tenant=test-advanced', [
                'file'   => $file,
                'folder' => 'invoices',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('company_files', [
            'company_id'    => $this->company->id,
            'original_name' => 'document.pdf',
            'folder'        => 'invoices',
        ]);
    }

    #[Test]
    public function file_upload_rejects_over_quota(): void
    {
        Storage::fake('public');

        // Create a plan with tiny limit
        $plan = Plan::factory()->create(['max_storage_gb' => 1, 'is_active' => true]);
        Subscription::factory()->create([
            'company_id' => $this->company->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'starts_at'  => now()->subDay(),
            'ends_at'    => now()->addMonth(),
        ]);

        // Already have a file that nearly fills the 1 GB quota
        CompanyFile::create([
            'company_id'    => $this->company->id,
            'uploaded_by'   => $this->user->id,
            'original_name' => 'big.zip',
            'disk'          => 'public',
            'path'          => 'test/big.zip',
            'size'          => 1073741824, // exactly 1 GB
            'folder'        => 'general',
        ]);

        $file = UploadedFile::fake()->create('extra.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post('/app/storage/upload?_tenant=test-advanced', [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    #[Test]
    public function file_delete_removes_record(): void
    {
        Storage::fake('public');

        $cf = CompanyFile::create([
            'company_id'    => $this->company->id,
            'uploaded_by'   => $this->user->id,
            'original_name' => 'temp.txt',
            'disk'          => 'public',
            'path'          => 'test/temp.txt',
            'size'          => 100,
            'folder'        => 'general',
        ]);

        $this->actingAs($this->user)
            ->delete("/app/storage/{$cf->id}?_tenant=test-advanced")
            ->assertRedirect();

        $this->assertDatabaseMissing('company_files', ['id' => $cf->id]);
    }

    #[Test]
    public function file_delete_forbidden_for_other_company(): void
    {
        $other = Company::factory()->create(['slug' => 'other-co']);
        $cf = CompanyFile::create([
            'company_id'    => $other->id,
            'original_name' => 'secret.pdf',
            'disk'          => 'public',
            'path'          => 'other/secret.pdf',
            'size'          => 100,
            'folder'        => 'general',
        ]);

        $this->actingAs($this->user)
            ->delete("/app/storage/{$cf->id}?_tenant=test-advanced")
            ->assertStatus(403);
    }

    // ══════════════════════════════════════════════════════════
    // 4E.3 — Loyalty Tiers
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function loyalty_page_includes_tiers(): void
    {
        // Need boutique module
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $this->actingAs($this->user)
            ->get('/app/boutique/loyalty?_tenant=test-advanced')
            ->assertStatus(200)
            ->assertInertia(fn ($p) => $p
                ->component('App/Boutique/Loyalty/Index')
                ->has('tiers')
            );
    }

    #[Test]
    public function can_create_loyalty_tier(): void
    {
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $this->actingAs($this->user)
            ->post('/app/boutique/loyalty/tiers?_tenant=test-advanced', [
                'name'             => 'Gold',
                'min_points'       => 500,
                'bonus_multiplier' => 2.0,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('loyalty_tiers', [
            'company_id' => $this->company->id,
            'name'       => 'Gold',
            'min_points' => 500,
        ]);
    }

    #[Test]
    public function can_delete_loyalty_tier(): void
    {
        $tier = LoyaltyTier::create([
            'company_id'       => $this->company->id,
            'name'             => 'Silver',
            'min_points'       => 200,
            'bonus_multiplier' => 1.5,
        ]);

        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $this->actingAs($this->user)
            ->delete("/app/boutique/loyalty/tiers/{$tier->id}?_tenant=test-advanced")
            ->assertRedirect();

        $this->assertDatabaseMissing('loyalty_tiers', ['id' => $tier->id]);
    }

    #[Test]
    public function resolve_tier_for_points(): void
    {
        LoyaltyTier::create(['company_id' => $this->company->id, 'name' => 'Bronze', 'min_points' => 0, 'bonus_multiplier' => 1]);
        LoyaltyTier::create(['company_id' => $this->company->id, 'name' => 'Silver', 'min_points' => 100, 'bonus_multiplier' => 1.5]);
        LoyaltyTier::create(['company_id' => $this->company->id, 'name' => 'Gold', 'min_points' => 500, 'bonus_multiplier' => 2]);

        $tier = LoyaltyTier::resolveForPoints($this->company->id, 250);
        $this->assertEquals('Silver', $tier->name);

        $tier = LoyaltyTier::resolveForPoints($this->company->id, 600);
        $this->assertEquals('Gold', $tier->name);

        $tier = LoyaltyTier::resolveForPoints($this->company->id, 10);
        $this->assertEquals('Bronze', $tier->name);
    }

    // ══════════════════════════════════════════════════════════
    // 4E.4 — Transfer Approval Workflow
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function store_transfer_creates_pending_status(): void
    {
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $depotA = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Dépôt A']);
        $depotB = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Dépôt B']);
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $depotA->id, 'quantity' => 50]);

        $this->actingAs($this->user)
            ->post('/app/boutique/transfers?_tenant=test-advanced', [
                'from_depot_id' => $depotA->id,
                'to_depot_id'   => $depotB->id,
                'items'         => [
                    ['product_id' => $product->id, 'variant_id' => null, 'quantity' => 10],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('depot_transfers', [
            'company_id' => $this->company->id,
            'status'     => 'pending',
        ]);

        // Stock should NOT change yet (still pending)
        $this->assertEquals(50, StockItem::where('product_id', $product->id)->where('depot_id', $depotA->id)->first()->quantity);
    }

    #[Test]
    public function approve_transfer_moves_stock(): void
    {
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $depotA = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Source']);
        $depotB = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Dest']);
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $depotA->id, 'quantity' => 100]);

        $transfer = DepotTransfer::create([
            'company_id'    => $this->company->id,
            'from_depot_id' => $depotA->id,
            'to_depot_id'   => $depotB->id,
            'user_id'       => $this->user->id,
            'reference'     => 'TRF-TEST001',
            'status'        => 'pending',
        ]);
        DepotTransferItem::create([
            'depot_transfer_id' => $transfer->id,
            'product_id'        => $product->id,
            'quantity'          => 25,
        ]);

        $this->actingAs($this->user)
            ->post("/app/boutique/transfers/{$transfer->id}/approve?_tenant=test-advanced")
            ->assertRedirect();

        $transfer->refresh();
        $this->assertEquals('completed', $transfer->status);
        $this->assertEquals($this->user->id, $transfer->approved_by);
        $this->assertNotNull($transfer->approved_at);

        // Stock moved
        $this->assertEquals(75, StockItem::where('product_id', $product->id)->where('depot_id', $depotA->id)->first()->quantity);
        $dest = StockItem::where('product_id', $product->id)->where('depot_id', $depotB->id)->first();
        $this->assertNotNull($dest);
        $this->assertEquals(25, $dest->quantity);
    }

    #[Test]
    public function reject_transfer_cancels_without_stock_change(): void
    {
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $depotA = Depot::factory()->create(['company_id' => $this->company->id]);
        $depotB = Depot::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $depotA->id, 'quantity' => 80]);

        $transfer = DepotTransfer::create([
            'company_id'    => $this->company->id,
            'from_depot_id' => $depotA->id,
            'to_depot_id'   => $depotB->id,
            'user_id'       => $this->user->id,
            'reference'     => 'TRF-REJECT01',
            'status'        => 'pending',
        ]);
        DepotTransferItem::create([
            'depot_transfer_id' => $transfer->id,
            'product_id'        => $product->id,
            'quantity'          => 15,
        ]);

        $this->actingAs($this->user)
            ->post("/app/boutique/transfers/{$transfer->id}/reject?_tenant=test-advanced", [
                'rejection_reason' => 'Quantité insuffisante en stock',
            ])
            ->assertRedirect();

        $transfer->refresh();
        $this->assertEquals('cancelled', $transfer->status);
        $this->assertEquals('Quantité insuffisante en stock', $transfer->rejection_reason);

        // Stock unchanged
        $this->assertEquals(80, StockItem::where('product_id', $product->id)->where('depot_id', $depotA->id)->first()->quantity);
    }

    #[Test]
    public function cannot_approve_already_completed_transfer(): void
    {
        $module = \App\Models\Module::factory()->create(['code' => 'boutique', 'is_active' => true]);
        $this->company->modules()->attach($module->id);

        $depotA = Depot::factory()->create(['company_id' => $this->company->id]);
        $depotB = Depot::factory()->create(['company_id' => $this->company->id]);

        $transfer = DepotTransfer::create([
            'company_id'    => $this->company->id,
            'from_depot_id' => $depotA->id,
            'to_depot_id'   => $depotB->id,
            'user_id'       => $this->user->id,
            'reference'     => 'TRF-DONE',
            'status'        => 'completed',
        ]);

        $this->actingAs($this->user)
            ->post("/app/boutique/transfers/{$transfer->id}/approve?_tenant=test-advanced")
            ->assertSessionHasErrors('transfer');
    }
}
