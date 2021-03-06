<?php
/**
 * This file is part of Hackathon_Metrics for Magento.
 *
 * @license OSL v3
 * @author Marco Bamert <mb@bami.ch> <@marcobamert>
 * @category Hackathon
 * @package Hackathon_Metrics
 * @copyright Copyright (c) 2014 Magento Hackathon (http://mage-hackathon.de)
 */

class Hackathon_Metrics_Model_Queue
{
    protected $_messages = array();

    /**
     * add a key/value pair to the stack
     *
     * @param $key
     * @param $value
     * @return Hackathon_Metrics_Model_Queue
     */
    public function addMessage($key, $value = null, $tags = [], $type = Hackathon_Metrics_Model_Config::CHANNEL_MESSAGE_TYPE_INCREMENT)
    {
        $this->_messages[] = [
            'key'   => $key,
            'type'  => $type,
            'value' => $value,
            'tags'  => $tags,
        ];
        return $this;
    }

    public function __destruct()
    {
        $this->_sendMessages();
    }

    protected function _sendMessages()
    {
        $store  = Mage::app()->getStore();
        $mainTags = [
            'website'     => $store->getWebsite()->getCode(),
            'store_group' => $store->getGroupId(),
            'store'       => $store->getCode()
        ];
        foreach ($this->getActiveChannels() as $channel) {
            if (!$channel instanceof Hackathon_Metrics_Model_Channel_Interface) {
                throw new ErrorException("Your channel doesn't implement the channel interface.");
            }

            foreach ($this->_messages as $data) {
                $channel->send($data['key'], $data['value'], $data['tags'] + $mainTags, $data['type']);
            }

            $this->_messages = [];
        }
    }

    /**
     * get list of all active channels to push data to
     *
     * @return array
     */
    protected function getActiveChannels()
    {
        return Mage::getModel('hackathon_metrics/config')->getActiveChannels();
    }
}
