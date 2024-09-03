<?php
if (isset($_GET['data'])) {
    $data = base64_decode($_GET['data']);
    $params = json_decode($data, true);
    if ($params && isset($params['status']) && $params['status'] == 'COMPLETE') {
        $transaction_code = $params['transaction_code'];
        $status = $params['status'];
        $total_amount = $params['total_amount'];
        $transaction_uuid = $params['transaction_uuid'];
        $product_code = $params['product_code'];
        $signed_field_names = "transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names";
        $secret_key = '8gBm/:&EnhH.1/q'; // for testing only
        $data_to_sign = "transaction_code=$transaction_code,status=$status,total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code,signed_field_names=$signed_field_names";
        $signature = $params['signature'];
        $generatedSignature = base64_encode(hash_hmac('sha256', $data_to_sign, $secret_key, true));
        echo"<br>"; 
        echo $signature;
        echo"<br>";
        echo $generatedSignature;
        echo"<br>";echo"<br>";
        if (hash_equals($signature, $generatedSignature)) {
            $verifyUrl = "https://uat.esewa.com.np/api/epay/transaction/status/?product_code=$product_code&total_amount=$total_amount&transaction_uuid=$transaction_uuid";
            $verifyData = [
                'amt' => $total_amount,
                'rid' => $transaction_code,
                'pid' => $product_code,
                'scd' => 'EPAYTEST'
            ];
            $curl = curl_init($verifyUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($verifyData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            
            if ($curlError = curl_error($curl)) {
                echo "cURL error: " . htmlspecialchars($curlError);
            }
            curl_close($curl);
            
            if (strpos($response, "Success") !== false) {
                echo "Success";
            } else {
                echo "Payment verification failed. Please contact support.";
            }
        } else {
            echo "Signature verification failed.";
        }
    } else {
        echo "op, its invalid";
    }
} else {
    echo "No data received.";
}
?>