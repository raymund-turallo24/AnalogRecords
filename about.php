<?php
session_start();
include('./includes/header.php'); 
?>

<div class="container-fluid about-page-content">

    <header class="about-hero text-center">
        <h1 class="display-1 about-title">Our Analog Story</h1>
        <p class="lead about-subtitle">
            Curated soundscapes. Timeless format. The home of premium vinyl.
        </p>
    </header>

    <div class="row about-section-row">
        
        <div class="col-lg-6 col-md-12 about-text-block order-lg-1 order-2">
            <h2 class="section-heading">The Pursuit of Pure Sound</h2>
            <p class="section-body">
                In a digital world, Analog Records is an anchor to the true fidelity of sound. We believe that music is meant to be experienced, not just consumed. Our collection is meticulously hand-picked—each album a testament to exceptional artistry and the warm, irreplaceable quality of vinyl playback.
            </p>
            <p class="section-body">
                We cater to the discerning audiophile, the dedicated collector, and the new enthusiast seeking an authentic connection to their favorite artists. This is more than a store; it’s a sanctuary for sound.
            </p>
        </div>
        <div class="col-lg-6 col-md-12 about-image-block mb-4 mb-lg-0 order-lg-2 order-1">
            <div class="card-image-wrapper">
                <img src="./images/about-image-1.jpg" alt="A person carefully placing a vinyl record on a turntable" class="about-image rounded-3">
            </div>
        </div>

    </div>
    
    <hr class="section-divider">

    <div class="row about-section-row reverse-mobile">
        
        <div class="col-lg-6 col-md-12 about-image-block mb-4 mb-lg-0">
            <div class="card-image-wrapper">
                <img src="./images/about-image-2.jpg" alt="Close-up of a record cutter head creating grooves on a master disc" class="about-image rounded-3">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 about-text-block">
            <h2 class="section-heading">Curation & Craftsmanship</h2>
            <ul class="list-unstyled detail-list">
                <li>
                    <i class="fas fa-check-circle check-icon"></i> 
                    <strong>Hand-Selected Inventory:</strong> Every record is chosen for its acoustic quality, pressing excellence, and cultural significance. We avoid fillers, focusing only on premium vinyl.
                </li>
                <li>
                    <i class="fas fa-check-circle check-icon"></i> 
                    <strong>Sourcing Excellence:</strong> We partner directly with revered labels and audiophile pressing plants known for their mastering and vinyl quality.
                </li>
                <li>
                    <i class="fas fa-check-circle check-icon"></i> 
                    <strong>The Aesthetic Experience:</strong> From gatefold sleeves to limited-edition color vinyl, we celebrate the physical art of the album package.
                </li>
            </ul>
        </div>
        
    </div>
    
    <hr class="section-divider">

    <div class="row justify-content-center pt-5 pb-5">
        <div class="col-lg-10">
            <div class="glass-quote-box p-5 text-center">
                <blockquote class="blockquote mb-4">
                    <i class="fas fa-quote-left quote-icon"></i>
                    <p class="mb-0 quote-text">
                        "Vinyl is the only format that truly gives you everything. The warmth, the weight, the ritual—it demands your attention, and it repays that attention with a sound unlike anything else."
                    </p>
                    <footer class="blockquote-footer mt-3">
                        <cite title="Source Title">Elijah Gallardo, Founder of Analog Records</cite>
                    </footer>
                </blockquote>
            </div>
        </div>
    </div>
    
</div>

<?php
include('./includes/footer.php'); 
?>