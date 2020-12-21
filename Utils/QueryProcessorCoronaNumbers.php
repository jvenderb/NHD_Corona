<?php
declare(strict_types=1);

namespace Corona\Utils;

use DateTime;
use Exception;

class QueryProcessorCoronaNumbers
{
    const NUMBER_FILE = '/Users/Shared/Temp/CoronaNumbers.csv';
    /** @var DateTime $date */
    private DateTime $date;
    /** @var array $collectedData */
    private array $collectedData;

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

    private function getCommunities(): array
    {
        $communities['ALK'] = ['Alkmaar', 'Bergen (NH.)', 'Castricum', 'Heerhugowaard', 'Heiloo', 'Langedijk'];
        $communities['KOP'] = ['Den Helder', 'Hollands Kroon', 'Schagen', 'Texel'];
        $communities['WEF'] = ['Hoorn', 'Drechterland', 'Enkhuizen', 'Koggenland', 'Medemblik', 'Opmeer', 'Stede Broec'];
        return $communities;
    }

    private function isSameDay(DateTime $target): bool
    {
        $targetDate = $target->format('d-m-Y');
        $sourceDate = $this->date->format('d-m-Y');
        return $targetDate == $sourceDate;
    }

    private function requestedCommunity($community): bool
    {
        $communityGroups = $this->getCommunities();
        foreach ($communityGroups as $communityGroup) {
            if (in_array($community, $communityGroup)) {
                return true;
            }
        }
        return false;
    }
}