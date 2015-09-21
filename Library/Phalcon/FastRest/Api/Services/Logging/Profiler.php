<?php
namespace Phalcon\FastRest\Api\Services\Logging;
use Phalcon\Logger\AdapterInterface as Logger;
class Profiler {
	/** @type  Logger */
	private $logger;
	/** @type  int */
	private $lastTime;
	/** @type  int[] */
	private $groupTotals;
	/** @type  int[] */
	private $groupStarts;

	/**
	 * Constructor
	 * @param Logger $logger
	 */
	public function __construct(Logger $logger) {
		$this->setLogger($logger);
		$this->nextTime();
		$this->resetGroups();
	}

	/**
	 * profile
	 * @param string   $description
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Exception
	 */
	public function profile($description, \Closure $callback) {
		$this->startGroup($description);
		$returnVar = $callback();
		$this->endGroup($description);
		return $returnVar;
	}

	/**
	 * resetGroups
	 * @return void
	 */
	private function resetGroups() {
		$this->setGroupStarts([]);
		$this->setGroupTotals([]);
	}

	/**
	 * startGroup
	 * @param string $description
	 * @return void
	 */
	public function startGroup($description) {
		$groupStarts = $this->getGroupStarts();
		$groupStarts[$description] = microtime(true);
		$this->setGroupStarts($groupStarts);
	}

	/**
	 * endGroup
	 * @param string $description
	 * @return void
	 * @throws \Exception
	 */
	public function endGroup($description) {
		$groupStarts = $this->getGroupStarts();
		if(!array_key_exists($description, $groupStarts)) {
			throw new \Exception('No start group was specified for: '.$description);
		}
		$time = microtime(true);
		$difference = $time-$groupStarts[$description];
		$groupTotals = $this->getGroupTotals();
		if(!array_key_exists($description, $groupTotals)) {
			$groupTotals[$description] = 0;
		}
		$groupTotals[$description] += $difference;
		$this->setGroupTotals($groupTotals);
	}

	/**
	 * logGroups
	 * @return void
	 */
	public function logGroups() {
		foreach($this->getGroupTotals() as $description=>$total) {
			$time = number_format(round($total, 3), 3, '.', '');
			$this->getLogger()->info($description.': '.$time);
		}
		$this->resetGroups();
	}

	/**
	 * Getter
	 * @return \int[]
	 */
	private function getGroupTotals() {
		return $this->groupTotals;
	}

	/**
	 * Setter
	 * @param \int[] $groupTotals
	 */
	private function setGroupTotals(array $groupTotals) {
		$this->groupTotals = $groupTotals;
	}

	/**
	 * Getter
	 * @return \int[]
	 */
	private function getGroupStarts() {
		return $this->groupStarts;
	}

	/**
	 * Setter
	 * @param \int[] $groupStarts
	 */
	private function setGroupStarts(array $groupStarts) {
		$this->groupStarts = $groupStarts;
	}



	/**
	 * Getter
	 * @return int
	 */
	public function getLastTime() {
		return $this->lastTime;
	}

	/**
	 * Setter
	 * @param int $lastTime
	 */
	public function setLastTime($lastTime) {
		$this->lastTime = $lastTime;
	}

	/**
	 * Getter
	 * @return Logger
	 */
	private function getLogger() {
		return $this->logger;
	}

	/**
	 * Setter
	 * @param Logger $logger
	 */
	private function setLogger(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	 * log
	 * @param string $descriptionPoint
	 * @return void
	 */
	public function log($descriptionPoint) {
		$this->getLogger()->info($descriptionPoint.': '.$this->nextTime());
	}

	/**
	 * nextTime
	 * @return null|string
	 */
	private function nextTime() {
		$time = microtime(true);
		$difference = $time-$this->getLastTime();
		$this->setLastTime($time);
		if(!is_null($difference)) {
			return number_format(round($difference, 3), 3, '.', '');
		}
		return null;
	}


}