<?php
//maqidong@baixing.com
namespace Service\LgeQueue;

use Illuminate\Queue\Failed\NullFailedJobProvider;

class FailedJobProvider  extends NullFailedJobProvider {
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     */
    public function log($connection, $queue, $payload) {
        //TODO:看一下要怎么把基本的环境载入进来。
//        \Logger::event('FaliQueueEvent', ['connection' => $connection, 'queue' => $queue, 'payload' => $payload]);
    }
}
