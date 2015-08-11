<?php
/**
 * loadshell.php
 *
 * This file is supposed to be loaded via php -a,
 * this way:
 *
 * > php -a
 * > require 'misc/loadshell.php';
 *
 * You then have a working console to test
 * some code with the models defined in the
 * app.
 *
 * There is also misc/appshell.sh script
 * which is faster to run, it does the same
 * thing as mentionned previously.
 *
 * @author Nelson Zamith <nzamith@teclib.com>
 */

require __DIR__ . '/../api/vendor/autoload.php';

use \Illuminate\Database\Capsule\Manager as DB;
use \API\Core\Tool;

use \API\Model\Author as Author;
use \API\Model\Message;
use \API\Model\Plugin;
use \API\Model\PluginDescription;
use \API\Model\PluginDownload;
use \API\Model\PluginScreenshot;
use \API\Model\PluginStar;
use \API\Model\PluginVersion;
use \API\Model\Tag;

// Note : those 'use' statements are not useful
// at all, and in the shell, Models needs to
// be referenced by the full namespace location :
//  \API\Model\Plugin

// These are just in the file to serve as a memo

\API\Core\DB::initCapsule();