<?php

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = 'config')
    {
        return app()->basePath().($path ? '/'.$path : $path);
    }
}
