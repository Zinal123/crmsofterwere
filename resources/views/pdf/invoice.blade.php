@php
    $inv = $invoice[0];
    $cust = $customer[0];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice</title>
<style>
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #212529; }
    p { margin: 0 0 8px 0; }
    h5 { margin: 0 0 8px 0; font-size: 14px; }
    .logo { width: 260px; margin-bottom: 10px; }
    .divider { border: none; border-top: 1px solid green; margin: 10px 0; }
    .footer-text { text-align: center; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.bordered th, table.bordered td { border: 1px solid #999; padding: 6px 8px; }
    table.bordered thead th { background-color: #f1f1f1; text-align: left; }
    table.plain td { border: none; padding: 2px 4px; vertical-align: top; }
    table.plain td.label { width: 30%; font-weight: bold; }
    .text-right { text-align: right; }
</style>
</head>
<body>

<img class="logo" src="{{ public_path('build/images/header.png') }}" alt="Oracle Machine Tech">
<br>

<h5>Invoice {{ $inv->invoice_id }}</h5>
<hr class="divider">

<table class="plain">
    <tr><td class="label">Date</td><td>{{ \Illuminate\Support\Carbon::parse($inv->date)->format('d M Y') }}</td></tr>
    <tr><td class="label">Bill To</td><td>{{ strtoupper($cust->name ?? '-') }}</td></tr>
    <tr><td class="label">Address</td><td>{{ $cust->address ?? '-' }}</td></tr>
    <tr><td class="label">GSTIN</td><td>{{ $cust->billinggst ?? '-' }}</td></tr>
    <tr><td class="label">Place of Supply</td><td>{{ $inv->placesupply }}</td></tr>
</table>

<table class="bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>GST %</th>
            <th class="text-right">GST Amount</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoiceproduct as $item)
            <tr>
                <td>{{ $item->product ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ \App\Support\IndianNumber::format($item->rate) }}</td>
                <td>{{ $item->gst }}%</td>
                <td class="text-right">{{ \App\Support\IndianNumber::format($item->gstamount) }}</td>
                <td class="text-right">{{ \App\Support\IndianNumber::format($item->totalamount) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="plain">
    @if($state === \App\Support\IndianStates::HOME_STATE)
        <tr><td class="label">SGST</td><td class="text-right">{{ \App\Support\IndianNumber::format($sgstamount) }}</td></tr>
        <tr><td class="label">CGST</td><td class="text-right">{{ \App\Support\IndianNumber::format($cgstamount) }}</td></tr>
    @else
        <tr><td class="label">IGST</td><td class="text-right">{{ \App\Support\IndianNumber::format($igsamount) }}</td></tr>
    @endif
    <tr><td class="label">Grand Total</td><td class="text-right"><strong>{{ \App\Support\IndianNumber::format($totalamountwithtax) }}</strong></td></tr>
    <tr><td class="label">Paid</td><td class="text-right">{{ \App\Support\IndianNumber::format($inv->paidamount) }}</td></tr>
    <tr><td class="label">Remaining</td><td class="text-right">{{ \App\Support\IndianNumber::format($inv->remaining_amount) }}</td></tr>
</table>

<p class="footer-text">Generated {{ now()->format('d M Y, H:i') }} &middot; Oracle Machine Tech CRM</p>

</body>
</html>
