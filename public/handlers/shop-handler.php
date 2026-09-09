<?php
/**
 * MediQuick Pharmacy
 * Shop Handler
 *
 * Location:
 * public/handlers/shop-handler.php
 *
 * Responsibilities:
 * - Read shop filters
 * - Query products
 * - Load categories
 * - Calculate stock
 * - Handle sorting
 * - Handle pagination
 *
 * NOTE:
 * - generic_name is NOT used
 * - discount_percent is NOT used
 * - barcode is NOT used
 * - unit_price is the selling price
 */


// ==================================================
// DATABASE CHECK
// ==================================================

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available.');
}


// ==================================================
// HELPER FUNCTIONS
// ==================================================

/**
 * Escape a normal SQL string.
 */
function shopEscape(string $value): string
{
    global $conn;

    return $conn->real_escape_string($value);
}


/**
 * Escape a value used inside LIKE.
 *
 * % and _ are SQL LIKE wildcards,
 * so they are escaped separately.
 */
function shopLikeEscape(string $value): string
{
    global $conn;

    $value = $conn->real_escape_string($value);

    $value = str_replace(
        ['%', '_'],
        ['\%', '\_'],
        $value
    );

    return $value;
}


// ==================================================
// GET FILTER VALUES
// ==================================================

$search = trim(
    $_GET['search'] ?? ''
);


$categoryId = isset($_GET['category'])
    ? (int) $_GET['category']
    : 0;


$minPrice = (
    isset($_GET['min_price']) &&
    $_GET['min_price'] !== ''
)
    ? (float) $_GET['min_price']
    : null;


$maxPrice = (
    isset($_GET['max_price']) &&
    $_GET['max_price'] !== ''
)
    ? (float) $_GET['max_price']
    : null;


$prescription = trim(
    $_GET['prescription'] ?? ''
);


$sort = trim(
    $_GET['sort'] ?? 'newest'
);


$currentPage = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;


// ==================================================
// PAGINATION SETTINGS
// ==================================================

$productsPerPage = 12;


// ==================================================
// VALIDATE PRICE RANGE
// ==================================================

if ($minPrice !== null && $minPrice < 0) {
    $minPrice = 0;
}


if ($maxPrice !== null && $maxPrice < 0) {
    $maxPrice = 0;
}


if (
    $minPrice !== null &&
    $maxPrice !== null &&
    $minPrice > $maxPrice
) {
    $temp = $minPrice;

    $minPrice = $maxPrice;
    $maxPrice = $temp;
}


// ==================================================
// VALIDATE PRESCRIPTION FILTER
// ==================================================

$allowedPrescription = [
    '',
    'required',
    'not_required'
];


if (!in_array(
    $prescription,
    $allowedPrescription,
    true
)) {
    $prescription = '';
}


// ==================================================
// VALIDATE SORT
// ==================================================

$allowedSorts = [
    'newest',
    'name_asc',
    'name_desc',
    'price_low',
    'price_high'
];


if (!in_array(
    $sort,
    $allowedSorts,
    true
)) {
    $sort = 'newest';
}


// ==================================================
// BUILD WHERE CONDITIONS
// ==================================================

$where = [];


/**
 * Only active products.
 */
$where[] = "p.status = 'active'";


// ==================================================
// SEARCH FILTER
// ==================================================

if ($search !== '') {

    $searchEscaped = shopLikeEscape($search);

    $where[] = "
        (
            p.product_name LIKE '%{$searchEscaped}%'
            OR p.description LIKE '%{$searchEscaped}%'
            OR p.sku LIKE '%{$searchEscaped}%'
            OR p.strength LIKE '%{$searchEscaped}%'
            OR p.dosage_form LIKE '%{$searchEscaped}%'
        )
    ";
}


// ==================================================
// CATEGORY FILTER
// ==================================================

if ($categoryId > 0) {

    $where[] = "p.category_id = {$categoryId}";
}


// ==================================================
// MINIMUM PRICE FILTER
// ==================================================

if ($minPrice !== null) {

    $minPriceSql = number_format(
        $minPrice,
        2,
        '.',
        ''
    );

    $where[] = "
        p.unit_price >= {$minPriceSql}
    ";
}


// ==================================================
// MAXIMUM PRICE FILTER
// ==================================================

