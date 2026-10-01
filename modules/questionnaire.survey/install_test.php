<?php

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/install/index.php';

try {
    $module = new questionnaire_survey();
    $module->DoInstall();

    echo "Module installed\n";
} catch (Throwable $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}