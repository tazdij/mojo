<?php

namespace Mojo\Event;

interface IListener {
	public function onEvent(&$event);
}
