    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-3">
                    <h5><i class="fas fa-gavel text-warning me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">Your trusted auction house for quality items at great prices.</p>
                    <div class="contact-info">
                        <p class="mb-1 text-light">
                            <i class="fas fa-phone text-warning me-2"></i>
                            <small>09512723785</small>
                        </p>
                        <p class="mb-0 text-light">
                            <i class="fas fa-envelope text-warning me-2"></i>
                            <small>jbrincestrading0716@gmail.com</small>
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6><i class="fas fa-clock text-warning me-2"></i>Auction Schedule</h6>
                    <ul class="list-unstyled">
                        <li class="text-muted">
                            <strong class="text-warning">Face-to-Face Auction:</strong><br>
                            <small>Wed & Sat: 10:00am - 6:00pm</small>
                        </li>
                        <li class="text-muted mt-2">
                            <strong class="text-warning">Online Auction:</strong><br>
                            <small>Daily: 7:00pm - 9:00pm</small><br>
                            <small class="text-info">www.jbrincesbid.com</small>
                        </li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6><i class="fas fa-map-marker-alt text-warning me-2"></i>Location & Info</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php" class="text-muted text-decoration-none">Auction Items</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-building text-warning me-1"></i>
                            0261 D San Luis St. Purob 6,<br>
                            Landayan, San Pedro, Laguna
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6>Follow Us</h6>
                    <div class="d-flex">
                        <a href="https://www.facebook.com/" target="_blank" class="text-muted me-3" title="Follow us on Facebook">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="https://twitter.com/" target="_blank" class="text-muted me-3" title="Follow us on Twitter">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="https://www.tiktok.com/" target="_blank" class="text-muted me-3" title="Follow us on TikTok">
                            <i class="fab fa-tiktok fa-lg"></i>
                        </a>
                        <a href="https://www.instagram.com/" target="_blank" class="text-muted" title="Follow us on Instagram">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
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