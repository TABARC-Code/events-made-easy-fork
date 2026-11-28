<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('ABSPATH', __DIR__);
define('EME_DB_PREFIX', 'test_');
define('EME_PAYMENTS_TBNAME', 'payments');
function add_action(...$args) {}
function wp_cache_delete(...$args) {}
require __DIR__ . '/../includes/eme-payments.php';
function check($condition, $description) {
    if (!$condition) { fwrite(STDERR, "FAIL: $description\n"); exit(1); }
}
check(eme_payment_amount_is_sufficient('10.00', 10), 'Exact amount');
check(eme_payment_amount_is_sufficient(11, 10), 'Overpayment');
check(!eme_payment_amount_is_sufficient(9.99, 10), 'One penny short');
check(!eme_payment_amount_is_sufficient(999, 1000), 'One minor unit short');
check(!eme_payment_amount_is_sufficient('invalid', 0), 'Malformed amount');
check(!eme_payment_amount_is_sufficient(INF, 10), 'Infinite amount');
check(!eme_payment_amount_is_sufficient(NAN, 10), 'NaN amount');
check(!eme_payment_amount_is_sufficient(-1, 0), 'Negative amount');
check(eme_payment_amount_is_sufficient(0.3, 0.1 + 0.2), 'Floating point rounding');
check(eme_payment_currency_matches('gbp', 'GBP'), 'Currency case');
check(!eme_payment_currency_matches('USD', 'GBP'), 'Wrong currency');
check(!eme_payment_currency_matches(null, 'GBP'), 'Missing currency');
class PaymentStore {
    public $row = ['id' => 1, 'pg_pid' => 'checkout', 'pg_handled' => 0];
    public function update($table, $data, $where, $formats, $where_formats) {
        foreach ($where as $key => $value) {
            if ($this->row[$key] !== $value) { return 0; }
        }
        $this->row = array_merge($this->row, $data);
        return 1;
    }
}
$wpdb = new PaymentStore();
$snapshot = $wpdb->row;
check(eme_claim_payment_notification($snapshot, 'transaction'), 'First notification');
check(!eme_claim_payment_notification($snapshot, 'transaction'), 'Concurrent stale notification');
check(!eme_claim_payment_notification($wpdb->row, 'transaction'), 'Repeated notification');
check(!eme_claim_payment_notification($wpdb->row, ''), 'Missing transaction identifier');
echo "Payment regression checks passed.\n";

$wpdb->row = ['id' => 2, 'pg_pid' => null, 'pg_handled' => 0];
check(eme_claim_payment_notification($wpdb->row, 'first-transaction'), 'New payment with nullable gateway ID');