if ($maxPrice !== null) {

    $maxPriceSql = number_format(
        $maxPrice,
        2,
        '.',
        ''
    );

    $where[] = "
        p.unit_price <= {$maxPriceSql}
    ";
}


// ==================================================
// PRESCRIPTION FILTER
// ==================================================

if ($prescription === 'required') {

    $where[] = "
        p.requires_prescription = 1
    ";
}


if ($prescription === 'not_required') {

    $where[] = "
        p.requires_prescription = 0
    ";
}


// ==================================================
// CREATE WHERE SQL
// ==================================================

$whereSql = implode(
    ' AND ',
    $where
);


// ==================================================
// SORTING
// ==================================================

switch ($sort) {

    case 'name_asc':

        $orderBy = "
            p.product_name ASC,
            p.product_id DESC
        ";

        break;


    case 'name_desc':

        $orderBy = "
            p.product_name DESC,
            p.product_id DESC
        ";

        break;


    case 'price_low':

        $orderBy = "
            p.unit_price ASC,
            p.product_id DESC
        ";

        break;


    case 'price_high':

        $orderBy = "
            p.unit_price DESC,
            p.product_id DESC
        ";

        break;


    case 'newest':

    default:

        $orderBy = "
            p.product_id DESC
        ";

        break;
}


// ==================================================
// COUNT PRODUCTS
// ==================================================

$countSql = "
    SELECT
        COUNT(*) AS total
    FROM products p
    WHERE {$whereSql}
";


$countResult = $conn->query(
    $countSql
);


if (!$countResult) {
    die(
        'Product count query failed: ' .
        $conn->error
    );
}


$countRow = $countResult->fetch_assoc();


$totalProducts = (int) (
    $countRow['total'] ?? 0
);


// ==================================================
// CALCULATE TOTAL PAGES
// ==================================================

$totalPages = max(
    1,
    (int) ceil(
        $totalProducts / $productsPerPage
    )
);


// ==================================================
// MAKE SURE CURRENT PAGE IS VALID
// ==================================================

if ($currentPage > $totalPages) {

    $currentPage = $totalPages;
}


// ==================================================
// CALCULATE OFFSET
// ==================================================

$offset = (
    $currentPage - 1
) * $productsPerPage;


// ==================================================
// PRODUCTS QUERY
// ==================================================
//
// IMPORTANT:
// - generic_name is NOT selected
// - discount_percent is NOT selected
// - barcode is NOT selected
// - no discount calculation
// - unit_price is the selling price
//

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
                SELECT
                    SUM(pb.quantity_on_hand)

                FROM product_batches pb

                WHERE
                    pb.product_id = p.product_id

                    AND pb.status = 'active'

                    AND pb.expiry_date >= CURDATE()
            ),
            0
        ) AS stock_quantity

    FROM products p

    LEFT JOIN categories c
        ON p.category_id = c.category_id

    WHERE {$whereSql}

    ORDER BY {$orderBy}

    LIMIT {$productsPerPage}

    OFFSET {$offset}
";


// ==================================================
// EXECUTE PRODUCTS QUERY
// ==================================================

$productResult = $conn->query(
    $productSql
);


if (!$productResult) {

    die(
        'Products query failed: ' .
        $conn->error
    );

}


// ==================================================
// PRODUCTS ARRAY
// ==================================================

$products = [];


while ($row = $productResult->fetch_assoc()) {

    $products[] = $row;
}


// ==================================================
// LOAD CATEGORIES
// ==================================================

$categoriesSql = "
    SELECT

        category_id,

        category_name

    FROM categories

    WHERE status = 'active'

    ORDER BY category_name ASC
";


$categoriesResult = $conn->query(
    $categoriesSql
);


if (!$categoriesResult) {

    die(
        'Categories query failed: ' .
        $conn->error
    );

}


$categories = [];


while ($row = $categoriesResult->fetch_assoc()) {

    $categories[] = $row;
}


// ==================================================
// SHOP URL HELPER
// ==================================================

/**
 * Generate pagination URL while
 * keeping existing filters.
 */
function shopPageUrl(int $page): string
{
    $params = $_GET;

    $params['page'] = max(
        1,
        $page
    );

    return 'shop.php?' . http_build_query(
        $params
    );
}


// ==================================================
// END OF SHOP HANDLER
// ==================================================