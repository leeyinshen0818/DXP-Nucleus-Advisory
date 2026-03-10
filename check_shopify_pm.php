<?php
$content = file_get_contents('d:/Users/Nitro/Documents/GitHub/DXP-Nucleus-Advisory/includes/product-manager.php');
if (strpos($content, '_nucleus_product_shopify_button') !== false) {
    echo "Found _nucleus_product_shopify_button\n";
} else {
    echo "Did NOT find _nucleus_product_shopify_button\n";
}

$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'shopify_button') !== false) {
        echo ($i+1) . ": " . $line . "\n";
    }
}
