<?php

// Copy this file to config.php

// Build list adapter to use
// Currently available adapters:
// * FileBuildList   - get builds for a file (JSON, YAML, CSV, XML)
// * FolderBuildList - get builds from actual build directory structure
define('BUILD_LIST_ADAPTER', 'File');

// FileBuildList adapter options
define('BUILD_LIST_FILE', '/var/lib/cm/builds.yaml');

// FolderBuildList adapter options
define('DOWNLOAD_ROOT', '/var/www/cm');
define('DOWNLOAD_BASE_URL', 'http://cm.example.net/get');
