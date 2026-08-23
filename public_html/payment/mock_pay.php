<?php
session_start();

$user = $_GET['user'] ?? '';
$amount = $_GET['am'] ?? '0';
$order = $_GET['order'] ?? ('MOCKxx' . $user . 'xx' . time());

$merchant_key = $_ENV['PAYMENT_API_KEY'] ?? "5d6af34cd11f453aa837766355d07b25";

// Build the confirmation post fields to match the real gateway response
$params = [
    'amount' => $amount,
    'mchId' => $_ENV['PAYMENT_MERCHANT_ID'] ?? '888100868',
    'mchOrderNo' => $order,
    'merRetMsg' => $user,
    'orderDate' => date('Y-m-d H:i:s'),
    'orderNo' => 'MOCKGATEWAY' . time(),
    'oriAmount' => $amount,
    'tradeResult' => '1', // 1 means Success
    'signType' => 'MD5'
];

// Generate signature using MD5 (same logic as verify/confirm)
ksort($params);
$signSource = "";
foreach ($params as $key => $value) {
    if (!empty($value) && $key !== 'sign' && $key !== 'sign_type') {
        $signSource .= "$key=$value&";
    }
}
$signSource .= "key=" . $merchant_key;
$signature = md5($signSource);
$params['sign'] = $signature;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sandbox Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; }
        .brand-header { background: linear-gradient(135deg, #6610f2, #6f42c1); color: white; border-top-left-radius: 15px; border-top-right-radius: 15px; padding: 20px; text-align: center; }
        .qr-placeholder { background-color: #e9ecef; border: 2px dashed #ced4da; border-radius: 10px; height: 200px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #6c757d; margin: 20px 0; }
        .btn-success { background-color: #28a745; border: none; padding: 12px; font-weight: bold; border-radius: 8px; width: 100%; }
        .btn-success:hover { background-color: #218838; }
        .btn-danger { background-color: #dc3545; border: none; padding: 12px; font-weight: bold; border-radius: 8px; width: 100%; }
        .btn-danger:hover { background-color: #c82333; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="brand-header">
                    <h4>LO's Sandbox UPI Gateway</h4>
                    <p class="mb-0 text-white-50">Safe testing environment • No real money used</p>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <span class="text-muted">User ID / Username:</span>
                        <h5 class="text-dark font-monospace"><?php echo htmlspecialchars($user); ?></h5>
                    </div>
                    
                    <div class="text-center mb-4">
                        <span class="text-muted">Amount to Pay:</span>
                        <h2 class="text-success font-monospace">₹<?php echo htmlspecialchars($amount); ?></h2>
                    </div>

                    <div class="qr-placeholder flex-column">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=mock@upi&pn=Sandbox&am=<?php echo urlencode($amount); ?>" alt="Scan to Pay QR" style="width: 150px; height: 150px; margin-bottom: 8px;">
                        <small class="text-muted">Scan QR to simulate or click button below</small>
                    </div>

                    <div class="alert alert-info text-center py-2" role="alert">
                        <strong>Order Reference:</strong> <?php echo htmlspecialchars($order); ?>
                    </div>

                    <form action="confirm.php" method="POST" id="paymentForm" class="mt-3">
                        <?php foreach($params as $k => $v): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-success mb-2">Simulate Successful Payment</button>
                    </form>
                    
                    <button class="btn btn-danger" onclick="window.close();">Cancel & Go Back</button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
