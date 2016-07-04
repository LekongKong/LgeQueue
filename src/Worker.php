<?php
//caoweijie@baixing.com
namespace Service\LgeQueue;
class Worker extends \Illuminate\Queue\Worker {
    public function daemon($connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0) {
        $lastRestart = $this->getTimestampOfLastQueueRestart();

        while ($this->shouldContinue()) {
            if ($this->daemonShouldRun()) {
                $this->runNextJobForDaemon(
                    $connectionName, $queue, $delay, $sleep, $maxTries
                );
            } else {
                $this->sleep($sleep);
            }

            if ($this->memoryExceeded($memory) || $this->queueShouldRestart($lastRestart)) {
                $this->stop();
            }
        }
        
        exit(5);
    }

    public function shouldContinue() {
        return !$this->manager->isDownForMaintenance();
    }

    protected function daemonShouldRun() {

        return $this->events->until('illuminate.queue.looping') !== false;
    }

}