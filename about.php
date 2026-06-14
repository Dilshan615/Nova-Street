<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Nova Street Fashion</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 
    $active_page = 'about';
    require_once 'includes/navbar.php'; 
    ?>

    <!-- Page Header -->
    <section class="py-5 mt-5">
        <div class="container py-5 text-center">
            <h6 class="text-uppercase text-muted fw-bold mb-3 letter-spacing-2" data-aos="fade-up">Our Heritage</h6>
            <h1 class="display-3 fw-bold mb-4" data-aos="fade-up" data-aos-delay="100">Nova Street: Redefining Modern Luxury</h1>
            <p class="lead text-muted mx-auto" style="max-width: 750px;" data-aos="fade-up" data-aos-delay="200">
                Since 2014, we've been crafting stories through style, blending traditional craftsmanship with minimalist design tokens.
            </p>
        </div>
    </section>

    <!-- Story Timeline -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Brand Milestones</h2>
                <p class="text-muted">A look back at how we started and where we are today.</p>
            </div>
            
            <div class="about-timeline">
                <div class="timeline-item" data-aos="fade-right">
                    <div class="timeline-badge"></div>
                    <div class="timeline-card">
                        <div class="timeline-year">2014</div>
                        <h4>The Beginning</h4>
                        <p class="text-muted mb-0">Nova Street was founded by a team of three visionaries in a Colombo workshop with a single sewing machine and a dream to craft timeless wardrobes.</p>
                    </div>
                </div>
                
                <div class="timeline-item" data-aos="fade-left">
                    <div class="timeline-badge"></div>
                    <div class="timeline-card">
                        <div class="timeline-year">2018</div>
                        <h4>Going Green</h4>
                        <p class="text-muted mb-0">We shifted 100% of our fabric supply chains to certified organic cotton and pure linen, committing to an eco-friendly and ethical production cycle.</p>
                    </div>
                </div>

                <div class="timeline-item" data-aos="fade-right">
                    <div class="timeline-badge"></div>
                    <div class="timeline-card">
                        <div class="timeline-year">2022</div>
                        <h4>Global Footprint</h4>
                        <p class="text-muted mb-0">We launched our online global shop, delivering minimalist Sri Lankan fashion designs to style enthusiasts in over 40 countries.</p>
                    </div>
                </div>

                <div class="timeline-item" data-aos="fade-left">
                    <div class="timeline-badge"></div>
                    <div class="timeline-card">
                        <div class="timeline-year">2026</div>
                        <h4>A New Era</h4>
                        <p class="text-muted mb-0">Nova Street introduces its new luxury cashmere and silk collections, blending ultimate comfort with modern couture.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Craftsmanship Showroom -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h6 class="text-uppercase text-muted fw-bold mb-2" style="letter-spacing: 1.5px;">Materials First</h6>
                <h2 class="display-5 fw-bold">Our Materials Showroom</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Every garment starts with premium fibers. We curate and weave our own fabrics for unparalleled quality.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="showroom-card">
                        <div class="showroom-img">
                            <img src="assets/img/summer.png" alt="Pure Linen">
                        </div>
                        <div class="showroom-body">
                            <span class="showroom-badge">100% Organic</span>
                            <h4 class="fw-bold">Belgian Flax Linen</h4>
                            <p class="text-muted mb-0">Naturally breathable, durable, and biodegradable. Our flax is grown without artificial irrigation or toxic fertilizers.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="showroom-card">
                        <div class="showroom-img">
                            <img src="assets/img/winter.png" alt="Cashmere">
                        </div>
                        <div class="showroom-body">
                            <span class="showroom-badge">Hand-Spun</span>
                            <h4 class="fw-bold">Himalayan Cashmere</h4>
                            <p class="text-muted mb-0">Ethically sheared, hand-combed, and spun by Himalayan families, providing unmatched lightweight insulation and luxury feel.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="showroom-card">
                        <div class="showroom-img">
                            <img src="assets/img/accessories.png" alt="Silk">
                        </div>
                        <div class="showroom-body">
                            <span class="showroom-badge">Fair Trade</span>
                            <h4 class="fw-bold">Mulberry Crepe Silk</h4>
                            <p class="text-muted mb-0">Woven on traditional wooden looms in small cooperatives. Smooth, lustrous, hypoallergenic, and colored with natural plant dyes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Meet The Team -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">The Creative Minds</h2>
                <p class="text-muted">Meet the designers and directors bringing Nova Street's vision to life.</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 col-sm-6" data-aos="fade-up">
                    <div class="team-card">
                        <img src="assets/img/hero.png" class="team-img" alt="Founder">
                        <h5>Aria Sterling</h5>
                        <p>Founder & Creative Director</p>
                        <p class="text-muted small mb-0">"Fashion should feel like a second skin, bringing quiet luxury and elegance to everyday wear."</p>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="150">
                    <div class="team-card">
                        <img src="assets/img/men.png" class="team-img" alt="Lead Tailor">
                        <h5>Marcus Vance</h5>
                        <p>Head of Craftsmanship</p>
                        <p class="text-muted small mb-0">"Every stitch we lay down is a promise of quality, built to stand decades in your luxury wardrobe."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>

</html>
