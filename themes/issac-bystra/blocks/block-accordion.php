<?php 
/*
 █████   ██████  ██████  ██████  ██████  ██████  ██  ██████  ███    ██
██   ██ ██      ██      ██    ██ ██   ██ ██   ██ ██ ██    ██ ████   ██
███████ ██      ██      ██    ██ ██████  ██   ██ ██ ██    ██ ██ ██  ██
██   ██ ██      ██      ██    ██ ██   ██ ██   ██ ██ ██    ██ ██  ██ ██
██   ██  ██████  ██████  ██████  ██   ██ ██████  ██  ██████  ██   ████


*/
$accordion_anchor = get_sub_field('accordion_anchor');
$accordion_headline = get_sub_field('accordion_headline');
$accordion_content = get_sub_field('accordion_content');
?>
<div id="<?php echo $accordion_anchor; ?>">
    <div id="accordionBlock-<?php echo $iBlock; ?>" class="container-fluid pb-5">



        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-11 col-lg-10">





                    <div class="container block-accordion__wrapper">

                        <div class="row  d-flex justify-content-center">


                            <div class="col-12 <?php if (!(get_post_type() == 'projects')) { echo 'col-xl-10'; } ?>">

                                <?php if($accordion_headline): ?>
                                <h2 class="my-5"><?php echo $accordion_headline; ?></h2>
                                <?php endif; ?>

                                <?php if($accordion_content): ?>
                                <div class="mb-5">
                                    <?php echo $accordion_content; ?>
                                </div>
                                <?php endif; ?>


                                <!-- Accordion Repeater -->

                                <?php
                                // check if the repeater field has rows of data
                                if( have_rows('accordion_repeater') ): $iAccordion = 0; // Set the increment variable
                                ?>

                                <div class="accordion pt-5" id="accordionMain-<?php echo $iBlock; ?>">

                                    <?php // loop through the rows of data for the tab header
                                    // Initialize $collapse before using it
                                    $collapse = 0;
                                    while ( have_rows('accordion_repeater') ) : the_row(); $collapse++;
                                    $accordion_headline = get_sub_field('accordion_headline');
                                    $accordion_body = get_sub_field('accordion_body');
                                    $accordion_image = get_sub_field('accordion_image');
                                    if($accordion_image):
                                        $accordion_image_url = $accordion_image['sizes']['576sm'];
                                    endif;
                                    $accordion_resource_repeater = get_sub_field('accordion_resource_repeater');
                                    ?>

                                    <div class="accordion-item mb-4 border-0">

                                        <div class="accordion-header" id="heading-<?php echo $iBlock; ?>-<?php echo $collapse; ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $iBlock; ?>-<?php echo $collapse; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $iBlock; ?>-<?php echo $collapse; ?>">
                                                <div class="accordion-title text-left  py-1 px-4">
                                                    <?php echo $accordion_headline; ?>
                                                    <i class="plusMinus" aria-hidden="true"></i>
                                                </div>
                                            </button>
                                        </div>

                                        <div id="collapse-<?php echo $iBlock; ?>-<?php echo $collapse;?>" class="accordion-collapse collapse text-tinta py-5 p-5 py-3" data-bs-parent="#accordionMain-<?php echo $iBlock; ?>">

                                            <div class="row text-start">


                                                <?php if( $accordion_image ): ?>
                                                <div class="col-12 col-md-4 mb-5">
                                                    <img src="<?php echo $accordion_image_url; ?>" alt="<?php echo $accordion_image['alt']; ?>" class="img-fluid" />
                                                </div>
                                                <?php endif ?>



                                                <div class="contentBox <?php if( $accordion_image ):  echo'col-12 col-md-8';  else: echo'col-12'; endif; ?> prose">
                                                    <?php echo $accordion_body; ?>
                                                </div>


                                                <!-- Resources -->
                                                <?php if($accordion_resource_repeater): ?>
                                                <div class="col-12 mt-5">
                                                    <?php display_resources('accordion_resource_repeater'); ?>
                                                </div>
                                                <?php endif; ?>


                                            </div><!-- end accordion body -->

                                        </div><!-- end collapse -->

                                    </div><!-- end item -->


                                    <?php $iAccordion++;  // Increment the increment variable

                                    endwhile;//End the loop
                                    ?>

                                </div><!-- end accordion -->



                                <?php
                                else :
                                // no rows found
                                endif;
                                ?>

                                <!-- //end Accordion Repeater -->
                            </div>


                        </div>
                    </div>





                </div>
            </div>
        </div>



    </div>
</div>