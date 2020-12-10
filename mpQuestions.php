<?php

/** 
 * Plugin Name: Medijpratiba.lv jautājumi
 * Version: 1.1.4
 * Plugin URI: https://medijpratiba.lv/spele/
 * Description: Medijpratiba.lv spēles jautājumi
 * Text Domain: medijpratibalv
 * Domain Path: /languages
 * Author: Rolands Umbrovskis
 * Requires at least: 4.5
 * Tested up to: 5.5.3
 * Author URI: https://umbrovskis.com
 * License: GNU General Public License
 * 
 */

require_once __DIR__ . '/PageTemplater.php';

try {
    new mpQuestions();
} catch (\Throwable $e) {
    $mpquestions_debug = 'Caught throwable: mpQuestions ' . $e->getMessage() . "\n";

    if (apply_filters('mpquestions_debug_log', defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)) {
        error_log(print_r(compact('mpquestions_debug'), true));
    }
}
/**
 * May have some dependency on plugin itself in future
 */
require_once __DIR__ . '/helpers.php';
/**
 * medijpratiba.lv questions
 *
 * @author rolandinsh
 */
class mpQuestions
{

    var $vers = '1.1.4';
    var $versbuild; // build version 
    var $plugin_slug;
    var $label_singular;
    var $label_plural;
    var $plugin_td; // text domain

    var $mpqdir; // Plugin's directory
    var $cb_name; // Meta fields callback
    var $templates = []; // The array of templates that this plugin tracks.

    var $custom_template = 'tpl_medijpratibalv_5x5.php';

    var $game_questions = [];
    var $total_questions;
    var $transient_ttl;

    function __construct()
    {
        $this->plugin_td = 'medijpratibalv';
        $this->plugin_slug = 'mpquestions';

        $this->versbuild = $this->versionPatch();

        $this->label_plural = __('Questions', $this->plugin_td);
        $this->label_singular = __('Question', $this->plugin_td);

        $this->mpqdir = plugin_dir_url(__FILE__);

        $this->cb_name = $this->plugin_slug . '_cbq';

        add_action('plugins_loaded', [$this, 'pageTemplater']);
        add_action('plugins_loaded', [$this, 'loadTextdomain']);

        add_action('init', [&$this, 'mpquestionsPostTypes'], 15);

        add_filter('rwmb_meta_boxes',  [&$this, 'registerMb']);
        add_filter('single_template',  [&$this, 'loadSingleTemplate']);

        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles'], 15);

        add_filter('wp_resource_hints', [$this, 'resourceHints'], 20, 2);

        add_action('wp_ajax_mpq_action', [$this, 'mpcAjaxAction']);
        add_action('wp_ajax_nopriv_mpq_action', [$this, 'mpcAjaxAction']);

        add_action('wp_ajax_mpreset_action', [$this, 'resetAjaxQuestion']);
        add_action('wp_ajax_nopriv_mpreset_action', [$this, 'resetAjaxQuestion']);

        add_filter('manage_' . $this->plugin_slug . '_posts_columns', [$this, 'set_custom_edit_' . $this->plugin_slug . '_columns']);
        add_action('manage_' . $this->plugin_slug . '_posts_custom_column', [$this, 'custom_' . $this->plugin_slug . '_column'], 10, 2);

        $this->transient_ttl = 1 * HOUR_IN_SECONDS;
    }

