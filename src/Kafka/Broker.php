<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 15:29
 */

namespace Kafka;

class Broker {
    use SingletonTrait;

    private $groupBrokerId = null;

    private $topics = [];

    private $brokers = [];

    private $metaSockets = [];

    private $dataSockets = [];

    private $process;

    private $socket;

    public function setProcess(\Closure $process) {
        $this->process = $process;
    }

    public function setGroupBrokerId($brokerId) {
        $this->groupBrokerId = $brokerId;
    }

    public function getGroupBrokerId() {
        return $this->groupBrokerId;
    }

    public function setData($topics, $brokersResult) {
        $brokers = [];
        foreach ($brokersResult as $value) {
            $key           = $value['nodeId'];
            $hostname      = $value['host'] . ':' . $value['port'];
            $brokers[$key] = $hostname;
        }

        $change = false;
        if (serialize($this->brokers) != serialize($brokers)) {
            $this->brokers = $brokers;
            $change        = true;
        }

        $newTopics = [];
        foreach ($topics as $topic) {
            if ($topic['errorCode'] != Protocol::NO_ERROR) {
                $this->error('Parse metadata for topic is error, error:' . Protocol::getError($topic['errorCode']));
                continue;
            }
            $item = [];
            foreach ($topic['partitions'] as $part) {
                $item[$part['partitionId']] = $part['leader'];
            }
            $newTopics[$topic['topicName']] = $item;
        }

        if (serialize($this->topics) != serialize($newTopics)) {
            $this->topics = $newTopics;
            $change       = true;
        }

        return $change;
    }

    public function getTopics() {
        return $this->topics;
    }

    public function getBrokers() {
        return $this->brokers;
    }

    public function getMetaConnect($key, $modeSync = false) {
        return $this->getConnect($key, 'metaSockets', $modeSync);
    }

    public function getRandConnect($modeSync = false) {
        $nodeIds = array_keys($this->brokers);
        shuffle($nodeIds);
        if (!isset($nodeIds[0])) {
            return false;
        }

        return $this->getMetaConnect($nodeIds[0], $modeSync);
    }

    public function getDataConnect($key, $modeSync = false) {
        return $this->getConnect($key, 'dataSockets', $modeSync);
    }

    public function getConnect($key, $type, $modeSync = false) {
        if (isset($this->{$type}[$key])) {
            return $this->{$type}[$key];
        }

        if (isset($this->brokers[$key])) {
            $hostname = $this->brokers[$key];
            if (isset($this->{$type}[$hostname])) {
                return $this->{$type}[$hostname];
            }
        }

        $host = null;
        $port = null;
        if (isset($this->brokers[$key])) {
            $hostname = $this->brokers[$key];
            list($host, $port) = explode(':', $hostname);
        }

        if (strpos($key, ':') !== false) {
            list($host, $port) = explode(':', $key);
        }

        if ($host && $port) {
            try {
                $socket = $this->getSocket($host, $port, $modeSync);
                if (!$modeSync) {
                    $socket->setOnReadable($this->process);
                }
                $socket->connect();
                $this->{$type}[$key] = $socket;

                return $socket;
            } catch (\Exception $e) {
                $this->error($e->getMessage());

                return false;
            }
        } else {
            return false;
        }
    }

    // }}}
    // {{{ public function clear()

    public function clear() {
        foreach ($this->metaSockets as $key => $socket) {
            $socket->close();
        }
        foreach ($this->dataSockets as $key => $socket) {
            $socket->close();
        }
        $this->brokers = [];
    }

    // }}}
    // {{{ public function getSocket()

    public function getSocket($host, $port, $modeSync) {
        if ($this->socket != null) {
            return $this->socket;
        }

        if ($modeSync) {
            $socket = new SocketSync($host, $port);
        } else {
            $socket = new Socket($host, $port);
        }

        return $socket;
    }

    /**
     * @param $socket
     */
    public function setSocket($socket) {
        $this->socket = $socket;
    }
}