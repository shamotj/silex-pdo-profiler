<?php

namespace Shamotj\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;


class PdoDataCollector extends DataCollector
{
    /**
     * @var \PDO $pdo
     */
    private $pdo;

    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {

        $data = array(
            'nb_statements' => 0,
            'nb_failed_statements' => 0,
            'accumulated_duration' => 0,
            'memory_usage' => 0,
            'peak_memory_usage' => 0,
            'statements' => array()
        );

        $pdodata = $this->collectPDO($this->pdo);
        $data['nb_statements'] += $pdodata['nb_statements'];
        $data['nb_failed_statements'] += $pdodata['nb_failed_statements'];
        $data['accumulated_duration'] += $pdodata['accumulated_duration'];
        $data['memory_usage'] += $pdodata['memory_usage'];
        $data['peak_memory_usage'] = max($data['peak_memory_usage'], $pdodata['peak_memory_usage']);

        $data['accumulated_duration_str'] = $data['accumulated_duration'];
        $data['memory_usage_str'] = $data['memory_usage'];
        $data['peak_memory_usage_str'] = $data['peak_memory_usage'];

        $this->data = $data;
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getQueryCount()
    {
        return count($this->data['queries']);
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $queries) {
            foreach ($queries as $query) {
                $time += $query['executionMS'];
            }
        }

        return $time;
    }

    public function getName()
    {
        return 'db';
    }

    /**
     * Collects data from a single TraceablePDO instance
     *
     * @param TraceablePDO $pdo
     * @param TimeDataCollector $timeCollector
     * @return array
     */
    protected function collectPDO(TraceablePDO $pdo, TimeDataCollector $timeCollector = null)
    {
        $stmts = array();
        foreach ($pdo->getExecutedStatements() as $stmt) {
            $stmts[] = array(
                'sql'            => $stmt->getSql(),
                'row_count'      => $stmt->getRowCount(),
                'stmt_id'        => $stmt->getPreparedId(),
                'prepared_stmt'  => $stmt->getSql(),
                'params'         => (object)$stmt->getParameters(),
                'duration'       => $stmt->getDuration(),
                'duration_str'   => $this->getDataFormatter()->formatDuration($stmt->getDuration()),
                'memory'         => $stmt->getMemoryUsage(),
                'memory_str'     => $this->getDataFormatter()->formatBytes($stmt->getMemoryUsage()),
                'end_memory'     => $stmt->getEndMemory(),
                'end_memory_str' => $this->getDataFormatter()->formatBytes($stmt->getEndMemory()),
                'is_success'     => $stmt->isSuccess(),
                'error_code'     => $stmt->getErrorCode(),
                'error_message'  => $stmt->getErrorMessage()
            );
            if ($timeCollector !== null) {
                $timeCollector->addMeasure($stmt->getSql(), $stmt->getStartTime(), $stmt->getEndTime());
            }
        }

        return array(
            'nb_statements'            => count($stmts),
            'nb_failed_statements'     => count($pdo->getFailedExecutedStatements()),
            'accumulated_duration'     => $pdo->getAccumulatedStatementsDuration(),
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($pdo->getAccumulatedStatementsDuration()),
            'memory_usage'             => $pdo->getMemoryUsage(),
            'memory_usage_str'         => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
            'peak_memory_usage'        => $pdo->getPeakMemoryUsage(),
            'peak_memory_usage_str'    => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
            'statements'               => $stmts
        );
    }

}