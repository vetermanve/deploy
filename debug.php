<?php

    if (isset($_GET['shoow'])) {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);    
    }

    // for correct logs
    function logz($data) {
        if (PRODUCTION) {
            return null;
        }
        echo '<pre style="padding-left: 200px;">';
        var_dump(func_get_args());
        echo '</pre>';
    };
