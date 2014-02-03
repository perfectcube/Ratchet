<?php

/**
 * This file is part of Ratchet for CakePHP.
 *
 ** (c) 2012 - 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('WebsocketShell', 'Ratchet.Console/Command');
App::uses('CakeWampAppServer', 'Ratchet.Lib/Wamp');
App::uses('CakeEventManager', 'Event');

class SessionHandlerImposer {

	public function all() {
		return [];
	}

}

abstract class CakeRatchetTestCase extends CakeTestCase {

/**
 * {@inheritdoc}
 */
	public function setUp() {
		parent::setUp();

		$this->shell = $this->getMock('WebsocketShell', [], [
			'out',
		]);
		$this->loop = $this->getMock('React\\EventLoop\\LoopInterface');
		$this->eventManagerOld = CakeEventManager::instance();
		$this->eventManager = CakeEventManager::instance(new CakeEventManager());
		$this->AppServer = new CakeWampAppServer($this->shell, $this->loop, $this->eventManager, true);
	}

/**
 * {@inheritdoc}
 */
	public function tearDown() {
		unset($this->AppServer, $this->eventManager);

		CakeEventManager::instance($this->eventManagerOld);

		parent::tearDown();
	}

	protected function _expectedEventCalls(&$asserts, $events) {
		$cbi = [];
		foreach ($events as $eventName => $event) {
			$count = count($events[$eventName]['callback']);
			for ($i = 0; $i < $count; $i++) {
				$asserts[$eventName . '_' . $i] = false;
			}
			$cbi[$eventName] = 0;
			$this->eventManager->attach(
				function ($event) use (&$events, $eventName, &$asserts, &$cbi) {
					$asserts[$eventName . '_' . $cbi[$eventName]] = true;
					call_user_func($events[$eventName]['callback'][$cbi[$eventName]], $event);
					$cbi[$eventName]++;
				},
				$eventName
			);
		}

		return $asserts;
	}
}
