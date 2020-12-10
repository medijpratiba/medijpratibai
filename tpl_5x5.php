<?php
/*
  Template Name: Medijpratiba 5x5
 */
get_header();

/**
 * Our questions class
 */
$mpqquestions = new mpQuestions();
?>

<main role="main" class="container mt-3 grid5x5_container">

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

                    <div class="modal fade" id="mpqModal" role="dialog">
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
                <article id="post-001-starts" class="article-wrap grid5x5-single" itemscope itemtype="http://schema.org/CreativeWork" data-mpgridnr="0" data-plus="0" data-bgimg="<?= $mpqquestions->mpqdir.'assets/img/zhuo-cheng-you-dice-300.jpg' ?>">
                    <div class="grid5x5-box text">
                        <p class="entry-title h2 text-shadow1"><?php _e("Start", 'medijpratibalv'); ?></p>
                        <input type="button" value="<?php _e("Roll", 'medijpratibalv'); ?>" id="startmest" class="btn btn-sm btn-success mest" data-canroll="yes" />
                    </div>
                </article>

                <?php 
                $mpqquestions->startFields();
                ?>

                <article id="post-001-finish" class="article-wrap grid5x5-single last-mpqfield" itemscope itemtype="http://schema.org/CreativeWork" data-mpgridnr="24" data-plus="0" data-bgimg="<?= $mpqquestions->mpqdir.'assets/img/david-boca-sparks-300.jpg' ?>">
                    <div class="grid5x5-box last-mpqbox">
                        <p class="entry-title h2 text-shadow1"><?php _e("Finish", 'medijpratibalv'); ?></p>
                        <small class="text-white nojauna text-shadow1" data-canroll="yes"><?php _e("Restart game", 'medijpratibalv'); ?></small>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center"><a href="https://medijpratiba.lv" class="medijpratiba_link">medijpratiba.lv</a></div>
    </div>

</main>
<?php
get_footer();
