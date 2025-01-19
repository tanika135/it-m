<?php
require_once 'crest.php';
require_once 'app.php';

$taskId = (int) $_REQUEST['data']['FIELDS_AFTER']['ID'];

$app = new App($taskId);
$app->run();



