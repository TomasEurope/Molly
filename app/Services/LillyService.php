<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class LillyService implements ServiceInterface
{
    private App $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    public function getScore(string $url, string $sample = ''): array
    {
        $fetch = $this->fetch($url);

        similar_text($this->removeHtml($fetch['response']), $this->removeHtml($sample), $html);
        similar_text($fetch['response'], $sample, $text);

        return [
            'text' => $sample ? $text : 100,
            'html' => $sample ? $html : 100,
            'hash' => md5($fetch['response']),
            'text_size' => strlen($this->removeHtml($fetch['response'])),
            'html_size' => strlen($fetch['response']),
            'code' => $fetch['info']['http_code'],
            'body' => $fetch['response']
        ];
    }

    public function fetch(string $url)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Agent0.1');
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        return [
            'response' => $response,
            'info' => $info
        ];
    }

    private function removeHtml(string $string): string
    {
        return strip_tags($string);
    }

    public function halfParams(mixed $params): array
    {
        return [
            array_slice($params, 0, round(count($params)/2)),
            array_slice($params, round(count($params)/2)),
        ];
    }

    public function addPay(string $url, array $params): string
    {
        $payload = 'L(i)l\'l"y<LoVe>%00\\';

        $pay = '';
        foreach ($params as $param){
            $pay .= '&' . $param . '=' . $payload;
        }

        return $url . '?' . $pay;

    }


}
