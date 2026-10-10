
<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$callback = json_decode($raw, true);

try {
    $stk = $callback['Body']['stkCallback'] ?? null;

    if (!$stk || empty($stk['CheckoutRequestID'])) {
        error_log('M-Pesa callback: Invalid payload');
        http_response_code(400);
        echo json_encode(['Result' => 'Invalid callback']);
        exit;
    }

    $checkoutId = $stk['CheckoutRequestID'];
    $resultCode = (int)($stk['ResultCode'] ?? -1);
    $resultDesc = $stk['ResultDesc'] ?? 'Unknown result';

    $metadata = [];

    foreach (($stk['CallbackMetadata']['Item'] ?? []) as $item) {
        if (isset($item['Name'])) {
            $metadata[$item['Name']] = $item['Value'] ?? null;
        }
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT payment_id, reservation_id, amount, status
         FROM payment
         WHERE payment_ref = ?
         LIMIT 1
         FOR UPDATE"
    );
    $stmt->execute([$checkoutId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $pdo->rollBack();
        error_log("M-Pesa callback: Unknown checkout ID");
        http_response_code(200);
        echo json_encode(['Result' => 'Payment not found']);
        exit;
    }

    // Never downgrade a previously completed payment.
    if ($payment['status'] === 'paid') {
        $pdo->commit();
        echo json_encode(['Result' => 'Already processed']);
        exit;
    }

    if ($resultCode === 0) {
        $receivedAmount = (float)($metadata['Amount'] ?? 0);
        $receipt = $metadata['MpesaReceiptNumber'] ?? '';

        // Verify amount and receipt before confirming payment.
        if (
            $receivedAmount != (float)$payment['amount'] ||
            $receipt === ''
        ) {
            $pdo->rollBack();
            error_log("M-Pesa callback: Amount/receipt validation failed");
            http_response_code(200);
            echo json_encode(['Result' => 'Payment requires verification']);
            exit;
        }

        $meta = json_encode([
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'receipt' => $receipt,
            'amount' => $receivedAmount,
            'phone' => $metadata['PhoneNumber'] ?? null,
            'transaction_date' => $metadata['TransactionDate'] ?? null
        ]);

        $stmt = $pdo->prepare(
            "UPDATE payment
             SET status = 'paid', payment_meta = ?
             WHERE payment_id = ?"
        );
        $stmt->execute([$meta, $payment['payment_id']]);

        $stmt = $pdo->prepare(
            "UPDATE reservations
             SET status = 'confirmed'
             WHERE reservation_id = ?"
        );
        $stmt->execute([$payment['reservation_id']]);

    } else {
        $meta = json_encode([
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);

        $stmt = $pdo->prepare(
            "UPDATE payment
             SET status = 'failed', payment_meta = ?
             WHERE payment_id = ?"
        );
        $stmt->execute([$meta, $payment['payment_id']]);
    }

    $pdo->commit();

    echo json_encode(['Result' => 'Callback processed']);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('M-Pesa callback error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['Result' => 'Processing error']);
}