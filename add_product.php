<?php
require_once 'db_connect.php';

// Fetch categories and subcategories
$categories_sql = "SELECT * FROM categories";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$subcategories_sql = "SELECT * FROM subcategories";
$subcategories_stmt = $pdo->query($subcategories_sql);
$subcategories = $subcategories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch phones
$phones_sql = "SELECT * FROM phones";
$phones_stmt = $pdo->query($phones_sql);
$phones = $phones_stmt->fetchAll(PDO::FETCH_ASSOC);

$edit_mode = false;
$product = [
    'name' => '', 'description' => '', 'price' => '', 'sale_price' => '',
    'category_id' => '', 'subcategory_id' => '', 'is_new_arrival' => 0,
    'is_sale' => 0, 'image_path' => ''
];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch compatible phones for phone cases
    if ($product['category_id'] == 'Phone Cases Category ID') {
        $compatible_phones_sql = "SELECT phone_id FROM phone_case_compatibility WHERE product_id = ?";
        $compatible_phones_stmt = $pdo->prepare($compatible_phones_sql);
        $compatible_phones_stmt->execute([$id]);
        $compatible_phones = $compatible_phones_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $subcategory_id = $_POST['subcategory_id'];
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $is_sale = isset($_POST['is_sale']) ? 1 : 0;
    $sale_price = $is_sale ? $_POST['sale_price'] : null;
    $image_path = $_POST['image_path'];

    if ($category_id == '3') {
        $compatible_phones = isset($_POST['compatible_phones']) ? $_POST['compatible_phones'] : [];

        if ($edit_mode) {
            // Delete existing compatibility entries
            $delete_compatibility_sql = "DELETE FROM phone_case_compatibility WHERE product_id = ?";
            $delete_compatibility_stmt = $pdo->prepare($delete_compatibility_sql);
            $delete_compatibility_stmt->execute([$id]);
        }

        // Insert new compatibility entries
        $insert_compatibility_sql = "INSERT INTO phone_case_compatibility (product_id, phone_id) VALUES (?, ?)";
        $insert_compatibility_stmt = $pdo->prepare($insert_compatibility_sql);
        foreach ($compatible_phones as $phone_id) {
            $insert_compatibility_stmt->execute([$id, $phone_id]);
        }
    }

    if ($edit_mode) {
        $sql = "UPDATE products SET name = :name, description = :description, price = :price, 
                sale_price = :sale_price, category_id = :category_id, subcategory_id = :subcategory_id, 
                is_new_arrival = :is_new_arrival, is_sale = :is_sale, image_path = :image_path 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name, 'description' => $description, 'price' => $price,
            'sale_price' => $sale_price, 'category_id' => $category_id,
            'subcategory_id' => $subcategory_id, 'is_new_arrival' => $is_new_arrival,
            'is_sale' => $is_sale, 'image_path' => $image_path, 'id' => $id
        ]);
        $message = "Product updated successfully!";
    } else {
        $sql = "INSERT INTO products (name, description, price, sale_price, category_id, subcategory_id, is_new_arrival, is_sale, image_path) 
                VALUES (:name, :description, :price, :sale_price, :category_id, :subcategory_id, :is_new_arrival, :is_sale, :image_path)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name, 'description' => $description, 'price' => $price,
            'sale_price' => $sale_price, 'category_id' => $category_id,
            'subcategory_id' => $subcategory_id, 'is_new_arrival' => $is_new_arrival,
            'is_sale' => $is_sale, 'image_path' => $image_path
        ]);
        $message = "Product added successfully!";
    }
    
    header("Location: product_management.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Product</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category:</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="mb-3">
                <label for="subcategory_id" class="form-label">Subcategory:</label>
                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                    <?php foreach ($subcategories as $subcategory): ?>
                        <option value="<?php echo $subcategory['id']; ?>" data-category="<?php echo $subcategory['category_id']; ?>" <?php echo $subcategory['id'] == $product['subcategory_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subcategory['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="phone_compatibility" class="mb-3" style="display: none;">
                <label class="form-label">Compatible Phones:</label>
                <?php foreach ($phones as $phone): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="compatible_phones[]" value="<?php echo $phone['id']; ?>" id="phone_<?php echo $phone['id']; ?>" <?php echo (isset($compatible_phones) && in_array($phone['id'], $compatible_phones)) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="phone_<?php echo $phone['id']; ?>">
                            <?php echo htmlspecialchars($phone['brand'] . ' ' . $phone['model']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_new_arrival" name="is_new_arrival" <?php echo $product['is_new_arrival'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_new_arrival">New Arrival</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_sale" name="is_sale" <?php echo $product['is_sale'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_sale">On Sale</label>
            </div>

            <div class="mb-3" id="sale_price_container" style="display: <?php echo $product['is_sale'] ? 'block' : 'none'; ?>;">
                <label for="sale_price" class="form-label">Sale Price:</label>
                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" value="<?php echo $product['sale_price']; ?>">
            </div>

            <div class="mb-3">
                <label for="image_path" class="form-label">Image Path:</label>
                <input type="text" class="form-control" id="image_path" name="image_path" value="<?php echo htmlspecialchars($product['image_path']); ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Product</button>
            <a href="product_management.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('is_sale').addEventListener('change', function() {
            document.getElementById('sale_price_container').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('category_id').addEventListener('change', function() {
            updateSubcategories();
            updatePhoneCompatibility();
        });

        function updateSubcategories() {
            const categoryId = document.getElementById('category_id').value;
            const subcategorySelect = document.getElementById('subcategory_id');
            const subcategoryOptions = subcategorySelect.options;

            for (let i = 0; i < subcategoryOptions.length; i++) {
                const option = subcategoryOptions[i];
                if (option.dataset.category === categoryId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }

            // Select the first visible option
            for (let i = 0; i < subcategoryOptions.length; i++) {
                const option = subcategoryOptions[i];
                if (option.style.display !== 'none') {
                    subcategorySelect.value = option.value;
                    break;
                }
            }
        }

        function updatePhoneCompatibility() {
            const categoryId = document.getElementById('category_id').value;
            const phoneCompatibilityDiv = document.getElementById('phone_compatibility');
            
            if (categoryId === '3') {
                phoneCompatibilityDiv.style.display = 'block';
            } else {
                phoneCompatibilityDiv.style.display = 'none';
            }
        }

        // Initial update
        updateSubcategories();
        updatePhoneCompatibility();
    </script>
</body>
</html>
