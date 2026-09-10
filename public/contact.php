<?php
require_once('../includes/db.php');
require_once('../includes/auth.php');
require_once('../includes/header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch customer ID from session if available (returns null for guests)
$customerId = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<body>

    <!-- Contact Start -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="p-5 bg-light rounded">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 900px;">
                            <h4 class="text-primary border-bottom border-primary border-2 d-inline-block pb-2">Get in touch</h4>
                            <p class="mb-5 fs-5 text-dark">We are here for you! How can we help?</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <h5 class="text-primary wow fadeInUp" data-wow-delay="0.1s">Let’s Connect</h5>
                        <h1 class="display-5 mb-4 wow fadeInUp" data-wow-delay="0.3s">Send Your Message</h1>

                        <form action="../public/handlers/contact-handler.php" method="POST">
                            <div class="row g-4 wow fadeInUp" data-wow-delay="0.1s">
                                
                                <!-- Dynamic Customer ID -->
                               <input type="hidden" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($customerId); ?>">

                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
                                        <label for="email">Your Email</label>
                                    </div>
                                </div>
                               <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Your Phone">
                                        <label for="phone">Your Phone</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                                        <label for="subject">Subject</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Leave a message here" id="message" name="message" style="height: 160px" required></textarea>
                                        <label for="message">Message</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-3">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-5 wow fadeInUp" data-wow-delay="0.2s">
                        <div class="h-100 rounded">
                            <iframe class="rounded w-100" style="height: 100%;"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.33750346623!2d-73.97968099999999!3d40.6974881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1694259649153!5m2!1sen!2sbd"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

    <?php include('../includes/footer.php') ?>

</body>

</html>