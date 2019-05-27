<?php

namespace Service;

use Admin\App;
use Exception\BuilderException;
use Git\GitRepository;
use Service\Slot\CustomButtonYmlSlot;
use Service\Slot\EmptySlot;
use Service\Slot\LocalSlot;
use Service\Slot\RemoteSshSlot;
use Service\Slot\SlotProto;
use Service\Slot\TagYmlSlot;
use Service\Slot\YmlSlotProto;

class SlotFactory
{
    /**
     * @param $id
     * @param string|null $type
     * @param \Service\Pack|null $pack
     * @return \Service\Slot\SlotProto|null
     */
    public static function getSlot($id, string $type = null, Pack $pack = null) : ?SlotProto
    {
        $slot = null;
        switch ($type) {
            case YmlSlotProto::getSlotType():
                $slot = null !== $pack ? self::getYmlSlotById($id, $pack->getRepos()) : null;
                break;
            default:
                $slotData = (new Data(App::DATA_SLOTS))->setReadFrom(__METHOD__)->readCachedId($id);
                $slot = self::getSlotModel($slotData);
                break;
        }

        return $slot;
    }

    /**
     * @param $id
     * @param GitRepository[] $gitRepositories
     * @return \Service\Slot\YmlSlotProto|null
     */
    public static function getYmlSlotById(string $id, array $gitRepositories) : ?YmlSlotProto
    {
        $slot = null;
        foreach ($gitRepositories as $repository) {
            if (!$repository instanceof GitRepository) {
                continue;
            }
            if (empty($repository->getPath())) {
                continue;
            }
            // find all slots and find needle by id
            foreach (self::makeYmlSlots($repository->getPath()) as $slot) {
                if ($slot->getId() !== $id) {
                    continue;
                }

                break 2;
            }
        }

        return $slot;
    }

    /**
     * @param string $dir
     * @param string $filename
     * @return \Service\Slot\YmlSlotProto[]
     */
    public static function makeYmlSlots(string $dir, $filename = 'builder.yml') : array
    {
        $slotModels = [];
        if (!empty($dir) && null !== $filename) {
            $yml = new Yml();
            $slots = $yml->parse(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename);

            foreach ($slots as $id => $slotData) {
                $slotData['id'] = $id; // copy key to id field
                $slotModels[$id] = self::getSlotModel($slotData);
            }
        }

        return $slotModels;
    }
    
    /**
     * @param $slotData
     *
     * @return SlotProto
     */
    public static function getSlotModel(&$slotData) : SlotProto
    {
        switch ($slotData['type'] ?? null) {
            case SlotsPool::SLOT_TYPE_LOCAL:
                $model = new LocalSlot();
                break;
            case SlotsPool::SLOT_TYPE_REMOTE:
                $model = new RemoteSshSlot();
                break;
            case SlotsPool::SLOT_TYPE_TAG:
                $model = new TagYmlSlot();
                break;
            case SlotsPool::SLOT_TYPE_CUSTON_BUTTON:
                $model = new CustomButtonYmlSlot();
                break;
            default:
                $model = new EmptySlot();
                break;
        }

        $model->setData($slotData);
        $model->init();
        
        return $model;
    }
}