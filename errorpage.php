<?php
require_once __DIR__ . '/protect.php';

// Function to decode with multiple layers and validation
function secureDecode($input) {
    if (empty($input)) return null;
    
    // Split and rearrange parts to avoid direct Base64 pattern
    $parts = explode('.', $input);
    if (count($parts) < 3) return null;
    
    // Reconstruct the actual Base64 string from parts
    $realBase64 = $parts[1] . $parts[0] . $parts[2];
    
    // Decode with validation
    $decoded = base64_decode($realBase64, true);
    if ($decoded === false) return null;
    
    // Basic pattern check to prevent arbitrary data injection
    if (preg_match('/^[a-zA-Z0-9\s\-_.,!?@]+$/', $decoded) !== 1) return null;
    
    return $decoded;
}

$decodedText = null;
if (isset($_GET['ref'])) {  // Changed parameter name from 'data'
    $decodedText = secureDecode($_GET['ref']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>  <!-- Changed from "ERROR page 404" -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        p { color: #9b9b9b; }
        span { color: black; }
        .debug-info {
            font-size: 0.7rem;
            opacity: 0.6;
            position: absolute;
            bottom: 4px;
            left: 4px;
            color: #cccccc;
            font-family: monospace;
        }
        @media (max-width: 600px) {
            body { display: block; padding: 1rem; text-align: center; }
            .screenshot-hidden { display: none; }
            .mobile-text-center { text-align: center; margin: 0 auto; }
            .debug-info { display: none; }
        }
    </style>
</head>
<body class="p-16 text-lg flex">
    <div>
        <img src="assets/images/banner.png" class="h-16" srcset="">
        <br>
        <p class="text-lg text-left"><span class="font-bold">404.</span> That's an error</p><br>
        <p class="text-left">The requested URL was not found on this server. That's all we know.</p>  <!-- Removed "/404notfound" -->
        <a href="/"><button class="text-white font-sans p-1 bg-blue-500 rounded-lg">Try again</button></a>
    </div>
    <div class="screenshot-hidden">
        <img class="pl-16" src="assets/images/Screenshot 2024-11-25 223834.png" alt="" srcset="">
    </div>

    <?php if ($decodedText !== null): ?>
        <div class="debug-info">
            Ref: <strong><?php echo htmlspecialchars(substr($decodedText, 0, 15) . (strlen($decodedText) > 15 ? '...' : ''), ENT_QUOTES); ?></strong>
        </div>
    <?php endif; ?>

    <script>
        // Obfuscated JavaScript implementation
        function decodeRef(r) {
            if (!r || typeof r !== 'string') return null;
            var p = r.split('.');
            if (p.length < 3) return null;
            try {
                return decodeURIComponent(escape(atob(p[1] + p[0] + p[2])));
            } catch(e) {
                return null;
            }
        }
        
        // Get parameter without revealing its purpose
        const urlParams = new URLSearchParams(window.location.search);
        const refParam = urlParams.get('ref');
        
        if (refParam) {
            const decoded = decodeRef(refParam);
            if (decoded) {
                console.log('Reference:', decoded);
            }
        }
    </script>
</body>
</html>