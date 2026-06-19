<?php
/*
██       ██████   ██████  █████  ████████ ██  ██████  ███    ██ ███████
██      ██    ██ ██      ██   ██    ██    ██ ██    ██ ████   ██ ██
██      ██    ██ ██      ███████    ██    ██ ██    ██ ██ ██  ██ ███████
██      ██    ██ ██      ██   ██    ██    ██ ██    ██ ██  ██ ██      ██
███████  ██████   ██████ ██   ██    ██    ██  ██████  ██   ████ ███████
*/

/*
Flexible Content layout: "locations"
ACF field group: acf-json/group_6670bd6172100.json (layout_69d5f533bafea)

Locations are managed in General Options:
  acf-json/group_6678ceb7becfe.json -> locations repeater
*/

$bg = get_block_background();
$locations = get_field('locations', 'option');

?>

<div id="locationsBlock-<?php echo $iBlock; ?>" class="container-fluid py-5 px-5 px-md-0 <?php echo esc_attr($bg['class']); ?>" <?php echo $bg['style_attr']; ?>>



    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-11 col-lg-10">


                <div class="container py-5 ">

                    <div class="py-5">
                        <h2>Office Locations</h2>
                    </div>

                    <!-- Locations Repeater -->
                    <?php
        if ($locations) : $iLocations = 0;
        ?>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-5 d-flex justify-content-center py-5">

                        <?php
            foreach ($locations as $location_item) :
                $location_name = $location_item['location'] ?? '';
                $location_image = $location_item['image'] ?? '';
                $location_image_url = '';
                $location_image_alt = '';
                if ($location_image) :
                    $location_image_url = $location_image['sizes']['576sm'] ?? $location_image['url'];
                    $location_image_alt = $location_image['alt'] ?? '';
                endif;
                $location_address = $location_item['address'] ?? '';
                $location_phone = $location_item['phone'] ?? '';
                $location_phone_link = $location_phone ? preg_replace('/[^0-9+]/', '', $location_phone) : '';
                $location_map_link = $location_item['map_link'] ?? '';
                if ($location_map_link) :
                    $location_map_link_url = $location_map_link['url'];
                    $location_map_link_title = $location_map_link['title'];
                    $location_map_link_target = $location_map_link['target'] ? $location_map_link['target'] : '_self';
                endif;
            ?>

                        <div class="col wow animate__animated animate__fadeInUp">

                            <div>

                                <div class="border border-light shadow-lg bg-white cards-shape">

                                    <?php if ($location_image_url) : ?>
                                    <div class="p-5 mb-5">
                                        <img src="<?php echo esc_url($location_image_url); ?>" class="img-cards img-fluid" alt="<?php echo esc_attr($location_image_alt ?: $location_name); ?>">
                                    </div>
                                    <?php endif; ?>

                                    <div class="pb-5 px-5 px-md-5 <?php if (!$location_image_url) : echo 'pt-5'; endif; ?>">

                                        <div class="fosforos pb-4">
                                            <?php if ($location_name) : ?>
                                            <div class="mb-5">
                                                <h5><?php echo esc_html($location_name); ?></h5>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($location_address) : ?>
                                            <div class="text-cards-content mb-3">
                                                <a href="<?php echo esc_url($location_map_link_url); ?>" target="_blank" title="<?php echo esc_attr($location_map_link_title); ?>"><?php echo $location_address; ?></a>
                                            </div>
                                            <?php endif; ?>

                                            <?php if ($location_phone) : ?>
                                            <div>
                                                <span class="text-brand-main fw-bold me-2">P</span>
                                                <a href="tel:<?php echo esc_attr($location_phone_link); ?>"><?php echo esc_html($location_phone); ?></a>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <?php $iLocations++;
            endforeach;
            ?>

                    </div>

                    <?php
        else :
        echo 'Carpe diem';
        endif;
        ?>
                    <!-- //end Locations Repeater -->

                </div>


            </div>
        </div>
    </div>


</div>