<?php

$app->bind('path.public', function() {
    return dirname(base_path()).'/'.PROJECT_DIR.'/public';
});