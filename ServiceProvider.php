<?php
//caoweijie@baixing.com
namespace Service\LgeQueue;

use Illuminate\Queue\QueueServiceProvider;

class ServiceProvider extends QueueServiceProvider {
    /**
     * {@inheritdoc}
     */
    protected function registerWorkCommand() {
        $this->app->singleton('command.queue.work', function ($app) {
            return new WorkCommand($app['queue.worker']);
        });

        $this->commands('command.queue.work');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerWorker() {
        $this->registerWorkCommand();

        $this->registerRestartCommand();

        $this->app->singleton('queue.worker', function ($app) {
            return new Worker($app['queue'], $app['queue.failer'], $app['events']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register() {
        parent::register();
        $this->registerSuperConfCommand();
    }

    protected function registerSuperConfCommand() {
        $this->app->singleton('command.superconf', function ($app) {
           return new SuperConfCommand();
        });

        $this->commands('command.superconf');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFailedJobServices() {
        $this->app->singleton('queue.failer', function () {
            return new FailedJobProvider();
        });
    }
}
