<?php
if (isset($_REQUEST['data'])) {
    echo "<pre>";
    echo "eSewa Raw Response (Base64):\n";
    print_r($_REQUEST['data']);
    echo "\n\nDecoded JSON:\n";
    print_r(json_decode(base64_decode($_REQUEST['data']), true));
    echo "</pre>";
} else {
    echo "❌ No response received from eSewa.";
}
?>