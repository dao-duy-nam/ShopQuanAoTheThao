<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Xác minh OTP</title>
  <style>
    body {
      background-color: #f5f7fa;
      font-family: Arial, sans-serif;
      color: #333;
      padding: 20px;
    }
    .container {
      max-width: 480px;
      background-color: #ffffff;
      margin: auto;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
      color: #2c3e50;
      font-weight: 700;
      font-size: 24px;
      margin-bottom: 20px;
    }
    p {
      font-size: 16px;
      line-height: 1.5;
      margin-bottom: 20px;
    }
    .otp-code {
      display: inline-block;
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 6px;
      background-color: #2c3e50;
      color: #ffffff;
      padding: 12px 24px;
      border-radius: 6px;
      margin-bottom: 25px;
      user-select: all;
    }
    .footer {
      font-size: 14px;
      color: #888;
      text-align: center;
      margin-top: 30px;
    }
    .button {
      background-color: #2980b9;
      color: #fff;
      text-decoration: none;
      padding: 12px 24px;
      border-radius: 6px;
      font-weight: 600;
      display: inline-block;
      margin-top: 10px;
    }
    .button:hover {
      background-color: #3498db;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Xin chào {{ $name }},</h1>
    <p>Bạn vừa yêu cầu xác minh tài khoản của mình.</p>
    <p>Mã OTP để xác minh tài khoản của bạn là:</p>
    <div class="otp-code">{{ $otp }}</div>
    <p>Mã OTP có hiệu lực trong 2 phút. Vui lòng không chia sẻ mã này với bất kỳ ai để bảo mật tài khoản.</p>
    
    <div class="footer">
      Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.
    </div>
  </div>
</body>
</html>
