<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 15:33
 */

namespace Kafka;

/**
 * Class Socket
 *
 * @package Kafka
 */
class Socket {

    /**
     *  read socket max length 5MB
     */
    const READ_MAX_LEN = 5242880;

    /**
     * max write socket buffer
     * fixed:send of 8192 bytes failed with errno=11 Resource temporarily
     * fixed:'fwrite(): send of ???? bytes failed with errno=35 Resource temporarily unavailable'
     * unavailable error info
     */
    const MAX_WRITE_BUFFER = 2048;

    /**
     * Send timeout in seconds.
     *
     * @var float
     */
    private $sendTimeoutSec = 0;

    /**
     * Send timeout in microseconds.
     *
     * @var float
     */
    private $sendTimeoutUsec = 100000;

    /**
     * Recv timeout in seconds
     *
     * @var float
     */
    private $recvTimeoutSec = 0;

    /**
     * Recv timeout in microseconds
     *
     * @var float
     */
    private $recvTimeoutUsec = 750000;

    /**
     * Stream resource
     *
     * @var mixed
     */
    private $stream = null;

    /**
     * Socket host
     *
     * @var mixed
     */
    private $host = null;

    /**
     * Socket port
     *
     * @var mixed
     */
    private $port = -1;

    /**
     * Max Write Attempts
     *
     * @var int
     */
    private $maxWriteAttempts = 3;

    /**
     * Reader watcher
     *
     * @var int
     */
    private $readWatcher = 0;

    /**
     * Write watcher
     *
     * @var int
     */
    private $writeWatcher = 0;

    /**
     * Write watcher
     *
     * @var int
     */
    private $writeBuffer = '';

    /**
     * Reader buffer
     *
     * @var int
     */
    private $readBuffer = '';

    /**
     * Reader need buffer length
     *
     * @var int
     */
    private $readNeedLength = 0;

    /**
     * @var null
     */
    private $onReadable = null;

    /**
     * Socket constructor.
     *
     * @param     $host
     * @param     $port
     * @param int $recvTimeoutSec
     * @param int $recvTimeoutUsec
     * @param int $sendTimeoutSec
     * @param int $sendTimeoutUsec
     */
    public function __construct(
        $host,
        $port,
        $recvTimeoutSec = 0,
        $recvTimeoutUsec = 750000,
        $sendTimeoutSec = 0,
        $sendTimeoutUsec = 100000
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->setRecvTimeoutSec($recvTimeoutSec);
        $this->setRecvTimeoutUsec($recvTimeoutUsec);
        $this->setSendTimeoutSec($sendTimeoutSec);
        $this->setSendTimeoutUsec($sendTimeoutUsec);
    }

    /**
     * @param $sendTimeoutSec
     */
    public function setSendTimeoutSec($sendTimeoutSec) {
        $this->sendTimeoutSec = $sendTimeoutSec;
    }

    /**
     * @param float $sendTimeoutUsec
     */
    public function setSendTimeoutUsec($sendTimeoutUsec) {
        $this->sendTimeoutUsec = $sendTimeoutUsec;
    }

    /**
     * @param float $recvTimeoutSec
     */
    public function setRecvTimeoutSec($recvTimeoutSec) {
        $this->recvTimeoutSec = $recvTimeoutSec;
    }

    /**
     * @param float $recvTimeoutUsec
     */
    public function setRecvTimeoutUsec($recvTimeoutUsec) {
        $this->recvTimeoutUsec = $recvTimeoutUsec;
    }

    /**
     * @param int $number
     */
    public function setMaxWriteAttempts($number) {
        $this->maxWriteAttempts = $number;
    }

    // }}}
    // {{{ public function connect()

    /**
     * Connects the socket
     *
     * @access public
     * @return void
     */
    public function connect() {
        if (!$this->isSocketDead()) {
            return;
        }

        if (empty($this->host)) {
            throw new Exception('Cannot open null host.');
        }
        if ($this->port <= 0) {
            throw new Exception('Cannot open without port.');
        }

        $this->stream = @fsockopen(
            $this->host,
            $this->port,
            $errno,
            $errstr,
            $this->sendTimeoutSec + ($this->sendTimeoutUsec / 1000000)
        );

        if ($this->stream == false) {
            $error = 'Could not connect to '
                     . $this->host . ':' . $this->port
                     . ' (' . $errstr . ' [' . $errno . '])';
            throw new Exception($error);
        }

        stream_set_blocking($this->stream, 0);
        stream_set_read_buffer($this->stream, 0);

        $this->readWatcher = \Amp\onReadable($this->stream, function () {
            do {
                $newData = @fread($this->stream, self::READ_MAX_LEN);
                if ($newData) {
                    $this->read($newData);
                }
            } while ($newData);
        });

        $this->writeWatcher = \Amp\onWritable($this->stream, function () {
            $this->write();
        }, array('enable' => false)); // <-- let's initialize the watcher as "disabled"
    }

    /**
     * reconnect the socket
     *
     * @access public
     * @return void
     */
    public function reconnect() {
        $this->close();
        $this->connect();
    }

    /**
     * get the socket
     *
     * @return mixed
     */
    public function getSocket() {
        return $this->stream;
    }

    /**
     * set on readable callback function
     *
     * @param \Closure $read
     */
    public function setOnReadable(\Closure $read) {
        $this->onReadable = $read;
    }

    /**
     * close the socket
     */
    public function close() {
        \Amp\cancel($this->readWatcher);
        \Amp\cancel($this->writeWatcher);
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->readBuffer     = '';
        $this->writeBuffer    = '';
        $this->readNeedLength = 0;
    }

    /**
     * checks if the socket is a valid resource
     *
     * @return bool
     */
    public function isResource() {
        return is_resource($this->stream);
    }

    /**
     * read from the socket at most $len bytes.
     * This method will not wait for all the requested data, it will return as soon as any data is received.
     * @param $data
     */
    public function read($data) {
        $this->readBuffer .= $data;
        do {
            // response start
            if ($this->readNeedLength == 0) {
                if (strlen($this->readBuffer) < 4) {
                    return;
                }
                $dataLen              = Protocol\Protocol::unpack(Protocol\Protocol::BIT_B32, substr($this->readBuffer, 0, 4));
                $this->readNeedLength = $dataLen;
                $this->readBuffer     = substr($this->readBuffer, 4);
            }

            if (strlen($this->readBuffer) < $this->readNeedLength) {
                return;
            }
            $data = substr($this->readBuffer, 0, $this->readNeedLength);

            $this->readBuffer     = substr($this->readBuffer, $this->readNeedLength);
            $this->readNeedLength = 0;
            call_user_func($this->onReadable, $data, (int)$this->stream);
        } while (strlen($this->readBuffer));
    }

    /**
     * Write to the socket.
     *
     * @param null $data
     */
    public function write($data = null) {
        if ($data != null) {
            $this->writeBuffer .= $data;
        }
        $bytesToWrite = strlen($this->writeBuffer);
        $bytesWritten = @fwrite($this->stream, $this->writeBuffer);

        if ($bytesToWrite === $bytesWritten) {
            \Amp\disable($this->writeWatcher);
        } elseif ($bytesWritten >= 0) {
            \Amp\enable($this->writeWatcher);
        } elseif ($this->isSocketDead()) {
            // todo
            $this->reconnect();
        }
        $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);
    }

    /**
     * check the stream is close
     *
     * @return bool
     */
    protected function isSocketDead() {
        return !is_resource($this->stream) || @feof($this->stream);
    }
}
