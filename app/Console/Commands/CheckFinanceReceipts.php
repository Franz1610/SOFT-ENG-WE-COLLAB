<?php

namespace App\Console\Commands;

use App\Models\FinanceEntry;
use Illuminate\Console\Command;

class CheckFinanceReceipts extends Command
{
    protected $signature = 'finance:spot-check {--status=Verified : FinanceEntry status to inspect} {--limit=15 : How many recent entries to display}';

    protected $description = 'Spot-check finance entries to ensure receipts have transaction dates and reference notes.';

    public function handle(): int
    {
        $status = $this->option('status') ?? 'Verified';
        $limit = (int) ($this->option('limit') ?? 15);

        $query = FinanceEntry::query()->with(['booking.user'])->orderByDesc('updated_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $entries = $query->limit(max(1, $limit))->get();
        if ($entries->isEmpty()) {
            $this->components->warn("No finance entries found for status '{$status}'.");
            return self::SUCCESS;
        }

        $missingDate = $entries->whereNull('transaction_date');
        $missingNotes = $entries->filter(fn (FinanceEntry $entry) => blank($entry->reference_notes));

        $this->components->info('Finance receipt spot-check summary');
        $this->line(sprintf('Total entries scanned: %d', $entries->count()));
        $this->line(sprintf('Missing transaction date: %d', $missingDate->count()));
        $this->line(sprintf('Missing reference notes: %d', $missingNotes->count()));

        $rows = $entries->map(function (FinanceEntry $entry) {
            return [
                'ID' => $entry->id,
                'Booking' => optional($entry->booking)->id,
                'Customer' => $entry->customer_name,
                'Status' => $entry->status,
                'Tx Date' => optional($entry->transaction_date)?->format('Y-m-d') ?? '—',
                'Notes?' => blank($entry->reference_notes) ? 'Missing' : 'OK',
            ];
        });

        $this->table(['ID', 'Booking', 'Customer', 'Status', 'Tx Date', 'Notes?'], $rows);

        if ($missingDate->isNotEmpty() || $missingNotes->isNotEmpty()) {
            $this->components->error('Some entries are missing required data. Please update them in the Finance module.');
            return self::FAILURE;
        }

        $this->components->info('All sampled entries contain the required receipt metadata.');
        return self::SUCCESS;
    }
}
