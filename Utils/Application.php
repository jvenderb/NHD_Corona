<?php
declare(strict_types=1);

namespace Corona\Utils;

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

    public function __construct(string $url, bool $download, string $day)
    {
        $this->httpClient = new HttpClient($url);
        $this->output = [];
        $this->download = $download;
        $this->date = new DateTime($day);
        $this->collectedData = [];
        $this->tableGenerator = new namespace\TableGenerator();
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
        $this->output[] = 'Ophalen cijfers voor: ' . $this->date->format('d-m-Y');
        $queryProcessor = new QueryProcessorCoronaNumbers();
        $dataOfDate = $queryProcessor->getEntriesForCommunities($this->date);
        if (!$dataOfDate) {
            $this->output[] = 'Geen cijfers beschikbaar voor: ' . $this->date->format('d-m-Y');
            return;
        }
        $table = $this->tableGenerator->generateTable($this->getTableHeadersData(), $dataOfDate);
        $this->output[] = $table;
        $dayBefore = new DateTime($this->date->format('d-m-Y'));
        $dayBefore->modify('-1 day');
        $this->output[] = 'Ophalen cijfers voor: ' . $dayBefore->format('d-m-Y');
        $dataOfDayBefore = $queryProcessor->getEntriesForCommunities($dayBefore);
        $table = $this->tableGenerator->generateTable($this->getTableHeadersData(), $dataOfDayBefore);
        $this->output[] = $table;
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
        $this->output[] = 'Overall cijfers Stijging totaal besmettingen: ' . strval($newTotalOverAll) . ' Stijging opnames: ' . strval($newHospitalOverAll) . ' Toename overlijden: ' . strval($newDeathOverAll);
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
}