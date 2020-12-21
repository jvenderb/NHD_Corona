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

define('CORONANUMBERS_URL', 'https://data.rivm.nl/covid-19/COVID-19_aantallen_gemeente_cumulatief.csv');
$download = isset($_POST['download']);
$day = $_POST['day'] ?? '';
$process = $_POST['process'] ?? false;
$html = '';
if ($process) {
    $app = new Application\Application(CORONANUMBERS_URL, $download, $day);
    $app->processRequest($day);
    $messages = $app->getOutput();
    if ($messages) foreach ($messages as $message) {
        $html .= $message . '<br>';
    }

}
$formBuilder = new Utils\FormBuilder();
$form = $formBuilder->getInPutForm($html);
print $form;







 