<?php
/**
 * MediQuick Pharmacy - Shop Handler
 * Location: public/handlers/shop-handler.php
 */

// Database Check
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available.');
}

// Helper Functions
function shopEscape(string $value): string
{
    global $conn;
    return $conn->real_escape_string($value);
}

function shopLikeEscape(string $value): string
{
    global $conn;
    $value = $conn->real_escape_string($value);
    return str_replace(['%', '_'], ['\%', '\_'], $value);
}

// Get Filter Values
$search       = trim($_GET['search'] ?? '');
$categoryId   = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$minPrice     = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float) $_GET['min_price'] : null;
$maxPrice     = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float) $_GET['max_price'] : null;
$prescription = trim($_GET['prescription'] ?? '');
$sort         = trim($_GET['sort'] ?? 'newest');
$currentPage  = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Pagination Settings
$productsPerPage = 12;

// Validate Price Range
if ($minPrice !== null && $minPrice < 0) {
    $minPrice = 0;
}
if ($maxPrice !== null && $maxPrice < 0) {
    $maxPrice = 0;
}
if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
    $temp = $minPrice;
    $minPrice = $maxPrice;
    $maxPrice = $temp;
}

// Validate Filters & Sorting
$allowedPrescription = ['', 'required', 'not_required'];
if (!in_array($prescription, $allowedPrescription, true)) {
    $prescription = '';
}

$allowedSorts = ['newest', 'name_asc', 'name_desc', 'price_low', 'price_high'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}

// Build WHERE Conditions
$where = ["p.status = 'active'"];

if ($search !== '') {
    $searchEscaped = shopLikeEscape($search);
    $where[] = "(
        p.product_name LIKE '%{$searchEscaped}%'
        OR p.description LIKE '%{$searchEscaped}%'
        OR p.sku LIKE '%{$searchEscaped}%'
        OR p.strength LIKE '%{$searchEscaped}%'
        OR p.dosage_form LIKE '%{$searchEscaped}%'
    )";
}

if ($categoryId > 0) {
    $where[] = "p.category_id = {$categoryId}";
}

if ($minPrice !== null) {
    $minPriceSql = number_format($minPrice, 2, '.', '');
    $where[] = "p.unit_price >= {$minPriceSql}";
}

if ($maxPrice !== null) {
    $maxPriceSql = number_format($maxPrice, 2, '.', '');
    $where[] = "p.unit_price <= {$maxPriceSql}";
}

if ($prescription === 'required') {
    $where[] = "p.requires_prescription = 1";
} elseif ($prescription === 'not_required') {
    $where[] = "p.requires_prescription = 0";
}

$whereSql = implode(' AND ', $where);

// Sorting SQL
switch ($sort) {
    case 'name_asc':
        $orderBy = "p.product_name ASC, p.product_id DESC";
        break;
    case 'name_desc':
        $orderBy = "p.product_name DESC, p.product_id DESC";
        break;
    case 'price_low':
        $orderBy = "p.unit_price ASC, p.product_id DESC";
        break;
    case 'price_high':
        $orderBy = "p.unit_price DESC, p.product_id DESC";
        break;
    case 'newest':
    default:
        $orderBy = "p.product_id DESC";
        break;
}

// Count Total Products
$countSql = "SELECT COUNT(*) AS total FROM products p WHERE {$whereSql}";
$countResult = $conn->query($countSql);

if (!$countResult) {
    die('Product count query failed: ' . $conn->error);
}

$countRow = $countResult->fetch_assoc();
$totalProducts = (int) ($countRow['total'] ?? 0);

// Pagination Calculations
$totalPages = max(1, (int) ceil($totalProducts / $productsPerPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $productsPerPage;

// Query Products
$productSql = "
    SELECT
        p.product_id,
        p.product_name,
        p.description,
        p.sku,
        p.category_id,
        p.dosage_form,
        p.strength,
        p.unit_price,
        p.requires_prescription,
        p.product_image,
        c.category_name,
        COALESCE(
            (
                SELECT SUM(pb.quantity_on_hand)
                FROM product_batches pb
                WHERE pb.product_id = p.product_id
                  AND pb.status = 'active'
                  AND pb.expiry_date >= CURDATE()
            ), 0
        ) AS stock_quantity
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE {$whereSql}
    ORDER BY {$orderBy}
    LIMIT {$productsPerPage}
    OFFSET {$offset}
";

$productResult = $conn->query($productSql);

if (!$productResult) {
    die('Products query failed: ' . $conn->error);
}

$products = [];
while ($row = $productResult->fetch_assoc()) {
    $products[] = $row;
}

// Query Categories
$categoriesSql = "
    SELECT category_id, category_name
    FROM categories
    WHERE status = 'active'
    ORDER BY category_name ASC
";

$categoriesResult = $conn->query($categoriesSql);

if (!$categoriesResult) {
    die('Categories query failed: ' . $conn->error);
}

$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

// URL Helper
function shopPageUrl(int $page): string
{
    $params = $_GET;
    $params['page'] = max(1, $page);
    return 'shop.php?' . http_build_query($params);
}