    public function versionPatch()
    {

        /**
         * Version patch
         * Can filter
         */
        $patch_nr = date("yWz");
        if (defined(WP_DEBUG) && WP_DEBUG) {
            $patch_nr = date("yWz.His");
        }
        return apply_filters($this->plugin_slug . '_versbuild', $patch_nr);
    }
    function loadTextdomain()
    {
        load_plugin_textdomain('medijpratibalv', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }
    /**
     * Register post type mpquestions and related taxonomies
     */
    public function mpquestionsPostTypes()
    {
        register_post_type(
            $this->plugin_slug,
            [
                'label'           => __('Questions', $this->plugin_td),
                'description'     => 'Medijpratiba.lv spēles jautājumi',
                'public'          => true,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'rewrite'         => [
                    'slug'       => $this->plugin_slug,
                    'with_front' => false
                ],
                'query_var'       => true,
                'has_archive'     => true,
                'show_in_rest'    => true,
                'supports'        => [
                    'title',
                    'editor',
                    'excerpt',
                    'custom-fields',
                    'revisions',
                    'thumbnail',
                    'author',
                    'page-attributes',
                ],
                'taxonomies'      => ['post_tag', $this->plugin_slug . '_tag'],
                'labels'          => [
                    'name'               => __('Questions', $this->plugin_td),
                    'singular_name'      => __('Question', $this->plugin_td),
                    'menu_name'          => __('Questions', $this->plugin_td),
                    'add_new'            => __('Add new', $this->plugin_td),
                    'add_new_item'       => __('Add new Question', $this->plugin_td),
                    'edit'               => __('Edit', $this->plugin_td),
                    'edit_item'          => __('Edit Question', $this->plugin_td),
                    'new_item'           => __('New Question', $this->plugin_td),
                    'view'               => __('View', $this->plugin_td),
                    'view_item'          => __('View Question', $this->plugin_td),
                    'search_items'       => __('Search Questions', $this->plugin_td),
                    'not_found'          => __('Questions not found', $this->plugin_td),
                    'not_found_in_trash' => __('Questions not found in trash', $this->plugin_td),
                    'parent'             => __('Parent Questions', $this->plugin_td),
                ]
            ]
        );
        if (!taxonomy_exists($this->plugin_slug . '_tag')) {
            register_taxonomy(
                $this->plugin_slug . '_tag',
                [0 => $this->plugin_slug,],
                [
                    'hierarchical'   => true,
                    'label'          => __('Questions categories', $this->plugin_td),
                    'show_ui'        => true,
                    'query_var'      => true,
                    'show_in_rest'   => true,
                    'rewrite'        => ['slug' => 'mpquestions-tag', 'with_front' => false],
                    'singular_label' => __('Questions category', $this->plugin_td)
                ]
            );
        }

        register_taxonomy_for_object_type('post_tag', $this->plugin_slug);
        // placeholder for data
    }

    /**
     * Register all meta fields at once
     */
    function registerMb($meta_boxes)
    {
        $prefix = 'mpc_';

        $meta_boxes[] = [
            'title'      => esc_html__('Questions', $this->plugin_td),
            'id'         => 'mpc_questions',
            'post_types' => [$this->plugin_slug],
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => [
                [
                    'type' => 'range',
                    'id'   => $prefix . 'nrpk',
                    'name' => esc_html__('Nr', $this->plugin_td),
                    'desc' => esc_html__('1 ... 23', $this->plugin_td),
                    'std'  => 1,
                    'min'  => 1,
                    'max'  => 23,
                    'step' => 1,
                ],
                [
                    'type' => 'number',
                    'id'   => $prefix . 'solis',
                    'name' => esc_html__('Step', $this->plugin_td),
                    'desc' => esc_html__('0 ... 3', $this->plugin_td),
                    'min'  => 0,
                    'max'  => 3,
                    'step' => 1,
                ],
                [
                    'type' => 'text',
                    'id'   => $prefix . 'atbildes_y',
                    'name' => esc_html__('Answer', $this->plugin_td),
                    'desc' => "pareizā atbilde",
                ],
                [
                    'type'  => 'text',
                    'id'    => $prefix . 'atbildes_n',
                    'name'  => __('INCORRECT answers', $this->plugin_td),
                    'desc'  => "Atbil\xc5\xbeu varianti",
                    'clone' => true,
                ],
                [
                    'type' => 'wysiwyg',
                    'id'   => $prefix . 'paskaidrojums',
                    'name' => esc_html__('Explanation', $this->plugin_td),
                ],
            ],
        ];

        return $meta_boxes;
    }

    /**
     * Template example 
     */
    public function loadSingleTemplate($template)
    {
        global $post;

        $tpl_file_single = 'single-' . $this->plugin_slug . '.php';

        if ($this->plugin_slug === $post->post_type && locate_template([$tpl_file_single]) !== $template) {
            return plugin_dir_path(__FILE__) . $tpl_file_single;
        }

        return $template;
    }

    public function pageTemplater()
    {
        try {
            $page_tplr = new PageTemplater();
            return $page_tplr->get_instance();
        } catch (\Throwable $th) {
            throw $th;
            wp_die($th->getMessage());
        }
    }

    /**
     * Register javascript file(s)
     */
    public function enqueueScripts()
    {
        $rlvhv = $this->vers;
        $mpq_js = $this->mpqdir . 'assets/js/mpq.js';

        if (!is_admin()) {
            wp_enqueue_script('jquery');
            wp_register_script('bootstrap', $this->mpqdir . 'assets/js/bootstrap.bundle.min.js', ['jquery'], $rlvhv . '.' . $this->versbuild, true);
            wp_enqueue_script('bootstrap');
            wp_register_script('mpq', $mpq_js, ['jquery', 'bootstrap'], $this->vers . '.' . $this->versbuild, true);
            wp_enqueue_script('mpq');
            $this->mpq_inline_script();
        }
    }
    /**
     * dirty way to include missing "{mpq}ajaxurl" in some cases
     * some plugins 
     */
    public function mpq_inline_script()
    {
        $mpqscript_il  = 'var mpqajaxurl = "' . admin_url('admin-ajax.php') . '";';
        wp_add_inline_script('mpq', $mpqscript_il, 'before');
    }
    /**
     * Registed CSS style(s)
     */
    public function enqueueStyles()
    {

        $mpq_css = $this->mpqdir . 'assets/css/grid5x5.css';

        if (!is_admin()) {
            wp_register_style('bootstrap', $this->mpqdir . 'assets/css/bootstrap.min.css', [], '4.4.1', 'all');
            wp_enqueue_style('bootstrap');

            wp_register_style('open-iconic-bootstrap', $this->mpqdir . 'assets/css/open-iconic-bootstrap.css', ['bootstrap'], '1.1.1', 'all');
            wp_enqueue_style('open-iconic-bootstrap');

            wp_register_style('firework', $this->mpqdir . 'assets/css/firework.css', [], '1.1.1', 'all');
            wp_enqueue_style('firework');

            $dependon_css = apply_filters($this->plugin_slug . '_dependon_css', ['bootstrap', 'open-iconic-bootstrap', 'firework']);

            wp_register_style('grid5x5', $mpq_css, $dependon_css, $this->vers . '.' . $this->versbuild, 'all');
            wp_enqueue_style('grid5x5');
        }
    }

    /**
     * resource hints for fater loading
     */
    public function resourceHints($hints, $relation_type)
    {
        $rlvhv = $this->vers;

        $mpq_css = $this->mpqdir . 'assets/css/grid5x5.css';
        $openiconic_bootstrap_css = $this->mpqdir . 'assets/css/open-iconic-bootstrap.css';
        switch ($relation_type) {
            case 'prerender':
                $hints[] = $mpq_css;
                $hints[] = $openiconic_bootstrap_css;
                break;
            case 'prefetch':
                $hints[] = $mpq_css;
                $hints[] = $openiconic_bootstrap_css;
                break;
        }

        return $hints;
    }

    public function randomQueryOrder()
    {
        return array_rand(array_flip(['ASC', 'DESC']), 1);
    }

    public function getRandomQuestion($current = 0, $not_in = [])
    {

        $random_order = $this->randomQueryOrder();
        $questions_randomq = new WP_Query([
            'post_type'      => apply_filters('mpq_questions_randomq', [$this->plugin_slug]),
            'posts_per_page' => 1,
            'orderby'        => 'rand',
            'post__not_in'   => $not_in,
            'no_found_rows'  => true,
            'order'          => $random_order,
        ]);
        if ($questions_randomq->have_posts()) {
            while ($questions_randomq->have_posts()) {
                $questions_randomq->the_post();
                return get_the_ID();
            }
        } else {
            // returning asked question, ignoring NOT IN list
            return $current;
        }
    }

    public function getRandomStart($size = 23)
    {
        $random_start_order = $this->randomQueryOrder();
        $random_start_questions_q = new WP_Query([
            'post_type'      => apply_filters('mpq_mpquestions_posts_list', [$this->plugin_slug]),
            'posts_per_page' => (isset($size) && !empty($size)) ? (int)$size : 23,
            'orderby'        => 'rand',
            'order'          => $random_start_order,
            'no_found_rows'  => true,
            'paged'          => get_query_var('paged'),
        ]);

        return $random_start_questions_q;
    }

    /**
     * Start of the game fields
     */
    public function startFields()
    {
        $mpd_questions_query = $this->getRandomStart();

        $fieldnr = 0;
        if ($mpd_questions_query->have_posts()) {

            // Load posts loop.
            while ($mpd_questions_query->have_posts()) {
                $mpd_questions_query->the_post();
                ++$fieldnr;
                $thispostid = get_the_ID();
                $mpq_data = get_post($thispostid);
                $posttype   = get_post_type($thispostid);
                $permalink = get_permalink($thispostid);
                $title = get_the_title($thispostid);
                $attach_data = [];
                $field_attach_data  = [
                    'src'    => $this->mpqdir . 'assets/img/conor-samuel-circus-300.jpg',
                    'width'  => 300,
                    'height' => 300
                ];
                /**
                 * Post meta fields
                 */
                $prefix = 'mpc_';
                $nrpk = $this->get_metafield($prefix . 'nrpk', $thispostid); // field nr.
                $solis = $this->get_metafield($prefix . 'solis', $thispostid);  // step

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

                <article id="post-<?php the_ID(); ?>" <?php post_class('article-wrap grid5x5-single'); ?> itemscope itemtype="http://schema.org/CreativeWork" data-mpgridnr="<?= $fieldnr ?>" data-plus="<?= $solis ?>" data-permalink="<?= $permalink ?>" data-bgimg="<?= $field_attach_data['src'] ?>">

                    <div class="grid5x5-box">
                        <?php echo '<p class="entry-title h2 text-shadow1"><span href="' . $permalink . '" rel="bookmark" title="' . esc_attr($title) . '" >' . $fieldnr . "</span></p>"; ?>
                        <div id="<?php echo $posttype; ?>-<?php echo $thispostid; ?>-content" <?php post_class(); ?>>
                            <div class="entry-content" itemprop="text">
                                <span class="mpc_box-questionlink mpc_box-questionlink-<?= $nrpk ?> grid-mpquestion" data-nrpk="<?= $fieldnr ?>" data-slug="<?= $mpq_data->post_name ?>" data-postid="<?= $mpq_data->ID ?>" data-toggle="mopal" data-xactive="false" data-target="#mpqModal"><small class="btn btn-light d-none mpquestion_btn btn-lg"><span class="oi oi-question-mark"></span></small></span>
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
            ?>
            <article id="error-404" class="post-404 page type-page status-publish hentry article-wrap grid5x5-single" itemscope="" itemtype="http://schema.org/CreativeWork">
                <div class="grid5x5-box">
                    <div class="entry-content" itemprop="text">
                        <p>x</p>
                    </div>
                </div>
            </article>

<?php
        }
    }

    /**
     * AJAX calls to WordPress backend
     */
    public function mpcAjaxAction()
    {
        $this->total_questions = (int)wp_count_posts($this->plugin_slug)->publish;
        $game_questions_used = !empty(get_transient('game_questions_used')) ? get_transient('game_questions_used') : [];

        // Did we ask for data?
        if (!empty($_POST['postid'])) {
            $postid = intval($_POST['postid']);

            // we used all questions, repeat already answerd
            if ((count($game_questions_used) >= $this->total_questions)) {
                set_transient('game_questions_used', [], $this->transient_ttl);
            }

            // get random question if this was used
            if (in_array($postid, $game_questions_used)) {
                $postid = $this->getRandomQuestion($postid, $game_questions_used);
                $game_questions_used2 = array_merge($game_questions_used, [$postid]);
                set_transient('game_questions_used', $game_questions_used2, $this->transient_ttl);
            }
            // Add requested question to the "used" list
            if (($this->total_questions > count($game_questions_used))) {
                $game_questions_used3 = array_merge($game_questions_used, [$postid]);
                set_transient('game_questions_used', $game_questions_used3, $this->transient_ttl);
            }

            $mpq_data = get_post($postid);
            $question = get_the_title($postid);
            $postid = $mpq_data->ID;

            $prefix = 'mpc_';
            $atbildes_y = $this->get_metafield($prefix . 'atbildes_y', $postid);
            $atbildes_n = $this->get_metafield($prefix . 'atbildes_n', $postid);
            $paskaidrojums = $this->get_metafield($prefix . 'paskaidrojums', $postid);
            $solis = $this->get_metafield($prefix . 'solis', $postid);
            // in case it's empty
            $solis = (isset($solis) && !empty($solis)) ? $solis : 1;
            $nrpk = $this->get_metafield($prefix . 'nrpk', $postid);

            $atbildes = array_merge((array)$atbildes_y, $atbildes_n); // Merge all answers in one array
            shuffle($atbildes); // Otherwise the correct answer always is first. Now random.

            echo '<p><strong>' . $question . '</strong></p>';
            if (!empty(get_the_content(null, false, $postid))) {
                echo '<div class="question_content">' . get_the_content(null, false, $postid) . '</div>';
            }

            echo '<p>' . __("Answers", 'medijpratibalv') . ':</p>';

            echo '<div data-mpqanswers="' . $nrpk . '" class="mpq_answers">';
            echo '<script>solis=' . $solis . ';</script>';
            $atb = 0;
            $answ_icon = '<span class="oi oi-target"></span>';
            foreach ($atbildes as $atbilde) {
                ++$atb;
                $pareiza = (($atbildes_y === $atbilde) ? 1 : 0);
                echo '<p class="mpq_correct-' . $pareiza . ' mpq_answer rounded-lg " data-mpqcorrect="' . $pareiza . '">' . $answ_icon . ' ' . $atbilde . "</p>\n";
            }
            echo "\n</div>";
            echo '<div class="mpq_description d-none bg-white text-dark p-3 mb-2 rounded-lg">' . $paskaidrojums . '</div>';
        }

        wp_die();
    }

    public function resetAjaxQuestion()
    {
        set_transient('game_questions_used', [], $this->transient_ttl);
        wp_die();
    }

    public function set_custom_edit_mpquestions_columns($columns)
    {
        $columns['mpc_solis'] = __("Step", $this->plugin_td);
        $columns['mpc_iamge'] = __("Image", $this->plugin_td);
        return $columns;
    }

    public function custom_mpquestions_column($column, $post_id)
    {
        $prefix = 'mpc_';
        $solis = $this->get_metafield($prefix . 'solis', $post_id);
        $show_solis = empty($solis) ? 1 : (int)$solis;

        $attach_data = [];
        $field_attach_data  = [
            'src'    => $this->mpqdir . 'assets/img/conor-samuel-circus-300.jpg',
            'width'  => 300,
            'height' => 300
        ];
        $img_border = 0;
        if (has_post_thumbnail($post_id)) {
            $attach_data = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'thumbnail');
            $img_border = 3;
        }

        if (!empty($attach_data)) {
            $field_attach_data  = [
                'src' => $attach_data[0],
                'width' => $attach_data[1],
                'height' => $attach_data[2]
            ];
        }

        switch ($column) {
            case 'mpc_solis':
                echo $show_solis;
                break;
            case 'mpc_iamge':
                echo '<img src="' . $field_attach_data['src'] . '" height="80" width="auto" style="border-bottom:' . $img_border . 'px solid rgba(109, 36, 0, 0.9); padding-bottom:' . $img_border . 'px;" />';
                break;
        }
    }

    public function get_metafield($field = '', $post_id = 0, $args = [])
    {
        return rwmb_meta($field, $args, $post_id);
    }
}
