<?php
declare(strict_types=1);
namespace Corona\Utils;

use Corona\Application\Config;
use DateTime;
use Exception;

class QueryProcessorCoronaNumbers
{
    const NUMBER_FILE = '/Users/Shared/Temp/CoronaNumbers.csv';
    private DateTime $date;
    private array $collectedData;
    private Config $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    public function getEntriesForCommunities(DateTime $date): array
    {
        $this->collectedData = [];
        $this->date = $date;
        $handle = fopen(self::NUMBER_FILE, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->processLine($line);
            }
            if (!feof($handle)) {
                $this->output[] = "Error: unexpected fgets() fail.";
            }
            fclose($handle);
        }
        if( !$this->collectedData) {
            $this->collectedData[] = 'Niet beschikbaar;Niet beschikbaar;Niet beschikbaar;Niet beschikbaar';
        }
        return $this->collectedData;
    }

    private function processLine(string $line)
    {
        $data = explode(';', $line);
        try {
            $readDate = new DateTime($data[0]);
        } catch (Exception $e) {
            return;
        }
        if ($this->isSameDay($readDate) && $this->requestedCommunity($data[2])) {
            $this->collectedData[] = $data[2] . ';' . $data[4] . ';' . $data[5] . ';' . $data[6];
        }
    }

    private function isSameDay(DateTime $target): bool
    {
        $targetDate = $target->format('d-m-Y');
        $sourceDate = $this->date->format('d-m-Y');
        return $targetDate == $sourceDate;
    }

    private function requestedCommunity($community): bool
    {
        $communityGroups = $this->config->getCommunityGroups();
        foreach ($communityGroups as $communityGroup) {
            if (in_array($community, $communityGroup)) {
                return true;
            }
        }
        return false;
    }
}