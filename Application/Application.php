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
    private array $output;
    private bool $download;
    private DateTime $date;
    private array $collectedData;
    private bool $dayBefore;
    private TableGenerator $tableGenerator;
    private QueryProcessorCoronaNumbers $queryProcessor;
    private Config $config;

    public function __construct(string $day, bool $download, bool $dayBefore )
    {
        $this->output = [];
        $this->download = $download;
        $this->date = new DateTime($day);
        $this->dayBefore = $dayBefore;
        $this->collectedData = [];
        $this->tableGenerator = new TableGenerator();
        $this->queryProcessor = new QueryProcessorCoronaNumbers();
        $this->config = new Config();
    }

    public function processRequest()
    {
        $this->output[] = '<br>';
        if( !$this->validInput() ) {
            $this->output[] = 'Geen geldige datum ingegeven.';
            return;
        }
        if ($this->download) {
            $result = $this->downloadFile();
            if (!$result) {
                $this->output[] = "Ophalen van de Corona cijfers is mislukt.";
                return;
            }
            $this->output[] = "Corana cijfers zijn opgehaald.";
        } else {
            $this->output[] = 'Geen nieuwe cijfers gedownload.';
        }
        $this->output[] = '<br>';
        $dataOfDate = $this->collectDataAndAddToTable($this->date);
        if( $this->dayBefore ) {
            $dayBefore = (new DateTime($this->date->format('d-m-Y')))->modify('-1 day');
            $dataOfDayBefore = $this->collectDataAndAddToTable($dayBefore);
            $this->output[] = '<em>De verschillen tussen ' . $this->date->format('d-m-Y') . ' en de vorige dag ('.$dayBefore->format('d-m-Y').')</em>';
            $this->calculateDiff($dataOfDate, $dataOfDayBefore);
        }
    }

    public function validInput(): bool
    {
        if (!$this->date) {
            return false;
        }
        return true;
    }

    private function getTableHeadersData(): array
    {
        return ['Gemeente', 'Besmettingen', 'Ziekenhuis Opn.', 'Cum. overleden'];
    }

    private function getTableHeadersDiff(): array
    {
        return ['Gemeente', ' Besmettingen', 'Opnames', 'Overlijden'];
    }

    private function calculateDiff(array $dataOfDate, array $dataOfDayBefore)
    {
        $changesPerCommunity = [];
        foreach ($dataOfDayBefore as $dayBefore) {
            $dayBeforeData = explode(';', $dayBefore);
            foreach ($dataOfDate as $currentDay) {
                $currentDayData = explode(';', $currentDay);
                if ($currentDayData[0] == $dayBeforeData[0]) {
                    $newTotal = intval($currentDayData[1]) - intval($dayBeforeData[1]);
                    $newHospital = intval($currentDayData[2]) - intval($dayBeforeData[2]);
                    $newDeath = intval($currentDayData[3]) - intval($dayBeforeData[3]);
                    $changesPerCommunity[$currentDayData[0]] = $currentDayData[0] . ';' . strval($newTotal) . ';' . strval($newHospital) . ';' . strval($newDeath);
                }
            }
        }
        sort($changesPerCommunity);
        $changesPerCommunity = $this->calculateTotals($changesPerCommunity);
        $this->output[] = $this->tableGenerator->generateTable($this->getTableHeadersDiff(), $changesPerCommunity);
    }

    private function downloadFile(): bool
    {
        $httpClient = new HttpClient($this->config->getDownloadUrl());
        $httpClient->createDownloadFile($this->config->getDownloadFile());
        return $httpClient->downloadAndWriteToFile();
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    private function collectDataAndAddToTable(DateTime $date): array
    {
        $this->output[] = 'Cijfers voor:<em> ' . $date->format('d-m-Y') . '</em><br>';
        $dataOfDate = $this->queryProcessor->getEntriesForCommunities($date);
        $communitieGroups = $this->config->getCommunityGroups();
        foreach( $communitieGroups as $group => $communitieGroup ) {
            $dataOfGroup = [];
            if($dataOfDate) {
                foreach ($dataOfDate as $dataLine) {
                    $dataItems = explode(';', $dataLine);
                    if( in_array( $dataItems[0], $communitieGroup )) {
                        $dataOfGroup[$dataItems[0]] = $dataLine;
                    }
                }
            }
            $this->output[] = "<em>Regio {$group}:</em>";
            sort($dataOfGroup);
            $dataOfGroup = $this->calculateTotals($dataOfGroup);
            $this->output[] = $this->tableGenerator->generateTable($this->getTableHeadersData(), $dataOfGroup);
        }
        return $dataOfDate;
    }

    private function calculateTotals( array $data ):array
    {
        $total1 = 0;
        $total2 = 0;
        $total3 = 0;
        foreach( $data as $dataLine) {
            $dataItems = explode(';', $dataLine);
            $total1 += intval($dataItems[1]);
            $total2 += intval($dataItems[2]);
            $total3 += intval($dataItems[3]);
        }
        $data[] = "Totaal;{$total1};{$total2};{$total3}";
        return $data;
    }
}