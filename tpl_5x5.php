<?php
/*
  Template Name: Medijpratiba 5x5
 */
get_header();
$prefix = 'mpc_';
$randomkaulins = mt_rand(1, 6);

?>

<main role="main" class="container mt-3 grid5x5_container">
    <!-- salūts -->
    <div class="no-pyro" id="saluts">
        <div class="before"></div>
        <div class="after"></div>
    </div>

    <div class="row">
        <div class="col-lg-12 <?= mpc_contentcss() ?>">
            <div class="row">

                <div class="col-4 pt-2">
                    <span id="dice" class="display-1 align-middle mest">&#x2685;</span>
                </div>
                <div class="col-4 text-center">
                    <p class="align-middle">
                        <span id="mest" class="d-none" data-canroll="yes"><?php _e("Roll", 'medijpratibalv'); ?></span>
                        <span id="uzmeta" min="1" max="6" class="d-none">1</span>
                        <span class="align-middle">
                            <span class="oi oi-grid-two-up display-4 align-middle"></span>
                            <span id="laukums" class="display-4 align-middle ml-3">0</span>

                        </span>

                    </p>

                </div>
                <div class="col-4 text-right">
                    <span class="oi oi-reload display-4 align-middle text-warning nojauna" id="nojauna"></span>
                </div>
            </div>


            <div class="row">
                <div class="col-12">

                    <div class="modal fade" id="empModal" role="dialog">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h4 class="modal-title"><?php _e('Question', 'medijpratibalv') ?></h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body border-0"></div>

                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-dismiss="modal"><?php _e("Close", 'medijpratibalv'); ?> & <?php _e("Continue", 'medijpratibalv'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid5x5 mb-4">
                <article id="post-001-starts" class="article-wrap grid5x5-single" itemscope itemtype="http://schema.org/CreativeWork" data-mpgridnr="0" data-plus="0">
                    <div class="grid5x5-box text">
                        <p class="entry-title h2 text-shadow1"><?php _e("Start", 'medijpratibalv'); ?></p>
                        <input type="button" value="<?php _e("Roll", 'medijpratibalv'); ?>" id="startmest" class="btn btn-sm btn-success mest" data-canroll="yes" />
                    </div>
                </article>

                <?php
                $core8_news_postsq = new WP_Query([
                    'post_type'      => apply_filters('mpq_mpquestions_posts_list', ['mpquestions',]),
                    'posts_per_page' => apply_filters('mpq_mpquestions_posts_per_page', 23),
                    'paged'          => get_query_var('paged'),
                    'order'          => 'ASC',
                    'meta_key'       => $prefix . 'nrpk',
                    'orderby'        => 'meta_value_num',
                    'no_found_rows'  => true, //useful when pagination is not needed
                ]);
                if ($core8_news_postsq->have_posts()) {

                    // Load posts loop.
                    while ($core8_news_postsq->have_posts()) {
                        $core8_news_postsq->the_post();
                        //get_template_part('template-parts/content/content', 'mpquestions-grid');

                        $thispostid = get_the_ID();
                        $mpq_data = get_post($thispostid);
                        $posttype   = get_post_type($thispostid);
                        $permalink = get_permalink($thispostid);
                        $title = get_the_title($thispostid);
                        $attach_data = [];
                        $field_attach_data  = [
                            'src' => false,
                            'width' => 0,
                            'height' => 0
                        ];
                        /**
                         * Post meta fields
                         */
                        $prefix = 'mpc_';
                        $nrpk = rwmb_meta($prefix . 'nrpk'); // field nr.
                        $solis = rwmb_meta($prefix . 'solis'); // step
                        
                        if (has_post_thumbnail($thispostid)) {
                            $attach_data = wp_get_attachment_image_src(get_post_thumbnail_id($thispostid), 'medium');
                        }
                        
                        if (!empty($attach_data)) {
                            $field_attach_data  = [
                                'src' => $attach_data[0],
                                'width' => $attach_data[1],
                                'height' => $attach_data[2]
                            ];
                        }
                        
                        ?>
                        
                        <article id="post-<?php the_ID(); ?>" <?php post_class('article-wrap grid5x5-single'); ?> 
                            itemscope itemtype="http://schema.org/CreativeWork" 
                            data-mpgridnr="<?= $nrpk ?>" 
                            data-plus="<?= $solis ?>" 
                            data-permalink="<?=$permalink ?>" 
                            data-bgimg="<?=$field_attach_data['src'] ?>">

                            <div class="grid5x5-box">
                                <?php echo  '<p class="entry-title h2 text-shadow1"><span href="' . $permalink . '" rel="bookmark" title="' . $title . '" >' . $nrpk . "</span></p>"; ?>
                                <div id="<?php echo $posttype; ?>-<?php echo $thispostid; ?>-content" <?php post_class(); ?>>
                                    <div class="entry-content" itemprop="text">
                                        <span 
                                            class="mpc_box-questionlink mpc_box-questionlink-<?= $nrpk ?> grid-mpquestion" 
                                            data-nrpk="<?= $nrpk ?>" 
                                            data-slug="<?= $mpq_data->post_name ?>" 
                                            data-title="<?= $mpq_data->post_title ?>" 
                                            data-postid="<?= $mpq_data->ID ?>" 
                                            data-toggle="mopal" 
                                            data-xactive="false" 
                                            data-target="#empModal"
                                        ><small class="btn btn-light d-none mpquestion_btn btn-lg"><!-- <?php _e('Question', 'medijpratibalv'); ?> --><span class="oi oi-question-mark"></span></small></span>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php
                        
                                }
                                // Previous/next page navigation.
                                // we do not need navigation here
                            } else {

                                // If no content, include the "No posts found" template.
                                get_template_part('template-parts/content/content', 'none-grid');
                            }
                                    ?>

                <article id="post-001-finish" class="article-wrap grid5x5-single" itemscope itemtype="http://schema.org/CreativeWork" data-mpgridnr="24" data-plus="0">
                    <div class="grid5x5-box">
                        <p class="entry-title h2 text-shadow1"><?php _e("Finish", 'medijpratibalv'); ?></p>
                        <small class="text-white nojauna text-shadow1" data-canroll="yes"><?php _e("Restart game", 'medijpratibalv'); ?></small>
                    </div>
                </article>
            </div>
        </div>
    </div>

</main>
<?php
get_footer();
