<?php
declare(strict_types=1);
namespace Corona\Application;


class Config
{
    public function getCommunityGroups(): array
    {
        $communities['ALK'] = ['Alkmaar', 'Bergen (NH.)', 'Castricum', 'Heerhugowaard', 'Heiloo', 'Langedijk'];
        $communities['KOP'] = ['Den Helder', 'Hollands Kroon', 'Schagen', 'Texel'];
        $communities['WEF'] = [ 'Drechterland', 'Enkhuizen', 'Hoorn', 'Koggenland', 'Medemblik', 'Opmeer', 'Stede Broec'];
        return $communities;
    }

    public function getDownloadUrl():string
    {
        return 'https://data.rivm.nl/covid-19/COVID-19_aantallen_gemeente_cumulatief.csv';
    }

    public function getDownloadFile():string
    {
        return '/Users/Shared/Temp/CoronaNumbers.csv';
    }
}