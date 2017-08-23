<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 14:58
 */

namespace Kafka;

class Protocol {

    /**
     * protocol request code
     */
    const PRODUCE_REQUEST = 0;
    const FETCH_REQUEST   = 1;
    const OFFSET_REQUEST  = 2;
    const METADATA_REQUEST      = 3;
    const OFFSET_COMMIT_REQUEST = 8;
    const OFFSET_FETCH_REQUEST  = 9;
    const GROUP_COORDINATOR_REQUEST = 10;
    const JOIN_GROUP_REQUEST  = 11;
    const HEART_BEAT_REQUEST  = 12;
    const LEAVE_GROUP_REQUEST = 13;
    const SYNC_GROUP_REQUEST  = 14;
    const DESCRIBE_GROUPS_REQUEST = 15;
    const LIST_GROUPS_REQUEST     = 16;

    /**
     * protocol error code
     */
    const NO_ERROR = 0;
    const ERROR_UNKNOWN = -1;
    const OFFSET_OUT_OF_RANGE = 1;
    const INVALID_MESSAGE = 2;
    const UNKNOWN_TOPIC_OR_PARTITION = 3;
    const INVALID_MESSAGE_SIZE = 4;
    const LEADER_NOT_AVAILABLE = 5;
    const NOT_LEADER_FOR_PARTITION = 6;
    const REQUEST_TIMED_OUT = 7;
    const BROKER_NOT_AVAILABLE = 8;
    const REPLICA_NOT_AVAILABLE = 9;
    const MESSAGE_SIZE_TOO_LARGE = 10;
    const STALE_CONTROLLER_EPOCH = 11;
    const OFFSET_METADATA_TOO_LARGE = 12;
    const GROUP_LOAD_IN_PROGRESS = 14;
    const GROUP_COORDINATOR_NOT_AVAILABLE = 15;
    const NOT_COORDINATOR_FOR_GROUP = 16;
    const INVALID_TOPIC = 17;
    const RECORD_LIST_TOO_LARGE = 18;
    const NOT_ENOUGH_REPLICAS = 19;
    const NOT_ENOUGH_REPLICAS_AFTER_APPEND = 20;
    const INVALID_REQUIRED_ACKS = 21;
    const ILLEGAL_GENERATION = 22;
    const INCONSISTENT_GROUP_PROTOCOL = 23;
    const INVALID_GROUP_ID = 24;
    const UNKNOWN_MEMBER_ID = 25;
    const INVALID_SESSION_TIMEOUT = 26;
    const REBALANCE_IN_PROGRESS = 27;
    const INVALID_COMMIT_OFFSET_SIZE = 28;
    const TOPIC_AUTHORIZATION_FAILED = 29;
    const GROUP_AUTHORIZATION_FAILED = 30;
    const CLUSTER_AUTHORIZATION_FAILED = 31;
    const UNSUPPORTED_FOR_MESSAGE_FORMAT = 43;

    protected static $objects = array();

    public static function init($version, $logger = null)
    {
        $class = array(
            \Kafka\Protocol\Protocol::PRODUCE_REQUEST => 'Produce',
            \Kafka\Protocol\Protocol::FETCH_REQUEST => 'Fetch',
            \Kafka\Protocol\Protocol::OFFSET_REQUEST => 'Offset',
            \Kafka\Protocol\Protocol::METADATA_REQUEST => 'Metadata',
            \Kafka\Protocol\Protocol::OFFSET_COMMIT_REQUEST => 'CommitOffset',
            \Kafka\Protocol\Protocol::OFFSET_FETCH_REQUEST => 'FetchOffset',
            \Kafka\Protocol\Protocol::GROUP_COORDINATOR_REQUEST => 'GroupCoordinator',
            \Kafka\Protocol\Protocol::JOIN_GROUP_REQUEST => 'JoinGroup',
            \Kafka\Protocol\Protocol::HEART_BEAT_REQUEST => 'Heartbeat',
            \Kafka\Protocol\Protocol::LEAVE_GROUP_REQUEST => 'LeaveGroup',
            \Kafka\Protocol\Protocol::SYNC_GROUP_REQUEST => 'SyncGroup',
            \Kafka\Protocol\Protocol::DESCRIBE_GROUPS_REQUEST => 'DescribeGroups',
            \Kafka\Protocol\Protocol::LIST_GROUPS_REQUEST => 'ListGroup',
        );

        $namespace = '\\Kafka\\Protocol\\';
        foreach ($class as $key => $className) {
            $class = $namespace . $className;
            self::$objects[$key] = new $class($version);
            if ($logger) {
                self::$objects[$key]->setLogger($logger);
            }
        }
    }
}