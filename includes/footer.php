    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-3">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">Your one-stop shop for quality products at great prices.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Customer Service</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/shipping.php" class="text-muted text-decoration-none">Shipping Info</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns.php" class="text-muted text-decoration-none">Returns</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/support.php" class="text-muted text-decoration-none">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Follow Us</h6>
                    <div class="d-flex">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <div class="mt-3">
                        <h6>Newsletter</h6>
                        <form class="d-flex">
                            <input type="email" class="form-control form-control-sm me-2" placeholder="Your email">
                            <button class="btn btn-primary btn-sm" type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="<?php echo SITE_URL; ?>/terms.php" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>