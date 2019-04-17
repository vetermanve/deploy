<?php

namespace Service\Slot;

/**
 * This slots lives in project's file builder.yml by default
 * @package Service\Slot
 */
abstract class YmlSlotProto extends SlotProto
{
    protected const SLOT_TYPE = 'ymlSlot';

    /**
     * @param $cmd
     * @param $out
     * @param $result
     * @param $from
     * @return mixed|void
     */
    public function exec($cmd, &$out, &$result, $from)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $localPath
     * @param $targetPath
     * @param $from
     * @return mixed|void
     */
    public function deliveryFile($localPath, $targetPath, $from)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $cmd
     * @param $from
     * @param int $outLines
     */
    public function stdExec($cmd, $from, $outLines = 10)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $fromPath
     * @param $to
     * @param $from
     * @return mixed|void
     */
    public function createLink($fromPath, $to, $from)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $targetPath
     * @param $from
     * @return mixed|void
     */
    public function rmLink($targetPath, $from)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $cmd
     * @param $from
     * @return mixed|void
     */
    public function silentExec($cmd, $from)
    {
        $this->badMethodCall(__METHOD__);
    }

    /**
     * @param $dirName
     * @return bool|void
     */
    public function ensureDir($dirName)
    {
        $this->badMethodCall(__METHOD__);
    }

    protected function badMethodCall(string $method) : void
    {
        throw new \BadMethodCallException('YmlSlotProto: method `' . $method . '` does not exist');
    }
}