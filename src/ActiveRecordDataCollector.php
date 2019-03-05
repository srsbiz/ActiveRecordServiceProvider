<?php

namespace srsbiz\Silex;

use ActiveRecord\ActiveRecordLogger;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ActiveRecordDataCollector extends DataCollector {

	/**
	 * @var ActiveRecordLogger
	 */
	private $logger;

	/**
	 * @param ActiveRecordLogger $logger
	 */
	public function __construct(ActiveRecordLogger $logger) {
		$this->logger = $logger;
		$this->reset();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param \Exception|null $exception
	 */
	public function collect(Request $request, Response $response, Exception $exception = null) {
		$this->data = $this->fetchData();
	}

	/**
	 * @return string The collector name.
	 */
	public function getName() {
		return 'ar';
	}

	/**
	 * @return array
	 */
	public function getQueries() {
		return $this->data['queries'];
	}

	/**
	 * @return int The query count
	 */
	public function getQueryCount() {
		return $this->data['queryCount'];
	}
	
	public function getStatusColor() {
		$count = $this->getQueryCount();
		if ($count >= 75) {
			return 'red';
		} elseif ($count >= 35) {
			return 'yellow';
		} else {
			return '';
		}
	}

	/**
	 * @return float The total time of queries
	 */
	public function getTime() {
		$time = 0;

		/** @var array $data */
		/** @noinspection PhpUnusedLocalVariableInspection */
		foreach ($this->data['queries'] as $data) {
			$time += $data['time']; // TODO.
		}

		return $time;
	}

	public function reset() {
		$this->data = [
			'queries' => [],
			'queryCount' => 0,
			'totalTime' => 0,
		];
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return array
	 */
	private function fetchData() {
		$queries = $this->logger->getQueries();

		return [
			'queries' => $queries,
			'queryCount' => \count($queries),
			'totalTime' => \array_reduce($queries, function($prev, $item) {
				return $prev + $item['time'];
			}, 0)
		];
	}

}
