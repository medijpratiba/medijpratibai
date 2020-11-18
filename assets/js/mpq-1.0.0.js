;
/**
 * Version: 1.0.7
 */
var laukums = 0; // init START
const circusfield_min = 0; // START
const circusfield_max = 23; // active fields without START and FINISH
const circusfield_last = 24; // we start with zero
var laukums_old = 0;
var randomkaulins = 1; // Default/start value
const class2ad = 'border-dark rounded-0 bg-info active shadow-sm';

const salutailgums = 10 * 1000; // 10 sec

jQuery(document).ready(function () {
    /**
     * roll the dice
     */
    jQuery('.mest').click(function () {
        if (jQuery('.mest').data('canroll') === 'no') {
            return
        }
        randomkaulins = Math.floor(Math.random() * 6) + 1 || 1
        laukums_old = laukums
        laukums = laukums + randomkaulins
        rollTheDice(randomkaulins)

        /**
         * if more than {circusfield_max} (25-1).. we are done
         */
        if (laukums > circusfield_max) {
            laukums = circusfield_last
        }

        jQuery('#uzmeta').val(randomkaulins)
        jQuery('#laukums').html(laukums)

        jQuery(
            '.grid5x5-single[data-mpgridnr = ' +
            Math.floor(laukums - randomkaulins) +
            ']',
        ).removeClass(class2ad)

        jQuery(
            '.grid5x5-single[data-mpgridnr = ' +
            Math.floor(laukums - randomkaulins) +
            '] .grid-mpquestion .mpquestion_btn',
        ).addClass('d-none')

        if (laukums > circusfield_max) {
            jQuery(
                '.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']',
            ).removeClass(class2ad)

            jQuery(
                '.grid5x5-single[data-mpgridnr = ' +
                Math.floor(laukums_old) +
                '] .grid-mpquestion .mpquestion_btn',
            ).addClass('d-none')
        }
        jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']')
            .addClass(class2ad)
            .css({
                'background-image':
                    'url(' +
                    jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']').data(
                        'bgimg',
                    ) +
                    ')',
            })
        jQuery(
            '.grid5x5-single[data-mpgridnr = ' +
            laukums +
            '] .grid-mpquestion .mpquestion_btn',
        ).removeClass('d-none')

        jQuery('.mest').data('canroll', 'no')

        if (laukums == circusfield_last) {
            jQuery('#saluts').addClass('pyro')

            /**
             * Salut!
             */
            setTimeout(() => {
                jQuery('#saluts').removeClass('pyro')
            }, salutailgums)
        }
    })
    /**
     * inspired by https://jsfiddle.net/estelle/6d5Z6/
     * @param {*} randomkaulins
     */
    var rollTheDice = function (randomkaulins = 1) {
        var diceValue = 1,
            output = ''
        diceValue = randomkaulins - 1
        output += '&#x268' + diceValue + '; '
        document.getElementById('dice').innerHTML = output
    }
    /**
     * reset moves
     */
    jQuery('.nojauna').click(function () {
        laukums_old = laukums
        laukums = laukums + randomkaulins
        jQuery('.mest').data('canroll', 'yes')
        jQuery('#laukums').html(0)
        /**
         * if more than {circusfield_max} (25).. we are done
         */
        if (laukums > circusfield_max) {
            laukums = circusfield_last
        }

        jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']')
            .removeClass(class2ad)
            .css({ 'background-image': '' })

        jQuery(
            '.grid5x5-single[data-mpgridnr = ' +
            Math.floor(laukums_old) +
            '] .grid-mpquestion .mpquestion_btn',
        ).addClass('d-none')
        jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']')
            .removeClass(class2ad)
            .css({ 'background-image': '' })

        jQuery(
            '.grid5x5-single[data-mpgridnr = ' +
            laukums +
            '] .grid-mpquestion .mpquestion_btn',
        ).addClass('d-none')

        randomkaulins = 1
        laukums_old = 0
        laukums = 0
    })

    jQuery('.grid-mpquestion').click(function () {
        var postid = jQuery(this).data('postid')
        var nrpk = jQuery(this).data('nrpk')

        /**
         * check the current firld
         */
        var iscurrentnr = nrpk == laukums
        if (iscurrentnr == false) {
            // not the same = do not open on click
            return
        }

        var data = {
            action: 'mpq_action',
            postid: postid,
        }
        jQuery.post(ajaxurl, data, function (response) {
            jQuery('.modal-body').html(response)

            // Display Modal
            jQuery('#empModal').modal('show')
            jQuery('#empModal').data('postid', postid)
            jQuery('.mpq_answer').click(function () {
                var mpqcorrect = jQuery(this).data('mpqcorrect')
                jQuery('.mpq_description').removeClass('d-none')

                laukums_old = laukums
                if (mpqcorrect === 1) {
                    jQuery(this).addClass('bg-success p-2')
                    if (jQuery('#empModal').data('postid') === postid) {
                        laukums = laukums + solis
                    }
                }
                if (mpqcorrect === 0) {
                    jQuery(this).addClass('bg-danger p-2')
                    if (jQuery('#empModal').data('postid') === postid) {
                        var laukuma_solis = laukums - solis
                        if (laukuma_solis < circusfield_min) {
                            laukums = circusfield_min
                            jQuery('.mest').data('canroll', 'yes')
                        } else {
                            laukums = laukuma_solis
                        }
                    }
                }

                jQuery(this)
                    .closest('#empModal')
                    .on('hide.bs.modal', function () {
                        jQuery('#laukums').html(laukums)
                        if (laukums > circusfield_max) {
                            jQuery(
                                '.grid5x5-single[data-mpgridnr = ' +
                                Math.floor(laukums_old) +
                                ']',
                            )
                                .removeClass(class2ad)
                                .css({ 'background-image': '' })

                            jQuery(
                                '.grid5x5-single[data-mpgridnr = ' +
                                Math.floor(laukums_old) +
                                '] .grid-mpquestion .mpquestion_btn',
                            ).addClass('d-none')
                            laukums = circusfield_last
                        }
                        jQuery(
                            '.grid5x5-single[data-mpgridnr = ' +
                            Math.floor(laukums_old) +
                            ']',
                        )
                            .removeClass(class2ad)
                            .css({ 'background-image': '' })

                        jQuery(
                            '.grid5x5-single[data-mpgridnr = ' +
                            Math.floor(laukums_old) +
                            '] .grid-mpquestion .mpquestion_btn',
                        ).addClass('d-none')
                        if (laukums < circusfield_min) {
                            laukums = circusfield_min
                            jQuery('.mest').data('canroll', 'yes')
                        }
                        jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']')
                            .addClass(class2ad)
                            .css({
                                'background-image':
                                    'url(' +
                                    jQuery(
                                        '.grid5x5-single[data-mpgridnr = ' + laukums + ']',
                                    ).data('bgimg') +
                                    ')',
                            })
                        jQuery(
                            '.grid5x5-single[data-mpgridnr = ' +
                            laukums +
                            '] .grid-mpquestion .mpquestion_btn',
                        ).removeClass('d-none')

                        solis = 0
                        laukums_old = laukums
                    })

                jQuery('#empModal').removeData('postid')

                if (laukums == circusfield_min || laukums == circusfield_last) {
                    jQuery('.mest').data('canroll', 'yes')
                }
                jQuery('.mpq_answer').off('click')

                // if we are on STARTS field
                if (jQuery('#post-001-starts').hasClass('active')) {
                    jQuery('.mest').data('canroll', 'yes')
                }

                if (laukums == circusfield_last) {
                    jQuery('#saluts').addClass('pyro')

                    /**
                     * Salut!
                     */
                    setTimeout(() => {
                        jQuery('#saluts').removeClass('pyro')
                    }, salutailgums)
                }
            })
        })
    })
});
