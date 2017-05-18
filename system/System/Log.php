<?php

namespace Mojo\System;

class Log {

    const LEVEL_ERROR		= 0b00000001;
	const LEVEL_WARN		= 0b00000010;
	const LEVEL_INFO		= 0b00000100;

    const MODE_DEBUG		= 0b00000001;
	const MODE_RELEASE		= 0b00000010;

    public static $messages = array();

    public static error($msg) {
        
    }

    public static warn() {

    }

    public static info() {

    }

}
