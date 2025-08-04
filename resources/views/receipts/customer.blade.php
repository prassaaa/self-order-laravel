<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 10px;
            margin-bottom: 2px;
        }
        .order-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .order-info div {
            margin-bottom: 2px;
        }
        .items {
            margin-bottom: 10px;
        }
        .item {
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            margin-left: 10px;
            font-size: 11px;
        }
        .item-notes {
            margin-left: 10px;
            font-style: italic;
            font-size: 10px;
        }
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
            text-align: right;
        }
        .total {
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }
        .qr-code {
            text-align: center;
            margin: 10px 0;
        }
        .dotted-line {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-info">{{ $company['address'] }}</div>
            <div class="company-info">{{ $company['phone'] }}</div>
            <div class="company-info">{{ $company['email'] }}</div>
        </div>

        <!-- Order Information -->
        <div class="order-info">
            <div><strong>Order Number:</strong> {{ $order->order_number }}</div>
            <div><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}</div>
            @if($order->table_number)
                <div><strong>Table:</strong> {{ $order->table_number }}</div>
            @endif
            @if($order->customer_name)
                <div><strong>Customer:</strong> {{ $order->customer_name }}</div>
            @endif
            @if($order->customer_phone)
                <div><strong>Phone:</strong> {{ $order->customer_phone }}</div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="items">
            @foreach($order->orderItems as $item)
                <div class="item">
                    <div class="item-name">{{ $item->menu->name }}</div>
                    <div class="item-details">
                        {{ $item->quantity }}x Rp {{ number_format($item->price, 0, ',', '.') }} = 
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </div>
                    @if($item->notes)
                        <div class="item-notes">Note: {{ $item->notes }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Total -->
        <div class="total-section">
            <div class="total">
                TOTAL: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
        </div>

        <!-- Order Notes -->
        @if($order->notes)
            <div class="dotted-line"></div>
            <div style="margin: 10px 0;">
                <strong>Special Instructions:</strong><br>
                {{ $order->notes }}
            </div>
        @endif

        <!-- QR Code Section -->
        <div class="qr-code">
            <div style="border: 1px solid #000; padding: 20px; margin: 10px 0;">
                QR Code<br>
                <small>Order: {{ $order->order_number }}</small>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your order!</div>
            <div>Please keep this receipt</div>
            <div style="margin-top: 10px;">
                <small>Printed on: {{ now()->format('d/m/Y H:i:s') }}</small>
            </div>
        </div>
    </div>
</body>
</html>
