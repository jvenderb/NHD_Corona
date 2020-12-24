<?php
declare(strict_types=1);

namespace Corona;

use Corona\Utils\FormBuilder;
use DateTime;
use Exception;

require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Utils/HttpClient.php';
require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Utils/FormBuilder.php';
require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Utils/QueryProcessorCoronaNumbers.php';
require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Utils/TableGenerator.php';
require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Application/Application.php';
require_once '/Users/jvenderb/phpStormProjects/NHD_Corona/Application/Config.php';

$download = isset($_POST['download']);
$day = $_POST['day'] ?? '';
$process = $_POST['process'] ?? false;
$dayBefore = isset($_POST['daybefore']);
$html = '';
if ($process) {
    $app = new Application\Application($day, $download, $dayBefore);
    $app->processRequest($day);
    $messages = $app->getOutput();
    if ($messages) foreach ($messages as $message) {
        $html .= $message . '<br>';
    }

}
$formBuilder = new Utils\FormBuilder();
$form = $formBuilder->getInPutForm($html);
print $form;







 