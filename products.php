<?php
// Define your database connection settings here.
$dbHost = 'dbHost';
$dbName = 'dbName';
$dbUser = 'dbUser';
$dbPass = 'dbPass';

try {
    // Create a PDO database connection.
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set the content type to JSON.
    header('Content-Type: application/json');

    // Check if the request method is POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if either 'page_unique' or 'page_url' is present in the POST data.
        if (isset($_POST['page_unique']) || isset($_POST['page_url'])) {
            // Handle retrieving a specific product based on 'page_unique' or 'page_url'.

            // Construct a SQL query to retrieve the product based on the provided parameter.
            $query = '';
            $params = [];

            if (isset($_POST['page_unique'])) {
                $query = 'SELECT * FROM products WHERE page_unique = ?';
                $params[] = $_POST['page_unique'];
            } elseif (isset($_POST['page_url'])) {
                $query = 'SELECT * FROM products WHERE page_url = ?';
                $params[] = $_POST['page_url'];
            }

            // Execute the SQL query.
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);
			// Format the image_links array as JSON.
			$product['image_links'] = json_decode($product['image_links'], true);
            $product['current_price'] = floatval($product['current_price']);
            $product['old_price'] = floatval($product['old_price']);

            $outputData = array(
				"count" => 1,
				"max_pages" => 1,
				"products" => $product
			);
			echo json_encode($outputData, JSON_UNESCAPED_UNICODE);
        } else {
            // Handle the case when neither 'page_unique' nor 'page_url' is provided.
            // Return a list of products (you can adjust this query based on your needs).
            // Order products by the newest first.
			$query = "SELECT COUNT(*) AS total_products FROM products";
			$stmt = $pdo->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalProducts = $row["total_products"];
			$productsPerPage = 100;
			$totalPages = ceil($totalProducts / $productsPerPage);
            $stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 100');
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
			// Format the image_links array as JSON for each product.
			foreach ($products as &$product) {
				$product['image_links'] = json_decode($product['image_links'], true);
                $product['current_price'] = floatval($product['current_price']);
                $product['old_price'] = floatval($product['old_price']);
			}
			$outputData = array(
				"count" => $totalProducts,
				"max_pages" => $totalPages,
				"products" => $products
			);
            echo json_encode($outputData, JSON_UNESCAPED_UNICODE);
        }
    }
} catch (PDOException $e) {
    // Handle any database connection errors here.
    echo 'Database connection failed: ' . $e->getMessage();
}
?>