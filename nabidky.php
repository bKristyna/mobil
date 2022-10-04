<?php
global $db;

if (!$db) {
	exit;
}

// Delete quote
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	try {
		$db->query('DELETE FROM quotes WHERE id = ' . $_GET['delete'] . ' AND user_id = ' . $_SESSION['user']['id'])->execute();
		echo '<div class="alert alert-success">Nabídka byla smazána</div>';
	} catch (Exception $ex) {
		echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
	}
}

// Fetch all quotes
$quotes = $db->query('SELECT id, customer, note, discount FROM quotes WHERE user_id = ' . $_SESSION['user']['id'])->fetchAll();
?>


<div class="p-3">
	<h1>Nabídky</h1>
	<a href="<?= $_SERVER['PHP_SELF'] ?>?page=detail" class="btn btn-primary">Nová nabídka</a>
</div>

<div class="p-3">

	<?php foreach ($quotes as $quote) { ?>

		<div class="border p-3 mb-5">
			<h2>Nabídka #<?= $quote['id'] ?></h2>
			<ul>
				<li>Jméno zákazníka: <?= $quote['customer'] ?></li>
				<li>Poznámka: <?= $quote['note'] ?></li>
			</ul>

			<h3>Produkty</h3>

			<table class="table">
				<thead>
					<tr>
						<th scope="col">Název produktu</th>
						<th scope="col">Cena</th>
						<th scope="col">Počet</th>
						<th scope="col">Cena celkem</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_price = 0;
					$products = $db->query('SELECT name, price, quantity FROM quotes_products LEFT JOIN products ON products.id = product_id WHERE quote_id = ' . $quote['id'])->fetchAll();

					foreach ($products as $product) {
						$price = $product['price'] * $product['quantity'];
						$total_price += $price;
					?>

						<tr>
							<td><?= $product['name'] ?></td>
							<td><?= $product['price'] ?> Kč</td>
							<td><?= $product['quantity'] ?></td>
							<td><?= $price ?> Kč</td>
						</tr>

					<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="3">Mezisoučet</th>
						<td><?= $total_price ?> Kč</td>
					</tr>
					<tr>
						<th colspan="3">Sleva</th>
						<td><?= $quote['discount'] ?> %</td>
					</tr>
					<tr>
						<th colspan="3">Celkem</th>
						<td><strong><?= ($total_price * (100 - $quote['discount']) / 100) ?> Kč</strong></td>
					</tr>
				</tfoot>
			</table>

			<p>
				<a href="<?= $_SERVER['PHP_SELF'] ?>?page=detail&id=<?= $quote['id'] ?>" class="btn btn-primary">Upravit nabídku</a>
				<a href="<?= $_SERVER['PHP_SELF'] ?>?page=nabidky&delete=<?= $quote['id'] ?>" class="btn btn-danger" onclick="return confirm('Opravdu smazat nabídku?')">Smazat nabídku</a>
			</p>
		</div>

	<?php } ?>

</div>