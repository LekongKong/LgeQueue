<?php
//caoweijie@baixing.com
namespace Service\LgeQueue;
class WorkCommand extends \Illuminate\Queue\Console\WorkCommand {
    public function option($key = null) {
        $value = parent::option($key);

        return $key == 'queue' ? str_replace('+', ',', $value) : $value;
    }
}