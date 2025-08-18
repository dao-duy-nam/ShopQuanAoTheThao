<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>{{ $subjectText }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
            padding: 16px;
        }

        .content {
            padding: 20px;
        }

        .transaction-info {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #777;
            padding: 12px;
            background: #f1f1f1;
        }

        .success {
            color: #4CAF50;
            font-weight: bold;
        }

        .rejected {
            color: #E53935;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h2>{{ $subjectText }}</h2>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $transaction->user->name }}</strong>,</p>

            @if ($transaction->status === 'success')
                <p class="success"> Bạn đã nạp thành công <strong>{{ number_format($transaction->amount) }} VND</strong>
                    vào ví của mình.</p>
            @elseif($transaction->status === 'rejected')
                <p class="rejected"> Giao dịch nạp tiền <strong>{{ $transaction->transaction_code }}</strong> đã bị từ
                    chối hoặc hết hạn.</p>
            @endif

            <div class="transaction-info">
                <p><strong>Mã giao dịch:</strong> {{ $transaction->transaction_code }}</p>
                <p><strong>Số tiền nạp:</strong> {{ number_format($transaction->amount) }} VND</p>
                <p><strong>Mô tả:</strong> {{ $transaction->description }}</p>
            </div>

            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
            <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
        </div>

    </div>
</body>

</html>
