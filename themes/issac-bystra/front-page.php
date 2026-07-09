<?php get_header(); ?>
<main id="main-content" role="main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>


    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>





        <?php
        $hero_stats = [];
        $item_count = 0;

        if (class_exists(\Issac\Domain\InstrumentRepository::class)) {
            $tree = \Issac\Domain\InstrumentRepository::tree();
            $subsection_count = 0;

            foreach ($tree as $domain) {
                $subsection_count += count($domain->subsections);
                foreach ($domain->subsections as $subsection) {
                    foreach ($subsection->items as $item) {
                        if ($item->isActive) {
                            $item_count++;
                        }
                    }
                }
            }

            $hero_stats = [
                ['value' => (string) count($tree), 'label' => __('Domains', 'bystra')],
                ['value' => (string) $subsection_count, 'label' => __('Subsections', 'bystra')],
                ['value' => (string) $item_count, 'label' => __('Reflection items', 'bystra')],
                ['value' => '1–5', 'label' => __('Maturity scale', 'bystra')],
            ];
        }

        $how_it_works_steps = [
            [
                'title' => __('Reflection, not a race', 'bystra'),
                'text'  => __('No streaks, no scores to beat. Progress is shown quietly so your team can take the time each item deserves.', 'bystra'),
            ],
            [
                'title' => __('Always know where you are', 'bystra'),
                'text'  => __('Progress rings, domain bars and a fixed header show how far you\'ve come and what\'s still ahead, on every screen.', 'bystra'),
            ],
            [
                'title' => __('Your work saves itself', 'bystra'),
                'text'  => __('There\'s no Save button. Each response is recorded the moment you make it, and a clear indicator confirms it — so you can close the tab mid-domain and return whenever suits.', 'bystra'),
            ],
            [
                'title' => __('Nothing hidden', 'bystra'),
                'text'  => $item_count > 0
                    ? sprintf(
                        __('All %d items stay open and visible. Subsection headings break the list into manageable stretches, so you can move through it at your own pace or jump straight to a domain.', 'bystra'),
                        $item_count
                    )
                    : __('All items stay open and visible. Subsection headings break the list into manageable stretches, so you can move through it at your own pace or jump straight to a domain.', 'bystra'),
            ],
        ];
        ?>

        <section class="issac-front-hero container-fluid px-5 px-md-0 card-rounded-top corner-fill bg-brand-accent">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <p class="issac-front-hero__tag"><?php esc_html_e('Inclusive Schools · Self-Assessment Checklist', 'bystra'); ?></p>
                        <h1 class="issac-front-hero__title headline-1">ISSAC — reflect together on inclusive practice</h1>

                        <p class="issac-front-hero__lead">A self-assessment tool for Australian school and site leaders: reflect on and collectively evaluate your current practices in inclusive education, and identify the priorities — including professional learning — that matter most for your community.<br />
                            Work through it in one sitting or across shorter sessions, alone or with your team. Your responses are saved as you go.</p>

                        <?php if ($hero_stats !== []) : ?>
                        <div class="issac-front-hero__stats" aria-label="<?php esc_attr_e('Instrument overview', 'bystra'); ?>">
                            <?php foreach ($hero_stats as $stat) : ?>
                            <div class="issac-front-hero__stat">
                                <span class="issac-front-hero__stat-value"><?php echo esc_html($stat['value']); ?></span>
                                <span class="issac-front-hero__stat-label"><?php echo esc_html($stat['label']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>




        <section class="issac-intro container-fluid px-5 px-md-0">
            <div class="container">
                <div class="row g-5 align-items-start">
                    <div class="col-lg-7">
                        <div class="issac-intro__content">
                            <h2 class="issac-intro__title headline-2"><?php esc_html_e('Inclusive Schools Self-Assessment Checklist (ISSAC)', 'bystra'); ?></h2>
                            <p><?php esc_html_e('The purpose of the Inclusive Schools Self-Assessment Checklist (ISSAC) is to encourage Australian school/site leaders to (1) reflect on and collectively self-evaluate current practices related to inclusive education, and (2) to identify priorities for future focus, including professional learning.', 'bystra'); ?></p>
                            <p><?php esc_html_e('Whilst inclusive education is a broad term, the ISSAC is particularly relevant to the education of academically diverse students—including those with disability, those at risk of or already experiencing academic and/or behavioural difficulties, and those with advanced academic abilities—as part of an overall, inclusive approach. It is therefore not intended to address every aspect of inclusive practice and could be used with complementary instruments for a more holistic assessment.', 'bystra'); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <aside class="issac-intro__artifact" aria-label="<?php esc_attr_e('How the tool works', 'bystra'); ?>">
                            <h2 class="issac-intro__artifact-heading"><?php esc_html_e('How the tool works', 'bystra'); ?></h2>
                            <ol class="issac-intro__steps">
                                <?php foreach ($how_it_works_steps as $index => $step) : ?>
                                    <li class="issac-intro__step">
                                        <span class="issac-intro__step-num issac-intro__step-num--<?php echo esc_attr((string) ($index + 1)); ?>" aria-hidden="true"><?php echo esc_html((string) ($index + 1)); ?></span>
                                        <div class="issac-intro__step-body">
                                            <strong class="issac-intro__step-title"><?php echo esc_html($step['title']); ?></strong>
                                            <span class="issac-intro__step-text"><?php echo esc_html($step['text']); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </aside>
                    </div>
                </div>
            </div>
        </section>






        <?php 
    /* Flexible Content */
    /* include get_theme_file_path('/blocks/flexible-content.php');  */
    ?>

    </article>


    <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>