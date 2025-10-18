<?php
// debug_products.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load DB connection - adjust path if needed
include __DIR__ . '/config.php'; // should set $conn = new mysqli(...)

// Helper
function out($title, $val='') {
    echo "<h3 style='margin-bottom:4px'>$title</h3>";
    echo "<pre style='background:#f4f4f4;padding:10px;border-radius:6px;'>";
    if (is_array($val) || is_object($val)) print_r($val); else var_dump($val);
    echo "</pre>";
}

// 1) show basic connection info
out("mysqli connection object", isset($conn) ? get_class($conn) : 'no $conn');
if (isset($conn)) {
    out("connect_errno / connect_error", [$conn->connect_errno, $conn->connect_error]);
    out("host_info", $conn->host_info ?? null);
}

// 2) show current database name
$res = $conn->query("SELECT DATABASE() as dbname");
$dbrow = $res ? $res->fetch_assoc() : null;
out("Current database (SELECT DATABASE())", $dbrow);

// 3) show tables matching 'products'
$res = $conn->query("SHOW TABLES LIKE 'products'");
$hasProducts = ($res && $res->num_rows > 0);
out("Does table `products` exist?", $hasProducts ? "YES" : "NO");
if ($hasProducts) {
    // 4) show columns
    $cols = $conn->query("DESCRIBE products")->fetch_all(MYSQLI_ASSOC);
    out("products table columns", $cols);

    // 5) count rows and show first 20 rows
    $countR = $conn->query("SELECT COUNT(*) AS cnt FROM products");
    $cnt = $countR ? $countR->fetch_assoc()['cnt'] : 'query failed';
    out("products row count", $cnt);

    $rows = $conn->query("SELECT id, name, category, price, image FROM products LIMIT 20");
    $rowsA = $rows ? $rows->fetch_all(MYSQLI_ASSOC) : null;
    out("first up to 20 rows from products", $rowsA);
} else {
    out("Suggestion", "If NO, run the SQL to create the table (I can paste the SQL if you need).");
}

// 6) Show filesystem check for uploads folder and example image existence
$uploadsDir = __DIR__ . '/uploads';
out("Project path (for reference)", __DIR__);
out("uploads folder exists?", is_dir($uploadsDir));
if (is_dir($uploadsDir)) {
    // list subfolders and sample files
    $folders = array_filter(glob($uploadsDir . '/*'), 'is_dir');
    $foldersClean = array_map(function($p){ return str_replace('\\','/',$p); }, $folders);
    out("uploads subfolders (found)", $foldersClean);

    // Show sample files per folder (first 10)
    foreach ($folders as $f) {
        $files = glob($f . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $sample = array_slice($files, 0, 10);
        $sampleRel = array_map(function($p){ return str_replace('\\','/',$p); }, $sample);
        out("Files in folder: " . basename($f), $sampleRel);
        // For each sample show web-path exists?
        foreach ($sample as $file) {
            $rel = str_replace('\\','/',$file);
            // build web path relative to project root
            $webPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($file));
            if ($webPath[0] !== '/') $webPath = '/' . $webPath;
            out("file existence and web path for: " . basename($file), [
                'realpath' => realpath($file),
                'filesize' => filesize($file),
                'web_path' => $webPath,
                'file_exists' => file_exists($file),
            ]);
        }
    }
} else {
    out("uploads folder missing", "Create folder: " . $uploadsDir . " and add images inside subfolders such as uploads/fruits_vegetables/");
}

// 7) Quick check: do any rows have image path that exists?
if ($hasProducts && !empty($rowsA)) {
    $check = [];
    foreach ($rowsA as $r) {
        $imgPath = __DIR__ . '/' . ltrim($r['image'], '/');
        $check[] = [
            'id'=>$r['id'],
            'image_field' => $r['image'],
            'file_exists' => file_exists($imgPath),
            'realpath' => file_exists($imgPath) ? realpath($imgPath) : null,
        ];
    }
    out("Do image paths from DB actually exist on disk? (first rows checked)", $check);
}

echo "<p style='color:green;font-weight:bold;'>After you open this page, copy the whole output and paste it here so I can read it and point exact fixes.</p>";
