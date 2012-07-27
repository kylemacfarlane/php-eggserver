<?php
$dirs = array('../');

function find_dists($dirs) {
    $dists = array();
    foreach ($dirs as $dir) {
	$dists = array_merge($dists, _find_dists($dir));
    }
    usort($dists, 'cmp');
    return $dists;
}

function _find_dists($dir) {
    $dists = array();
    if ($dh = opendir($dir)) {
        $found_setup = false;
        $dirs = array();
        while (($file = readdir($dh)) !== false) {
            if ($file == 'setup.py') {
                $found_setup = true;
            }
            if ($file != '.' && $file != '..') {
                if (is_dir($dir.'/'.$file)) {
                    $dirs[] = $dir.'/'.$file;
                }
            }
        }
        closedir($dh);
        if (!$found_setup) {
            foreach ($dirs as $dir) {
                $dists = array_merge($dists, _find_dists($dir));
            }
        } else {
            if (is_dir($dir.'/dist')) {
                if ($dh = opendir($dir.'/dist')) {
                    while (($file = readdir($dh)) !== false) {
                        if (is_file($dir.'/dist/'.$file)) {
                            $dists[] = $dir.'/dist/'.$file;
                        }
                    }
                    closedir($dh);
                }
            }
        }
    }
    usort($dists, 'cmp');
    return $dists;
}

function cmp($a, $b) {
    $a = basename($a);
    $b = basename($b);
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

if (!isset($_GET['download'])) {
    echo '<html><head><title>MyPi</title></head><body>';
    foreach (find_dists($dirs) as $egg) {
        $egg = basename($egg);
        echo '<p><a href="download/'.urlencode($egg).'">'.htmlspecialchars($egg).'</a></p>';
    }
    echo '</body></html>';
} else {
    $download = $_GET['download'];
    foreach (find_dists($dirs) as $egg) {
        if (basename($egg) == $download) {
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($egg).'"');
            header('Content-Length: '.filesize($egg));
            readfile($egg);
            exit;
        }
    }
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    echo '<html><head><title>404 - Page Not Found</title></head><body>404 - Page Not Found</body></html>';
}