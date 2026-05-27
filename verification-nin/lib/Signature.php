<?php

namespace SmileIdentity;

class Signature
{
    private $api_key;
    private $partner_id;

    /**
     * Signature constructor.
     * @param string $partner_id
     * @param string $api_key
     */
    public function __construct(string $partner_id, string $api_key)
    {
        $this->api_key = $api_key;
        $this->partner_id = $partner_id;
    }

    /**
     * Generates a signature with a proper UTC timestamp.
     *
     * @return array
     */
    public function generate_signature(): array
    {
        $timestamp = gmdate("Y-m-d\TH:i:s\Z"); // SmileID expects UTC ISO 8601
        $message = $timestamp . $this->partner_id . "sid_request";
        $signature = base64_encode(hash_hmac('sha256', $message, $this->api_key, true));

        return [
            "signature" => $signature,
            "timestamp" => $timestamp
        ];
    }

    /**
     * Verifies a signature by regenerating it from a given timestamp.
     *
     * @param string $timestamp
     * @param string $signature
     * @return bool
     */
    public function confirm_signature(string $timestamp, string $signature): bool
    {
        $message = $timestamp . $this->partner_id . "sid_request";
        $expected_signature = base64_encode(hash_hmac('sha256', $message, $this->api_key, true));
        return hash_equals($expected_signature, $signature);
    }
}
