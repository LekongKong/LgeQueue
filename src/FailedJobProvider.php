<?php
//maqidong@baixing.com
namespace Service\LgeQueue;

use Illuminate\Queue\Failed\NullFailedJobProvider;

class FailedJobProvider extends NullFailedJobProvider {
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     */
    public function log($connection, $queue, $payload) {
    }
}
