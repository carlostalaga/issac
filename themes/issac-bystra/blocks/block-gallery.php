<?php
/*
 ██████   █████  ██      ██      ███████ ██████  ██    ██
██       ██   ██ ██      ██      ██      ██   ██  ██  ██
██   ███ ███████ ██      ██      █████   ██████    ████
██    ██ ██   ██ ██      ██      ██      ██   ██    ██
 ██████  ██   ██ ███████ ███████ ███████ ██   ██    ██


*/

$bg = get_block_background();
$gallery_headline = get_sub_field('gallery_headline');
$gallery_content = get_sub_field('gallery_content');
$gallery = get_sub_field('gallery');
?>

<div id="gallery-<?php echo $iBlock; ?>" class="container-fluid <?php echo esc_attr($bg['class']); ?> py-5"<?php echo $bg['style_attr']; ?>>
    <div class="container">
        <?php if($gallery_headline): ?>
        <div class="mb-5">
            <h2><?php echo $gallery_headline; ?></h2>
        </div>
        <?php endif; ?>

        <?php if($gallery_content): ?>
        <div class="mb-5">
            <?php echo $gallery_content; ?>
        </div>
        <?php endif; ?>

        <?php if($gallery): ?>
        <div id="carouselGallery-<?php echo $iBlock; ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($gallery as $index => $image): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url($image['sizes']['1080p'] ?? $image['url']); ?>" data-lg-size="1920-1080" data-src="<?php echo esc_url($image['sizes']['1080p'] ?? $image['url']); ?>" data-thumb="<?php echo esc_url($image['sizes']['thumbnail'] ?? $image['url']); ?>">
                        <img src="<?php echo esc_url($image['sizes']['720p'] ?? $image['url']); ?>" class="d-block w-100 lazy" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery-<?php echo $iBlock; ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery-<?php echo $iBlock; ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            lightGallery(document.querySelector("#carouselGallery-<?php echo $iBlock; ?>"), {
                selector: "a",
                download: false,
                plugins: [lgThumbnail, lgZoom, lgAutoplay],
                thumbnail: true,
                zoom: true,
                autoplay: true,
                pause: 3000,
                progressBar: true,
            });
        });
        </script>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No gallery images available.
        </div>
        <?php endif; ?>
    </div>
</div>