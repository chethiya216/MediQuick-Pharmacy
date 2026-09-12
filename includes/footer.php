<style>
    .mq-footer {
        --mq-deep: #2720FF;
        --mq-blue: #006AFF;
        --mq-sky: #008DFF;
        --mq-primary: #00A3FF;
        --mq-cyan: #00B4DA;
        --mq-green: #00C391;
        --mq-dark: #071A2B;
        --mq-dark-2: #0B2438;
        --mq-text: #DDEBFA;
        --mq-muted: #AFC6DA;
        background: linear-gradient(135deg, #071A2B 0%, #0B2438 58%, #063A4B 100%);
        color: var(--mq-text);
        position: relative;
        overflow: hidden;
    }

    .mq-footer::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: linear-gradient(90deg,
            var(--mq-deep),
            var(--mq-blue),
            var(--mq-sky),
            var(--mq-primary),
            var(--mq-cyan),
            var(--mq-green));
    }

    .mq-footer a {
        color: var(--mq-muted);
        text-decoration: none;
        transition: all .25s ease;
    }

    .mq-footer a:hover {
        color: #FFFFFF;
        transform: translateX(3px);
    }

    .mq-contact-card {
        height: 100%;
        padding: 22px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 18px;
        background: rgba(255,255,255,.055);
        box-shadow: 0 12px 30px rgba(0,0,0,.12);
        transition: transform .25s ease, border-color .25s ease, background .25s ease;
    }

    .mq-contact-card:hover {
        transform: translateY(-4px);
        border-color: rgba(0,163,255,.55);
        background: rgba(255,255,255,.075);
    }

    .mq-contact-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        background: linear-gradient(135deg, #008DFF, #00C391);
        box-shadow: 0 8px 22px rgba(0,163,255,.22);
    }

    .mq-contact-card h6 {
        color: #FFFFFF;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .mq-contact-card p,
    .mq-contact-card a {
        color: var(--mq-muted);
        margin: 0;
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    .mq-footer-title {
        color: #FFFFFF;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 11px;
    }

    .mq-footer-title::after {
        content: "";
        width: 42px;
        height: 3px;
        border-radius: 20px;
        position: absolute;
        left: 0;
        bottom: 0;
        background: linear-gradient(90deg, #00A3FF, #00C391);
    }

    .mq-brand-name {
        font-size: 29px;
        line-height: 1;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 14px;
    }

    .mq-brand-name span {
        color: #00A3FF;
    }

    .mq-footer-text {
        color: var(--mq-muted);
        line-height: 1.8;
        max-width: 360px;
    }

    .mq-footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .mq-footer-links li {
        margin-bottom: 10px;
    }

    .mq-footer-links a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .mq-footer-links i {
        color: #00A3FF;
        font-size: 12px;
    }

    .mq-newsletter {
        display: flex;
        background: #FFFFFF;
        border-radius: 14px;
        padding: 5px;
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
        max-width: 360px;
    }

    .mq-newsletter input {
        min-width: 0;
        flex: 1;
        border: 0;
        outline: 0;
        box-shadow: none !important;
        padding: 10px 12px;
        background: transparent;
        color: #183247;
    }

    .mq-newsletter button {
        border: 0;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 700;
        color: #FFFFFF;
        background: linear-gradient(135deg, #00A3FF, #00C391);
        transition: transform .2s ease, box-shadow .2s ease;
        white-space: nowrap;
    }

    .mq-newsletter button:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,163,255,.28);
    }

    .mq-socials {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .mq-socials a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: #FFFFFF;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.07);
    }

    .mq-socials a:hover {
        transform: translateY(-3px);
        background: #00A3FF;
        border-color: #00A3FF;
    }

    .mq-footer-bottom {
        border-top: 1px solid rgba(255,255,255,.10);
        background: rgba(0,0,0,.18);
        color: var(--mq-muted);
    }

    .mq-footer-bottom a {
        color: #FFFFFF;
    }

    .mq-footer-bottom strong {
        color: #FFFFFF;
    }

    .mq-back-to-top {
        position: fixed;
        right: 28px;
        bottom: 28px;
        z-index: 99;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF !important;
        background: linear-gradient(135deg, #00A3FF, #00C391);
        border: 3px solid rgba(255,255,255,.85);
        box-shadow: 0 10px 25px rgba(0,0,0,.25);
    }

    .mq-back-to-top:hover {
        transform: translateY(-3px);
        color: #FFFFFF !important;
    }

    @media (max-width: 767.98px) {
        .mq-contact-card { padding: 18px; }
        .mq-footer-title { margin-top: 4px; }
        .mq-newsletter { max-width: 100%; }
        .mq-back-to-top { right: 18px; bottom: 18px; }
    }
</style>

<footer class="mq-footer mt-5">
    <div class="container py-5">
        <!-- Contact cards -->
        <div class="row g-3 mb-5 pt-2">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="mq-contact-card d-flex align-items-center gap-3">
                    <div class="mq-contact-icon"><i class="fas fa-map-marker-alt fa-lg"></i></div>
                    <div>
                        <h6>Visit Us</h6>
                        <p>Negombo, Sri Lanka</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="mq-contact-card d-flex align-items-center gap-3">
                    <div class="mq-contact-icon"><i class="fas fa-envelope fa-lg"></i></div>
                    <div>
                        <h6>Email Us</h6>
                        <a href="mailto:info@mediquick.lk">info@mediquick.lk</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="mq-contact-card d-flex align-items-center gap-3">
                    <div class="mq-contact-icon"><i class="fas fa-phone-alt fa-lg"></i></div>
                    <div>
                        <h6>Call Us</h6>
                        <a href="tel:+94112345678">+94 11 234 5678</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="mq-contact-card d-flex align-items-center gap-3">
                    <div class="mq-contact-icon"><i class="fas fa-clock fa-lg"></i></div>
                    <div>
                        <h6>Opening Hours</h6>
                        <p>Mon - Sun: 8.00 AM - 10.00 PM</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main footer content -->
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div class="mq-brand-name">Medi<span>Quick</span></div>
                <p class="mq-footer-text mb-3">
                    Your simple and convenient online pharmacy for everyday healthcare products and medicines.
                </p>
                <div class="mq-socials">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h5 class="mq-footer-title">Quick Links</h5>
                <ul class="mq-footer-links">
                    <li><a href="index.php"><i class="fas fa-angle-right"></i> Home</a></li>
                    <li><a href="shop.php"><i class="fas fa-angle-right"></i> Shop</a></li>
                    <li><a href="cart.php"><i class="fas fa-angle-right"></i> Cart</a></li>
                    <li><a href="contact.php"><i class="fas fa-angle-right"></i> Contact</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h5 class="mq-footer-title">Customer</h5>
                <ul class="mq-footer-links">
                    <li><a href="manage-account.php"><i class="fas fa-angle-right"></i> My Account</a></li>
                    <li><a href="login.php"><i class="fas fa-angle-right"></i> Login</a></li>
                    <li><a href="register.php"><i class="fas fa-angle-right"></i> Register</a></li>
                    <li><a href="checkout.html"><i class="fas fa-angle-right"></i> Checkout</a></li>
                </ul>
            </div>

        </div>
    </div>

    <div class="mq-footer-bottom py-3">
        <div class="container">
            <div class="row align-items-center g-2">
                <div class="col-md-6 text-center text-md-start">
                    &copy; <?php echo date('Y'); ?> <strong>MediQuick Pharmacy</strong>. All rights reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    Designed for <strong>MediQuick Pharmacy</strong>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- MediQuick Footer End -->

<!-- Back to Top -->
<a href="#" class="mq-back-to-top" aria-label="Back to top"><i class="fa fa-arrow-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/lib/wow/wow.min.js"></script>
<script src="assets/lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="assets/js/main.js"></script>