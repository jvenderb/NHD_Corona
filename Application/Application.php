<?php
declare(strict_types=1);

namespace Corona\Application;
use Corona\Utils\HttpClient;
use Corona\Utils\QueryProcessorCoronaNumbers;
use Corona\Utils\TableGenerator;
use DateTime;

/**
 * Handles the request and generates all data.
 */
class Application
{
    const NUMBER_FILE = '/Users/Shared/Temp/CoronaNumbers.csv';
    /** @var $httpClient resource */
    private $httpClient;
    /** @var $output array */
    private array $output;
    /** @var $download bool */
    private bool $download;
    /** @var $date DateTime */
    private DateTime $date;
    /** @var $collectedData array */
    private array $collectedData;
    /** @var TableGenerator $tableGenerator */
    private TableGenerator $tableGenerator;
    /** @var QueryProcessorCoronaNumbers */
    private QueryProcessorCoronaNumbers $queryProcessor;
    /** @var Config */
    private Config $config;

    public function __construct(string $url, bool $download, string $day)
    {
        $this->httpClient = new HttpClient($url);
        $this->output = [];
        $this->download = $download;
        $this->date = new DateTime($day);
        $this->collectedData = [];
        $this->tableGenerator = new TableGenerator();
        $this->queryProcessor = new QueryProcessorCoronaNumbers();
        $this->config = new Config();
    }

    public function processRequest()
    {
        if (!$this->date) {
            $this->output[] = 'Geen geldige datum ingegeven.';
            return;
        }
        if ($this->download) {
            $result = $this->downloadFile();
            if (!$result) {
                return;
            }
        } else {
            $this->output[] = 'Geen nieuwe cijfers gedownload.';
        }
        $dataOfDate = $this->collectDataAndAddToTable($this->date);
        $dayBefore = (new DateTime($this->date->format('d-m-Y')))->modify('-1 day');
        $dataOfDayBefore = $this->collectDataAndAddToTable($dayBefore);
        $this->calculateDiff($dataOfDate, $dataOfDayBefore);
    }

    private function getTableHeadersData(): array
    {
        return ['Gemeente', 'Tot. Besmettingen', 'Ziekenhuis Opn.', 'Tot. overleden'];
    }

    private function getTableHeadersDiff(): array
    {
        return ['Gemeente', ' Stijging totaal besmettingen', 'Stijging opnames', 'Toename overlijden'];
    }

    private function calculateDiff(array $dataOfDate, array $dataOfDayBefore)
    {
        $newTotalOverAll = 0;
        $newHospitalOverAll = 0;
        $newDeathOverAll = 0;
        $changesPerCommunity = [];
        $this->output[] = 'De verschillen tussen ' . $this->date->format('d-m-Y') . ' en de vorige dag.';
        foreach ($dataOfDayBefore as $dayBefore) {
            $dayBeforeData = explode(';', $dayBefore);
            foreach ($dataOfDate as $currentDay) {
                $currentDayData = explode(';', $currentDay);
                if ($currentDayData[0] == $dayBeforeData[0]) {
                    $newTotal = intval($currentDayData[1]) - intval($dayBeforeData[1]);
                    $newHospital = intval($currentDayData[2]) - intval($dayBeforeData[2]);
                    $newDeath = intval($currentDayData[3]) - intval($dayBeforeData[3]);
                    $newTotalOverAll += $newTotal;
                    $newHospitalOverAll += $newHospital;
                    $newDeathOverAll += $newDeath;
                    $changesPerCommunity[] = $currentDayData[0] . ';' . strval($newTotal) . ';' . strval($newHospital) . ';' . strval($newDeath);
                }
            }
        }
        $table = $this->tableGenerator->generateTable($this->getTableHeadersDiff(), $changesPerCommunity);
        $this->output[] = $table;
        $this->output[] = 'Overall cijfers: stijging totaal besmettingen: ' . strval($newTotalOverAll) . ', stijging opnames: ' . strval($newHospitalOverAll) . ', toename overlijden: ' . strval($newDeathOverAll);
    }

    private function downloadFile(): bool
    {
        $this->httpClient->createDownloadFile(self::NUMBER_FILE);
        $downloaded = $this->httpClient->downloadAndWriteToFile();
        if ($downloaded) {
            $this->output[] = "Corana cijfers zijn opgehaald.";
        } else {
            $this->output[] = "Ophalen van de Corona cijfers is mislukt.";
        }
        return $downloaded;
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    private function collectDataAndAddToTable(DateTime $date): array
    {
        $this->output[] = 'Cijfers voor: ' . $date->format('d-m-Y');
        $dataOfDate = $this->queryProcessor->getEntriesForCommunities($date);
        $communitieGroups = $this->config->getCommunityGroups();
        foreach( $communitieGroups as $group => $communitieGroup ) {
            $dataOfGroup = [];
            if($dataOfDate) {
                foreach ($dataOfDate as $dataLine) {
                    $dataItems = explode(';', $dataLine);
                    if( in_array( $dataItems[0], $communitieGroup )) {
                        $dataOfGroup[] = $dataLine;
                    }
                }
            }
            $this->output[] = "Cijfers voor {$group}:";
            $this->output[] = $this->tableGenerator->generateTable($this->getTableHeadersData(), $dataOfGroup);
        }

        return $dataOfDate;
    }
}