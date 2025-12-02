cat > ~/cve2961_lab/index.php <<'PHP'
<?php
// CVE lab page — plain-text output for POSTs, HTML for GETs
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
ini_set('error_log', '/var/log/php-error.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Plain-text response expected by exploit scripts
    if (!isset($_POST['file']) || $_POST['file'] === '') {
        echo "File contents: ";
        exit;
    }
    $file = $_POST['file'];
    if (strpos($file, 'file://') !== 0 && strpos($file, 'php://') !== 0) {
        echo "Invalid file scheme";
        exit;
    }
    $data = @file_get_contents($file);
    if ($data === false) {
        error_log("Failed to read file: $file");
        echo "File contents: ";
    } else {
        // Do not HTML-escape here — exploit expects raw base64/plain output
        echo "File contents: " . $data;
    }
    exit;

    $data = @file_get_contents($file);
    if ($data === false) {
        error_log("Failed to read file: $file");
        echo "File contents: ";
    } else {
        // Vulnerable conversion step
        $converted = iconv("UTF-8", "ISO-2022-CN-EXT", $data);
        echo "File contents: " . $converted;
    }
    exit;

}
// The HTML page contents
echo "<!doctype html>\n<html>\n<head><meta charset='utf-8'><title>CVE 2024 2961 Project Testing</title></head>\n<body>\n";
echo "<h1 style='font-family: Arial, Helvetica, sans-serif; color:#222;'>CVE 2024 2961 Project Testing</h1>\n";
echo "<hr />\n";
echo "<p>Use POST with <code>file=file://&lt;path&gt;</code> to read files (lab only).</p>\n";
echo "<form method='post'><input name='file' value='file:///etc/hosts' style='width:80%'><button type='submit'>Read</button></form>\n";
echo "</body>\n</html>";
?>
