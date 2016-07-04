<?php
//caoweijie@baixing.com
namespace Service\LgeQueue;
class SuperConfCommand extends \Illuminate\Console\Command {
    private static $programTemplate = "[program:%s]\ncommand=php %s queue:work --queue=%s --daemon --tries=3 --sleep=3\nnumprocs=%d\n";
    private static $multyProcessJobName = "process_name=queue_%(program_name)s_%(process_num)s\n";
    private static $logFileTemplate = "stdout_logfile=%logdir%/queue_%(program_name)s.log\nstderr_logfile=%logdir%/queue_%(program_name)s.error.log\n";

    protected $name = 'superconf';
    protected $description = 'supervisor configuration generator';

    protected $defaultLogDir = '/var/log';

    protected $logDir, $groups, $artisan, $jobsDir;

    public function __construct($logDir = null, $groups = null, $artisan = null, $jobsDir = null) {
        parent::__construct();
        $this->logDir = $logDir ?: \Config::get('queue.supervisor.logs');
        $this->groups = $groups ?: \Config::get('queue.supervisor.groups');
        $this->artisan = $artisan ?: \Config::get('queue.supervisor.artisan');
        $this->jobsDir = $jobsDir ?: \Config::get('queue.supervisor.jobs');
    }

    public function handle() {
        $groupedJobs = [];
        $programBlocks = [];
        $programNames = [];

        $definedJobs = $this->getAllJobs();
        $groups = $this->groups ?: [];

        foreach ($groups as $groupDef) {
            $jobs = $groupDef['jobs'];
            $processNum = isset($groupDef['processNum']) ? $groupDef['processNum'] : 1;
            array_walk($jobs, function (&$job) {
                $parts = explode('\\', $job);
                $job = end($parts);
            });
            $groupedJobs = array_merge($groupedJobs, $jobs);
            list($name, $block) = $this->makeProgramBlock($jobs, $processNum);
            $programNames[] = $name;
            $programBlocks[] = $block;
        }
        list($name, $block) = $this->makeProgramBlock(array_diff($definedJobs, $groupedJobs), 1);
        if ($name) {
            $programNames[] = $name;
            $programBlocks[] = $block;
        }

        $defaultQueueType = \Config::get('queue.default');
        list($defaultQueueName, $defaultQueueBlock) = $this->makeProgramBlock([\Config::get("queue.connections.$defaultQueueType.queue")], 1);
        $programNames[] = $defaultQueueName;
        $programBlocks[] = $defaultQueueBlock;

        $groupValue = str_replace('%programs%', implode(',', $programNames), str_replace('%logdir%', $this->getLogDir(),self::$groupValue));

        echo implode("\n", $programBlocks) . $groupValue;
    }

    protected function getLogDir() {
        return  $this->logDir ?: $this->defaultLogDir;
    }

    protected function getAllJobs() {
        $files = scandir($this->jobsDir);
        $files = array_combine($files, $files);
        unset($files['.']);
        unset($files['..']);
        unset($files['Job.php']);
        $files = array_values($files);
        array_walk($files, function (&$item) {
            $parts = explode('.', $item);
            $item = head($parts);
        });

        return $files;
    }

    protected function makeProgramBlock($jobs, $processNum) {
        if (!$jobs) {
            return [null, null];
        }
        $name = implode('+', $jobs);
        $block = sprintf(self::$programTemplate, $name, $this->artisan, $name, $processNum);
        if ($processNum > 1) {
            $block .= self::$multyProcessJobName;
        }
        $block .= str_replace('%logdir%', $this->getLogDir(), self::$logFileTemplate);

        return [$name, $block];
    }

    private static $groupValue = <<<groupValue

[group:queue]
programs=%programs%

process_name=queue_%(program_name)s
autostart=true
autorestart=unexpected
exitcodes=5
startsecs=3
startretries=3
stopsignal=QUIT
stopwaitsecs=10
redirect_stderr=false
stdout_logfile_maxbytes=1MB
stdout_logfile_backups=10
stdout_capture_maxbytes=1MB
stdout_events_enabled=false
stderr_logfile_maxbytes=1MB
stderr_logfile_backups=10
stderr_capture_maxbytes=1MB
stderr_events_enabled=false

[supervisord]
http_port=/var/tmp/supervisor.sock
logfile=%logdir%/supervisord.log
logfile_maxbytes=50MB
logfile_backups=10
loglevel=info
pidfile=/var/run/supervisord.pid
nodaemon=false
minfds=1024
minprocs=200

[supervisorctl]
serverurl=unix:///var/tmp/supervisor.sock

groupValue;
}
