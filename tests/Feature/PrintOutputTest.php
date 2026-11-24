<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ReserveFundLog;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Semua cetakan & ekspor harus benar-benar menghasilkan dokumen (bukan hanya status 200). */
class PrintOutputTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Invoice $paid;
    private Invoice $unpaid;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-24 10:00:00'));
        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net & Co <Utama>');
        $this->owner->refresh();
        $this->actingAs($this->owner);

        $c = Customer::create(['name' => 'Budi & Sons <x>', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active',
            'address' => 'Jl. Mawar 5', 'phone' => '0812']);
        $this->unpaid = Invoice::create(['customer_id' => $c->id, 'invoice_number' => 'INV-202611-0001', 'period_date' => '2026-11-01',
            'amount' => 140000, 'status' => 'unpaid',
            'details' => ['lines' => [['label' => 'Paket', 'amount' => 150000], ['label' => 'Diskon', 'amount' => -10000]], 'prorata' => null]]);
        $this->paid = Invoice::create(['customer_id' => $c->id, 'invoice_number' => 'INV-202610-0001', 'period_date' => '2026-10-01',
            'amount' => 150000, 'status' => 'paid']);

        Transaction::create(['date' => '2026-10-05', 'type' => 'income', 'amount' => 150000, 'description' => 'Bayar Okt & lunas',
            'account_id' => ChartOfAccount::byCode(ChartOfAccount::PENDAPATAN_VOUCHER)->id,
            'source_account_id' => ChartOfAccount::byCode(ChartOfAccount::KAS)->id,
            'customer_id' => $c->id, 'invoice_id' => $this->paid->id, 'user_id' => $this->owner->id]);

        ReserveFundLog::create(['type' => 'in', 'amount' => 50000, 'description' => 'Cadangan & simpanan', 'transaction_date' => '2026-10-31']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_invoice_prints_a4_and_pos(): void
    {
        $this->get(route('invoices.print', $this->unpaid->id))->assertOk()
            ->assertSee('INV-202611-0001')->assertSee('Diskon')->assertSee('window.print()', false);
        $this->get(route('invoices.print', ['id' => $this->paid->id, 'format' => 'pos', 'paper' => '58']))->assertOk()
            ->assertSee('INV-202610-0001');
        $this->get(route('invoices.print', ['id' => $this->paid->id, 'format' => 'pos', 'paper' => '80']))->assertOk();
        $this->get(route('invoices.printList', ['status' => 'unpaid']))->assertOk()
            ->assertSee('INV-202611-0001')->assertDontSee('INV-202610-0001');
        $this->get(route('invoices.printList', ['format' => 'pos']))->assertOk()->assertSee('INV-202610-0001');
    }

    public function test_finance_prints(): void
    {
        $this->get(route('finance.receivables.print'))->assertOk()->assertSee('Budi & Sons <x>')->assertSee('Rp 140.000')->assertSee('window.print()', false);
        $this->get(route('finance.pl.index', ['month' => '2026-10']))->assertOk()->assertSee('window.print()', false);
    }

    public function test_pdf_exports_are_real_pdfs(): void
    {
        foreach ([route('reports.history.pdf'), route('reports.reserve.pdf')] as $url) {
            $res = $this->get($url)->assertOk();
            $this->assertStringStartsWith('%PDF', $res->getContent(), $url);
        }
    }

    public function test_excel_exports_are_real_xlsx(): void
    {
        foreach ([
            route('reports.export-excel', ['month' => 10, 'year' => 2026]),
            route('reports.history.excel'),
            route('customers.export'),
        ] as $url) {
            $res = $this->get($url)->assertOk();
            $file = $res->baseResponse->getFile()->getPathname();
            $this->assertStringStartsWith('PK', file_get_contents($file), $url); // xlsx = zip
        }
    }
}
