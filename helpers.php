<?php

/**
 * content extra classes
 */
if (!function_exists('mpc_contentcss')) {

    function mpc_contentcss($more_extra = [])
    {
        $all_css = array_merge(["h100m"], $more_extra);
        /**
         * prefix with extra space
         */
        $content_css = " " . implode(" ", $all_css);
        $contentwrap_css = apply_filters('mpc_contentcss', $content_css);
        return $contentwrap_css;
    }
}
