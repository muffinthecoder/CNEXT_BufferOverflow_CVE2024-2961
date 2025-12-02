<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
ini_set('error_log', '/var/log/php-error.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = filter_var($_POST['file'] ?? '', FILTER_SANITIZE_URL);
    if ($file === '') {
        echo "File contents: ";
        exit;
    }

    if (!preg_match('/^(file|php):\/\//', $file)) {
        error_log("Blocked: Invalid file scheme used — $file");
        echo "Invalid file scheme";
        exit;
    }

    $forbidden = ['.env', '.passwd', 'config.php', '.htaccess', 'shadow', '/etc/passwd'];
    foreach ($forbidden as $bad) {
        if (stripos($file, $bad) !== false) {
            error_log("HONEYPOT TRIGGERED: Attempt to access sensitive file — $file");
            echo "Access denied";
            exit;
        }
    }

    $allowed_charsets = ['UTF-8', 'ISO-8859-1', 'ASCII', 'GB2312', 'BIG5'];
    $charset = $_POST['charset'] ?? 'UTF-8';
    if (!in_array($charset, $allowed_charsets)) {
        error_log("Blocked: Disallowed charset used — $charset");
        echo "Invalid charset";
        exit;
    }

    $data = @file_get_contents($file);
    if ($data === false) {
        error_log("Failed to read file: $file");
        echo "File contents: ";
    } else {
        $converted = @iconv("UTF-8", $charset, $data);
        echo "File contents: " . htmlspecialchars($converted, ENT_QUOTES, 'UTF-8');
    }
    exit;
}

echo "<!doctype html>\n<html>\n<head><meta charset='utf-8'><title>CVE 2024 2961 Project Testing</title></head>\n<body>\n";
echo "<h1 style='font-family: Arial, Helvetica, sans-serif; color:#222;'>CVE 2024 2961 Project Testing</h1>\n";
echo "<hr />\n";
echo "<p>Use POST with <code>file=file://&lt;path&gt;</code> and a safe charset to read files (lab only).</p>\n";
echo "<form method='post'>
        <input name='file' value='file:///etc/hosts' style='width:60%'>
        <input name='charset' value='UTF-8' style='width:20%'>
        <button type='submit'>Read</button>
      </form>\n";
echo "</body>\n</html>";
?>
