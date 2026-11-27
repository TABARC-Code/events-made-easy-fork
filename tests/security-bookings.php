<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('ABSPATH', __DIR__);
function add_action(...$args) {}
require __DIR__ . '/../includes/eme-rsvp.php';
function check($condition, $name) {
    if (!$condition) { fwrite(STDERR, "FAIL: $name\n"); exit(1); }
}
check(eme_booking_quantity_valid('0'), 'Zero attendance');
check(eme_booking_quantity_valid('2'), 'Positive quantity');
check(!eme_booking_quantity_valid('-1'), 'Negative quantity');
check(!eme_booking_quantity_valid('1.5'), 'Fractional quantity');
check(!eme_booking_quantity_valid(['2']), 'Array quantity');
check(!eme_booking_quantity_valid('8388608'), 'Database overflow');
check(eme_booking_post_quantities_valid(['bookedSeats1'=>'2','bookedSeats2'=>'0'],2), 'Valid categories');
check(!eme_booking_post_quantities_valid(['bookedSeats3'=>'2'],2), 'Unknown category');
check(!eme_booking_post_quantities_valid(['bookedSeats01'=>'2'],2), 'Category alias');
check(!eme_booking_post_quantities_valid(['prefixbookedSeats1'=>'2'],2), 'Malformed category name');
check(!eme_booking_post_quantities_valid(['bookedSeats1'=>'2','bookedSeats2'=>'-1'],2), 'Negative category');
check(!eme_booking_post_quantities_valid(['bookedSeats1'=>'8388607','bookedSeats2'=>'1'],2), 'Combined quantity overflow');
check(!eme_booking_post_quantities_valid('invalid',0), 'Malformed booking object');
check(eme_booking_categories_within_limit(['1','2'],['2','2']), 'Valid maximum');
check(!eme_booking_categories_within_limit(['1','3'],['2','2']), 'Second category exceeds maximum');
check(!eme_booking_categories_within_limit(['3','0'],['2','2']), 'First category exceeds maximum');
check(!eme_booking_categories_within_limit(['3','0'],['2','1'],true), 'Second category below minimum');
check(!eme_booking_categories_within_limit(['1','2','1'],['2','2']), 'Extra category');
echo "Booking regression checks passed.\n";
