<?php
require_once __DIR__ . '/../config.php';

$amount = isset($_POST["amount"]) ? $_POST["amount"] : 0;    
$mchId = isset($_POST["mchId"]) ? $_POST["mchId"] : '';
$mchOrderNo = isset($_POST["mchOrderNo"]) ? $_POST["mchOrderNo"] : '';
$merRetMsg = isset($_POST["merRetMsg"]) ? $_POST["merRetMsg"] : '';
$orderDate = isset($_POST["orderDate"]) ? $_POST["orderDate"] : '';
$orderNo = isset($_POST["orderNo"]) ? $_POST["orderNo"] : '';    
$oriAmount = isset($_POST["oriAmount"]) ? $_POST["oriAmount"] : 0;
$tradeResult = isset($_POST["tradeResult"]) ? $_POST["tradeResult"] : '';
$signType = isset($_POST["signType"]) ? $_POST["signType"] : '';
$sign = isset($_POST["sign"]) ? $_POST["sign"] : '';

$am = floatval($oriAmount);
$amount = $am;
$orderid = $mchOrderNo;
$order = explode('xx', $mchOrderNo);
$user = isset($order[1]) ? $order[1] : '';
$username = $user;

if ($username) {
    // Fetch user data from Firebase
    $user_data = firebase_request("users/$username");

    if ($user_data) {
        // Save recharge transaction record in Firebase
        $recharge_record = [
            'username' => $username,
            'amount' => $amount,
            'status' => 'Success',
            'utr' => $orderid,
            'time' => date('Y-m-d H:i:s')
        ];
        firebase_request("recharges/$orderid", "PUT", $recharge_record);
        
        // Add recharge amount to user balance
        $current_balance = isset($user_data['balance']) ? floatval($user_data['balance']) : 0;
        $new_balance = $current_balance + $amount;
        firebase_request("users/$username/balance", "PUT", $new_balance);
        
        // Referral bonus check
        $refcode = isset($user_data['refcode']) ? $user_data['refcode'] : '';
        if ($refcode) {
            $bonus = 100; // Flat referral bonus
            
            // Log bonus history
            firebase_request("bonuses/" . time(), "PUT", [
                'giver' => $username,
                'receiver_code' => $refcode,
                'amount' => $bonus,
                'time' => date('Y-m-d H:i:s')
            ]);
            
            // Award bonus to referrer
            $all_users = firebase_request("users");
            if ($all_users) {
                foreach ($all_users as $uname => $udata) {
                    if (isset($udata['usercode']) && $udata['usercode'] == $refcode) {
                        $ref_balance = isset($udata['balance']) ? floatval($udata['balance']) : 0;
                        $ref_bonus = isset($udata['bonus']) ? floatval($udata['bonus']) : 0;
                        firebase_request("users/$uname/bonus", "PUT", $ref_bonus + $bonus);
                        firebase_request("users/$uname/balance", "PUT", $ref_balance + $bonus);
                        break;
                    }
                }
            }
        }
    }
}

echo 'success';
?>