<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 14:23
 */

namespace Kafka;

use Kafka\Producer\Process;
use Kafka\Producer\SyncProcess;
use Psr\Log\LoggerAwareTrait;

class Producer {
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var Process|null
     */
    private $process = null;

    public function __construct(\Closure $producer = null)
    {
        if (is_null($producer)) {
            $this->process = new SyncProcess();
        } else {
            $this->process = new Process($producer);
        }
    }

    /**
     * start producer
     *
     * @param bool $data
     * @return mixed
     */
    public function send($data = true)
    {
        if ($this->logger) {
            $this->process->setLogger($this->logger);
        }
        if (is_bool($data)) {
            $this->process->start();
            if ($data) {
                \Amp\run();
            }
        } else {
            return $this->process->send($data);
        }
    }

    /**
     * syncMeta producer
     */
    public function syncMeta()
    {
        $this->process->syncMeta();
    }

    /**
     * producer success
     *
     * @param \Closure|null $success
     */
    public function success(\Closure $success = null)
    {
        $this->process->setSuccess($success);
    }

    /**
     * producer error
     *
     * @param \Closure|null $error
     */
    public function error(\Closure $error = null)
    {
        $this->process->setError($error);
    }
}