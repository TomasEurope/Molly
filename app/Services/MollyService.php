<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class MollyService implements ServiceInterface
{
    private DbService $db;

    private array $queue = [];
    private App $app;

    public function load(App $app): void
    {
        $this->db = $app->db;
        $this->app = $app;
    }

    final public function getIpsRandom(int $count = 1, ?string $where = null): array
    {
        return $this->db->fetchRowMany('SELECT id, ip FROM ips ' . ($where ? 'WHERE ' . $where : '') . ' ORDER BY updated_at ASC LIMIT :count'
            , ['count' => $count]);
    }

    final public function markUpdated(string $table, array $ids){
        $this->db->executeSql('UPDATE ' . $table . ' SET updated_at=CURRENT_TIMESTAMP WHERE id IN(' . implode(',', $ids) . ')');

    }

    final public function getResultIpRandom(): array
    {
        return $this->db->fetchRowMany('SELECT ip, ips_id AS id FROM results, ips WHERE ips_id=ips.id LIMIT 1');
    }

    public function getProtoRandom(int $count = 1): array
    {
        return $this->db->fetchRowMany('SELECT id, proto FROM protos ORDER BY RANDOM() LIMIT :count', ['count' => $count]);
    }

    public function getPortRandom(int $count = 1): array
    {
        return $this->db->fetchRowMany('SELECT id, id AS port FROM ports WHERE enabled=1 ORDER BY RANDOM() LIMIT :count', ['count' => $count]);
    }

    public function queueReset(): void
    {
        $this->queue = [];
    }

    public function queueAdd($one): void
    {
        $this->queue[] = $one;
    }

    public function queueExec(int $parallel)
    {
        $options = sprintf($this->app->config->options, $parallel);
        $time = time() . rand(0, 999);
        if (!mkdir($concurrentDirectory = $this->app->config->output . '/' . $time) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        foreach($this->queue as $one){
            $options .= '
--url ' . $one[1] . '
-o ' . $this->app->config->output . '/' . $time . '/' . $one[0] . '-.txt
';
        }

        file_put_contents($this->app->config->output . '/' . $time . '/options.txt', $options);

        exec('curl --config ' . $this->app->config->output . '/' . $time . '/options.txt' . ' > /dev/null 2>&1');
    }

    public function getFileRandom(int $count = 1): array
    {
        //$prio = random_int(0, 2);
        return $this->db->fetchRowMany('SELECT id, file FROM files ORDER BY RANDOM() LIMIT :count',
            ['count' => $count]
        );
    }

    public function getResultsRandom(int $count = 1, int $files_id = 0)
    {
        return $this->db->fetchRowMany('SELECT t1.id
FROM results t1
WHERE ips_id NOT IN (
SELECT ips_id FROM results WHERE t1.protos_id=results.protos_id AND t1.ips_id=results.ips_id AND t1.ports_id=results.ports_id
AND results.files_id=:files_id
)
AND files_id=0
ORDER BY RANDOM() LIMIT :count', ['count' => $count, 'files_id' => $files_id]);


    }

    public function parse(string $result, int $protosId, int $ipsId, int $portsId, int $filesId = 0): void
    {
        $head = [];
        $body = [];
        $file = explode("\r\n\r\n", $result);
        foreach($file as $part){
            if(str_starts_with($part, 'HTTP')){
                $head[] = $part;
            } else {
                $body[] = $part;
            }
        }

        $status = [0];
        if(count($head)){
            preg_match("/\d{3}/", $head[array_key_last($head)], $status);
        }

        $head = trim(implode("\r\n\r\n", $head));
        $body = trim(implode("\r\n\r\n", $body));
        $bodysHash = md5($body);
        $resultsRow = [
            'protos_id' => $protosId,
            'ips_id' => $ipsId,
            'ports_id' => $portsId,
            'files_id' => $filesId,
            'head' => $head,
            'bodys_hash' => $bodysHash,
            'size' => strlen($body),
            'status' => @$status[0]
        ];
        $bodysRow = [
            'body' => $body,
            'hash' => $bodysHash
        ];

        echo " TO INSERT ";
        if (!$filesId || ($resultsRow['size'] && (!str_starts_with($status[0], 4) || $status[0] == 403))) {
            print_r($resultsRow);
            $this->insertBodys($bodysRow);
            $this->insertResults($resultsRow);
        }
    }

    private function insertBodys(array $data):void
    {
        $data['body'] = @utf8_encode($data['body']);
        $this->db->insert('bodys', $data, null, true);
    }

    private function insertResults(array $data):void
    {
        $data['head'] = @utf8_encode($data['head']);
        $this->db->insert('results', $data, null, null, 'results_ips_id_ports_id_protos_id_file_id');
    }

    final public function recurseRmdir($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
