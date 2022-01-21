<?php
declare(strict_types=1);
namespace Corona\Utils;

/**
 * Can be used to download / upload content from the internet.
 */

/**
 * Class HttpClient
 * @package Corona\Utils
 * Responsibility: Download / upload content from the internet.
 */
class HttpClient
{
    /** @var resource */
    private $curlResource;

    public function __construct(string $url)
    {
        $this->curlResource = curl_init($url);
    }

    public function downloadAndWriteToFile(string $fileName): bool
    {
        $result = true;
        $filePointer = fopen($fileName, "w");
        curl_setopt($this->curlResource, CURLOPT_FILE, $filePointer);
        curl_setopt($this->curlResource, CURLOPT_HEADER, 0);

        curl_exec($this->curlResource);
        if (curl_error($this->curlResource)) {
            fwrite($filePointer, curl_error($this->curlResource));
            $result = false;
        }
        curl_close($this->curlResource);
        fclose($filePointer);
        return $result;
    }
}