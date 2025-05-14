<?php

if (!function_exists('get_template_url')) {
    /**
     * Get the template URL
     *
     * @param string $path
     * @return string
     */
    function get_template_url($path)
    {
        return asset('templates/' . $path);
    }
}
