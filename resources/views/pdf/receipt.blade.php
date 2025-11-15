<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 24px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .title { font-size: 20px; font-weight: 700; letter-spacing: 0.05em; color: #2f4b2f; }
        .invoice-meta { text-align: right; font-size: 12px; }
        .section { margin-bottom: 18px; }
        .section h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 8px; }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table td { padding: 6px 8px; border: 1px solid #d1d5db; }
        .details-table tr:nth-child(even) { background: #f9fafb; }
        .amount-table { width: 100%; border-collapse: collapse; }
        .amount-table th, .amount-table td { padding: 8px; border: 1px solid #d1d5db; text-align: left; }
        .amount-table th { background: #e5e7eb; text-transform: uppercase; font-size: 11px; letter-spacing: 0.08em; }
        .amount-table td.amount { text-align: right; font-weight: 600; }
        .total-row td { font-size: 14px; font-weight: 700; }
        .muted { color: #6b7280; font-size: 11px; }
        .notes { border: 1px solid #d1d5db; padding: 12px; border-radius: 8px; background: #f9fafb; }
        .footer { margin-top: 40px; font-size: 11px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">WeCollab Invoice</div>
            <div class="muted">127 Timog Ave, QC · hello@wecollab.test</div>
        </div>
        <div class="invoice-meta">
            <div><strong>{{ $receipt['invoice_number'] ?? 'Invoice' }}</strong></div>
            <div>Booking {{ $receipt['booking_reference'] ?? ('#' . $booking->id) }}</div>
            <div>{{ $receipt['transaction_date'] ?? optional($finance->transaction_date)->format('F j, Y') }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Bill To</h2>
        <table class="details-table">
            <tr>
                <td width="35%">Customer</td>
                <td>{{ $receipt['customer_name'] ?? ($booking->first_name . ' ' . $booking->last_name) }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $booking->email }}</td>
            </tr>
            <tr>
                <td>Room Category</td>
                <td>{{ ucfirst($booking->category) }} · {{ $booking->room_name }}</td>
            </tr>
            <tr>
                <td>Booking Date</td>
                <td>{{ $booking->booking_date->format('F j, Y') }} · {{ $booking->formatted_time }}</td>
            </tr>
            <tr>
                <td>Payment Method</td>
                <td>{{ $receipt['payment_method'] ?? $finance->payment_method }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Charges</h2>
        <table class="amount-table">
            <tr>
                <th>Description</th>
                <th class="amount">Amount (PHP)</th>
            </tr>
            <tr>
                <td>Gross Total</td>
                <td class="amount">{{ number_format($receipt['gross_total'] ?? $finance->gross_total ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td class="amount">{{ number_format($receipt['amount_received'] ?? $finance->amount_received ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Gateway Fee</td>
                <td class="amount">{{ number_format($receipt['gateway_fee'] ?? $finance->gateway_fee ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Tax Collected</td>
                <td class="amount">{{ number_format($receipt['tax_collected'] ?? $finance->tax_collected ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Net Revenue</td>
                <td class="amount">{{ number_format($receipt['net_revenue'] ?? $finance->net_revenue ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    @if (!empty($receipt['reference_notes']))
        <div class="section">
            <h2>Reference Notes</h2>
            <div class="notes">
                {!! nl2br(e($receipt['reference_notes'])) !!}
            </div>
        </div>
    @endif

    <div class="section">
        <h2>Signatories</h2>
        <table class="details-table">
            <tr>
                <td width="35%">Prepared By</td>
                <td>{{ $receipt['prepared_by'] ?? 'Finance Team' }}</td>
            </tr>
            <tr>
                <td>Verified By</td>
                <td>{{ $receipt['approved_by'] ?? 'Finance Reviewer' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ $receipt['status'] ?? $finance->status }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This document serves as an official receipt for the transaction above. Please keep a copy for your records.
    </div>
</body>
</html>
