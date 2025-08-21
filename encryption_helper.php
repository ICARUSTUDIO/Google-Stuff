<?php
class EncryptionHelper {
    private $key;
    private $cipher = 'aes-256-cbc';
    
    public function __construct($key) {
        $this->key = hash('sha256', $key, true);
    }
    
    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public function decrypt($data) {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, $this->cipher, $this->key, 0, $iv);
    }
}

// Initialize with a secure key (store this securely, not in the web root)
$encryption_key = "X7#gP9@qR$2sT%5vU&8wY*zA1bC3dE4fG6hJ8kL0mN2pQ4rS6tV8xZ0aB2cD4eF6gH8jK0";
$encryptor = new EncryptionHelper($encryption_key);
?>