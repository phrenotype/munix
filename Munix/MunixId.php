<?php

namespace Munix;

class MunixId {

    public $timestamp;
    public $timestamp_ms;
    public $customId;
    public $sequence;

    public function __construct(int $id)
    {
        $this->timestamp_ms = ($id >> (Munix::CUSTOM_BITS + Munix::SEQUENCE_BITS)) + Munix::getEpoch();

        $this->timestamp = ($this->timestamp_ms / 1000);

        $this->customId = ($id >> Munix::SEQUENCE_BITS) & (0b111111111);

        $this->sequence = $id & (0b1111111111);
    }

    public function __get($name)
    {
        // Do nothing
    }

    public function __set($name, $value)
    {
        // Do nothing
    }
}
