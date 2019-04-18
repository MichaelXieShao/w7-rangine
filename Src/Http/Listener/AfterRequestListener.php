<?php

namespace W7\Http\Listener;

use W7\App;
use W7\Core\Listener\ListenerAbstract;

class AfterRequestListener extends ListenerAbstract {
    public function run(...$params) {
        $context = App::getApp()->getContext();
        $context->destroy();


        if (is_array($params[0]) || is_object($params[0])) {
            $params[0] = '';
        }

        return $params[0];
    }
}