<?php

namespace Cm\Download\Api {


    use Cm\Download\Api;

    interface BuildListInterface
    {
        /**
         * Return the list of available builds for a given device and channel
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         * @return \Cm\Download\Api\Build[]
         */
        public function getBuilds($device, $channel = Api::CHANNEL_NIGHTLY);
    }
}
