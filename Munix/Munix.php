<?php

namespace Munix;

class Munix
{
    const TIMESTAMP_BITS = 44;
    const CUSTOM_BITS = 9;
    const SEQUENCE_BITS = 10;

    const MAX_TS = -1 ^ (-1 << self::TIMESTAMP_BITS);
    const MAX_CID = -1 ^ (-1 << self::CUSTOM_BITS);
    const MAX_SEQ = -1 ^ (-1 << self::SEQUENCE_BITS);

    private static $instance = null;

    private static $sequence = 0;
    private static $lastTimestamp = null;

    // Jan, 1 2022
    private static $customEpoch = 1640991600000;

    private static $customId;

    private $pdo;

    private function __construct(int $customId = null)
    {
        if ($customId && !self::$customId) {
            self::setCustomId($customId);
        } else if (!$customId && !self::$customId) {
            self::setCustomId(0);
        }

        $pdo = new \PDO('sqlite:' . __DIR__ . '/munix.sqlite3', null, null, [
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true,
        ]);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_TIMEOUT, 10);
        $this->pdo = $pdo;

        $pdo->query('CREATE TABLE IF NOT EXISTS munix(sequence INTEGER)');

        if ($this->getSequence() === null) {
            $pdo->query('INSERT INTO munix VALUES(0)');
        }
    }

    private function timestampDiff()
    {
        return floor(microtime(true) * 1000) - self::$customEpoch;
    }


    private function getSequence()
    {
        $query = 'SELECT sequence FROM munix';
        $obj = $this->pdo->query($query)->fetch(\PDO::FETCH_OBJ);

        if ($obj) {
            return $obj->sequence;
        }

        return null;
    }

    private function setSequence(int $value)
    {
        $this->pdo->query('UPDATE munix SET sequence=' . (int)$value);
    }

    public function generate()
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }

        $currentTimestamp = $this->timestampDiff();

        if (self::$lastTimestamp > $currentTimestamp) {
            throw new \Error("Clock has moved backwards. Please try again.");
        }

        if (self::$lastTimestamp == $currentTimestamp) {

            // Identity law + bit field size :)
            self::$sequence = (($this->getSequence() ?? 0) + 1) & self::MAX_SEQ;

            if (self::$sequence == 0) {
                $currentTimestamp = $this->waitTillNextMs(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }

        // Save new sequence number
        $this->setSequence(self::$sequence);
        $this->pdo->commit();
        usleep(1);


        self::$lastTimestamp = $currentTimestamp;

        $finalNumber = $currentTimestamp << (self::CUSTOM_BITS + self::SEQUENCE_BITS);
        $finalNumber |= (self::$customId << self::SEQUENCE_BITS);
        $finalNumber |= self::$sequence;

        return (string)$finalNumber;
    }

    public function waitTillNextMs(int $lastTimestamp)
    {
        $currentTimestamp = $this->timestampDiff();
        while ($currentTimestamp <= $lastTimestamp) {
            $currentTimestamp = $this->timestampDiff();
        }
        return $currentTimestamp;
    }

    public static function setEpoch(int $timestamp)
    {
        self::$customEpoch = $timestamp;
    }

    public static function getEpoch(){
        return self::$customEpoch;
    }

    public static function setCustomId(int $customId)
    {
        if (self::$customId < 0 || self::$customId > self::MAX_CID) {
            throw new \Error("Custom Id out of bounds. Must be between 0 and " . self::MAX_CID);
        }
        self::$customId = $customId;
    }

    public static function nextId(int $customId = null)
    {
        usleep(1);
        if (!self::$instance) {
            $instance = new static($customId);
            self::$instance = $instance;
        }
        return self::$instance->generate();
    }

    public static function nextIdAsObject(int $customId = null)
    {
        return new MunixId(self::nextId($customId));
    }

    public static function parse(int $id){
        return new MunixId($id);
    }
}
