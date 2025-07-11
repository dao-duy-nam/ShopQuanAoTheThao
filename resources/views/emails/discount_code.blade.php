<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mã Giảm Giá Đặc Biệt</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            margin: 0;
            padding: 50px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .container {
            background: #ffffff;
            max-width: 650px;
            margin: auto;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
        }
        .container:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #1a2a44;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .discount-code {
            font-size: 30px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(45deg, #ff4d4d, #ff1a1a);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
            letter-spacing: 3px;
            text-transform: uppercase;
            box-shadow: 0 6px 15px rgba(255, 77, 77, 0.5);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .discount-code::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        .discount-code:hover::before {
            left: 100%;
        }
        .discount-code:hover {
            transform: scale(1.03);
        }
        .info {
            font-size: 17px;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.7;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .highlight {
            color: #ff4d4d;
            font-weight: 600;
        }
        .note {
            margin: 30px 0;
            font-size: 15px;
            color: #5c6b80;
            line-height: 1.8;
            text-align: center;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .cta-button {
            display: block;
            margin: 25px auto;
            padding: 14px 40px;
            background: linear-gradient(45deg, #ff4d4d, #ff1a1a);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 77, 77, 0.4);
            max-width: 200px;
        }
        .cta-button:hover {
            background: linear-gradient(45deg, #e60000, #cc0000);
            transform: translateY(-4px);
            box-shadow: 0 6px 18px rgba(255, 77, 77, 0.6);
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
            font-size: 15px;
            color: #718096;
            text-align: center;
        }
        .footer a {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: #fff;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(46, 204, 113, 0.4);
        }
        @media (max-width: 600px) {
            .container {
                padding: 25px;
                margin: 0 10px;
            }
            h2 {
                font-size: 26px;
            }
            .discount-code {
                font-size: 24px;
                padding: 15px;
            }
            .cta-button {
                padding: 12px 30px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge">Ưu Đãi Độc Quyền</div>
        <h2>Xin chào {{ $user->name }}!</h2>

        <p class="info">Chúng tôi rất vui mừng mang đến bạn <span class="highlight">mã giảm giá đặc biệt</span> như lời tri ân vì sự đồng hành tuyệt vời của bạn!</p>

        <div class="discount-code">Mã: {{ $code->ma }}</div>

        <p class="info"><strong>Tên mã:</strong> {{ $code->ten ?? 'Không có tên' }}</p>

        <p class="info"><strong>Giá trị:</strong> 
            @if($code->loai === 'phan_tram')
                {{ $code->gia_tri }}% <span class="highlight">giảm giá</span>
            @else
                {{ number_format($code->gia_tri, 0, ',', '.') }}đ <span class="highlight">tiết kiệm</span>
            @endif
        </p>

        @if($code->ngay_ket_thuc)
            <p class="info"><strong>Hạn sử dụng:</strong> đến {{ \Carbon\Carbon::parse($code->ngay_ket_thuc)->format('d/m/Y H:i') }}</p>
        @endif

        <p class="note">⏰ Cơ hội có một không hai! Sử dụng mã giảm giá ngay để tận hưởng những ưu đãi đỉnh cao. <br> Mua sắm thỏa thích, tiết kiệm cực chất!</p>

        <a href="https://yourwebsite.com" class="cta-button">Mua Sắm Ngay</a>

        <div class="footer">
            Trân trọng,<br>
            Đội ngũ <a href="https://yourwebsite.com">Chăm sóc Khách hàng</a>
        </div>
    </div>
</body>
</html>