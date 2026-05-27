<?php
function generateSmileSignature($timestamp, $partner_id, $api_key) {
    $string_to_sign = $timestamp . $partner_id . 'sid_request'; // assuming 'sid_request' based on your signature rule
    return hash_hmac('sha256', $string_to_sign, $api_key);
}