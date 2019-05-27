<?php

namespace Commands\Command\Pack;

use Commands\CommandConfig;

class CustomButton extends YmlSlotCommandProto
{
    /**
     * @return string
     */
    public function getId()
    {
        return CommandConfig::PACK_CUSTOM_BUTTON;
    }

    public function run()
    {
        $this->runtime[] = 'Wrong configuration in builder.yml for this custom banner. Link will be placed at "href" field';
    }

    /**
     * @return string
     */
    public function getLink()
    {
        /** @var \Service\Slot\CustomButtonYmlSlot $slot */
        $slot = $this->getSlot();
        if (!$slot || empty($slot->href)) {
            return parent::getLink();
        }

        return $this->mutateHrefPatterns($slot->href);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return self::TYPE_PACK;
    }

    /**
     * @param string $href
     * @return string
     */
    private function mutateHrefPatterns(string $href) : string
    {
        $patterns = [
            '%pack_name%'   => urlencode($this->getContext()->getPack()->getName()),
            '%branch_name%' => urlencode($this->getContext()->getCheckpoint()->getName()),
        ];

        return strtr($href, $patterns);
    }
}