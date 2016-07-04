# LgeQueue
laravel graceful exit queue
# usage scenario
### need the queue to exit
The recommanded method to graceful restart a queue is using these commands


	php artisan down
	php artisan queue:restart

But, sometimes, we still want the queue to autoexit when necessary. For example, our team using docker to deploy the software want to determine the queue worker has finished current job and stop doing anything before we remove the old version and deploy a new one.
### use multi-processes to run queue
When some queue has more jobs than others, it may be good to set it to an indenpendent process. We make a new artisan command to make such complex supervisor configurations.
# install
1. You can install with composer

	composer reqiure lekongkong/lgequeue

2. Please replace the queue service provider, done.
# config
Add this config sample to your `queue.php` config file.

	'supervisor' => [

		//the dir to put logs of supervisor and jobs
        'logs' => '/var/logs', 

		//the artisan file path
        'artisan' => '/path/to/artisan', 

		// the dir where you store jobs
        'jobs' => '/path/to/Jobs',

		//jobs in one group will be put in one processes
        'groups' => [ 
            [
                'jobs' => [\app\Jobs\SendPush::class],
            ],
            [
                'jobs' => [\app\Jobs\AfterAdSaved::class, \app\Jobs\SelectAd::class, \app\Jobs\SendChatMessage::class],
                'processNum' => 1,
            ],
        ],
    ],

### addtional config
you may add a new config `handler` to `failed` in `queue.php`, which will enable your own failed job provider.

	'failed' => [
        'handler' => \LgeQueue\FailedJobProvider::class
    ],

# usage
### make supervisor config and start

	php artisan superconf > supervisor.conf
	supervisord -c supervisord.conf

### call the queue processes to exit

	php artisan down

# coding tips
* You can extend the `Job` class of this repo rather than the original one, which containing a convenient method `onSelf` to push the job to the queue named by its class. Otherwise you may have to adjust the `groups` config to add your own queue name, not the class name as in sample.
* Besides the grouped jobs queue processes, there will be a default queue process using the default queue name, configed in `queue.php`.
* Tested in redis queue backend, should work appropriate on other backend.
