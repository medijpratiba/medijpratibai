<?php
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        if (have_posts()) {

            // Load posts loop.
            while (have_posts()) {
                the_post();
                $post_id = get_the_ID();
                $prefix = 'mpc_';
                $atbildes_y = rwmb_meta($prefix . 'atbildes_y');
                $atbildes_n = rwmb_meta($prefix . 'atbildes_n');
                $paskaidrojums = rwmb_meta($prefix . 'paskaidrojums');
                $solis = rwmb_meta($prefix . 'solis');
                $nrpk = rwmb_meta($prefix . 'nrpk');

                $atbildes = array_merge((array)$atbildes_y, $atbildes_n);

        ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php

                        if (is_singular()) :
                            the_title('<h1 class="entry-title">', '</h1>');
                        else :
                            the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>');
                        endif;
                        ?>
                    </header><!-- .entry-header -->
                    <div class="entry-content">
                        <?php
                        echo '<p>';
                        echo '#' . $nrpk . ' | ' . __("Step", "medijpratibalv") . ':' . $solis . "<br />\n";
                        echo __("Answers", "medijpratibalv") . "<ul>\n";
                        foreach ($atbildes as $atbilde) {
                            echo '<li>' . (($atbildes_y === $atbilde) ? '<strong>' . $atbilde . '</strong>' : $atbilde) . "</li>\n";
                        }
                        echo '</ul>';
                        echo '</p>';
                        echo $paskaidrojums;
                        echo '<hr />';
                        the_content(
                            sprintf(
                                wp_kses(
                                    /* translators: %s: Post title. Only visible to screen readers. */
                                    __('Continue reading %s', 'medijpratibalv'),
                                    array(
                                        'span' => array(
                                            'class' => array(),
                                        ),
                                    )
                                ),
                                get_the_title()
                            )
                        );

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . __('Pages:', 'medijpratibalv'),
                                'after'  => '</div>',
                            )
                        );
                        ?>

                    </div><!-- .entry-content -->
                </article><!-- #post-<?php the_ID(); ?> -->
            <?php
            }
        } else {
            ?>

            <section class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php _e('Nothing Found', 'medijpratibalv'); ?></h1>
                </header><!-- .page-header -->

                <div class="page-content">
                    <p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'medijpratibalv'); ?></p>
                </div><!-- .page-content -->
            </section><!-- .no-results -->
        <?php

        }
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php
get_footer();
