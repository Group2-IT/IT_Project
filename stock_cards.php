<?php
if (!isset($products)) {
    $products = null;
}
if (!isset($showOrderButton)) {
    $showOrderButton = false;
}
?>
<?php if ($products && $products->num_rows > 0): ?>
    <div class="container">
        <?php while ($product = $products->fetch_assoc()): ?>
            <article class="card">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($product['pdt_name']); ?></h3>
                    <div class="price">UGX <?php echo number_format($product['price_per_unit']); ?> / <?php echo htmlspecialchars($product['unit_of_measure']); ?></div>
                    <div class="meta">
                        <span>Stock: <?php echo htmlspecialchars($product['qty_in_stock']); ?> <?php echo htmlspecialchars($product['unit_of_measure']); ?></span>
                        <?php if (!empty($product['description_age'])): ?>
                            <span><?php echo htmlspecialchars($product['description_age']); ?></span>
                        <?php endif; ?>
                        <span>Product ID: <?php echo htmlspecialchars($product['pdt_id']); ?></span>
                    </div>
                    <?php if ($showOrderButton): ?>
                        <button type="button" class="order-btn" data-product-id="<?php echo htmlspecialchars($product['pdt_id']); ?>" data-farmer-id="<?php echo htmlspecialchars($product['f_id']); ?>" data-price="<?php echo htmlspecialchars($product['price_per_unit']); ?>" data-stock="<?php echo htmlspecialchars($product['qty_in_stock']); ?>" data-unit="<?php echo htmlspecialchars($product['unit_of_measure']); ?>">Order Now</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <section class="no-stock">
        <h2>No vegetables are currently in stock.</h2>
        <p>Please check back later or contact farmers for the latest availability.</p>
    </section>
<?php endif; ?>
