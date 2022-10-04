<?php
global $db;

if (!$db) {
  exit;
}

$quote_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;

// Form submitted
if (isset($_POST['submit'])) {
  try {
    // Create or update quote
    if ($quote_id) {
      $query = $db->prepare('UPDATE quotes SET customer = :customer, note = :note, discount = :discount WHERE id = :quote_id AND user_id = :user_id');
      $query->bindParam(':quote_id', $quote_id);
    } else {
      $query = $db->prepare('INSERT INTO quotes (user_id, customer, note, discount) VALUES (:user_id, :customer, :note, :discount)');
    }

    $query->bindParam(':user_id', $_SESSION['user']['id']);
    $query->bindParam(':customer', $_POST['customer']);
    $query->bindParam(':note', $_POST['note']);
    $query->bindParam(':discount', $_POST['discount']);
    $query->execute();

    // Insert products to quote
    if ($quote_id) {
      $db->query('DELETE FROM quotes_products WHERE quote_id = ' . $quote_id);
    } else {
      $quote_id = $db->lastInsertId();
    }

    foreach ($_POST['products'] as $product) {
      if ($product['id'] && $product['quantity'] > 0) {
        $query = $db->prepare('INSERT INTO quotes_products (quote_id, product_id, quantity) VALUES (:quote_id, :product_id, :quantity)');
        $query->bindParam(':quote_id', $quote_id);
        $query->bindParam(':product_id', $product['id']);
        $query->bindParam(':quantity', $product['quantity']);
        $query->execute();
      }
    }

    echo '<div class="alert alert-success">Nabídka byla uložena</div>';
  } catch (Exception $ex) {
    echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
  }
}

// Edit mode - fetch quote data
if ($quote_id) {
  $quote = $db->query('SELECT user_id, customer, note, discount FROM quotes WHERE id = ' . $quote_id . ' AND user_id = ' . $_SESSION['user']['id'])->fetch();

  if ($quote) {
    $quote_products = $db->query('SELECT product_id, quantity FROM quotes_products WHERE quote_id = ' . $quote_id)->fetchAll();
  } else {
    echo '<div class="alert alert-danger">Toto není vaše nabídka</div>';
    exit;
  }
}

// Get all products to populate select fields
$products = $db->query('SELECT id, name, price FROM products')->fetchAll();
?>

<div class="p-3">
  <h1><?= (isset($_GET['id']) ? 'Upravit nabídku #' . $quote_id : 'Nová nabídka') ?></h1>
  <a href="<?= $_SERVER['PHP_SELF'] ?>?page=nabidky" class="btn btn-primary"> Zpět na seznam</a>
</div>

<div class="p-3">
  <form method="post">
    <div class="form-group mb-3">
      <label for="customer" class="form-label">Jméno zákazníka</label>
      <input type="text" name="customer" id="customer" class="form-control" value="<?= (isset($quote) ? $quote['customer'] : '') ?>" />
    </div>
    <div class="form-group mb-3">
      <label for="note" class="form-label">Poznámka</label>
      <textarea name="note" id="note" class="form-control"><?= (isset($quote) ? $quote['note'] : '') ?></textarea>
    </div>
    <div class="form-group mb-3">
      <label for="discount" class="form-label">Sleva</label>
      <input type="number" name="discount" id="discount" class="form-control" min="0" max="100" value="<?= (isset($quote) ? $quote['discount'] : '') ?>" />
    </div>

    <h2>Produkty</h2>
    <?php for ($i = 0; $i < 5; $i++) { ?>
      <div class="form-group mb-3">
        <div class="d-flex">
          <input type="number" name="products[<?= $i ?>][quantity]" id="quantity<?= $i ?>" class="form-control" min="0" style="width: 70px" value="<?= (isset($quote_products) ? $quote_products[$i]['quantity'] : 0) ?>" />
          <span class="p-2">x</span>
          <select name="products[<?= $i ?>][id]" id="product<?= $i ?>" class="form-select" placeholder="Vyberte produkt">
            <option>Vyberte produkt</option>
            <?php foreach ($products as $product) { ?>
              <option value="<?= $product['id'] ?>" <?= (isset($quote_products) && $quote_products[$i]['product_id'] == $product['id'] ? ' selected' : '') ?>><?= $product['name'] ?> (<?= $product['price'] ?> Kč)</option>
            <?php } ?>
          </select>
        </div>
      </div>
    <?php } ?>

    <button type="submit" name="submit" class="btn btn-primary"><?= (isset($_GET['id']) ? 'Upravit' : 'Vytvořit') ?></button>
  </form>
</div>