<?php

namespace

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NeaceLukens\entitystuff

class LoadInitialData implements FixtureInterface {
	public function load(ObjectManager $manager)
	{
	filename = __DIR__ . DIRECTORY_SEPARATOR . '..' .
		DIRECTORY_SEPARATOR . '..' .
		DIRECTORY_SEPARATOR . 'Resources/data/data.csv';

		
	}
}

?>