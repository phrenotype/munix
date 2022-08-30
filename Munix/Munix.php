<?php

namespace Munix;

class Munix
{
    const TIMESTAMP_BITS = 41;
    const CUSTOM_BITS = 10;
    const SEQUENCE_BITS = 12;

    const MAX_TIMESTAMP = -1 ^ (-1 << self::TIMESTAMP_BITS);
    const MAX_CUSTOM_ID = -1 ^ (-1 << self::CUSTOM_BITS);
    const MAX_SEQUENCE = -1 ^ (-1 << self::SEQUENCE_BITS);

    private static $instance = null;

    private static $sequence = 0;
    private static $lastTimestamp = null;

    // Jan, 1 2022
    private static $customEpoch = 1640991600000;

    private static $customId;

    private function __construct(int $customId = null)
    {
        if ($customId && !self::$customId) {
            self::setCustomId($customId);
        } else if (!$customId && !self::$customId) {
            self::setCustomId(0);
        }
    }

    private function timestampDiff()
    {
        return floor(microtime(true) * 1000) - self::$customEpoch;
    }

    public function generate()
    {
        $currentTimestamp = $this->timestampDiff();

        if (self::$lastTimestamp > $currentTimestamp) {
            throw new \Error("Clock has moved backwards. Please try again.");
        }

        if (self::$lastTimestamp == $currentTimestamp) {

            // Identity law + bit field size :)
            self::$sequence = (self::$sequence + 1) & self::MAX_SEQUENCE;

            if (self::$sequence == 0) {
                $currentTimestamp = $this->waitTillNextMs(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }

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

    public static function setCustomId(int $customId)
    {
        if (self::$customId < 0 || self::$customId > self::MAX_CUSTOM_ID) {
            throw new \Error("Custom Id out of bounds. Must be between 0 and " . self::MAX_CUSTOM_ID);
        }
        self::$customId = $customId;
    }

    public static function nextId(int $customId = null)
    {
        if (!self::$instance) {
            $instance = new static($customId);
            self::$instance = $instance;
        }
        return self::$instance->generate();
    }
}
