<?php

namespace src/NeaceLukens/Bundle

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NeaceLukens\Bundle\Entity\Member;
use NeaceLukens\Bundle\Entity\MemberLicense;
use NeaceLukens\Bundle\Entity\MemberContactInfo;
use NeaceLukens\Bundle\Entity\MemberLicensePrefix;
use NeaceLukens\Bundle\Entity\MemberPolicyInfo;
use NeaceLukens\Bundle\Entity\MemberPreviousInsurance;
use NeaceLukens\Bundle\Entity\MemberCertificate;
use NeaceLukens\Bundle\Entity\Certificates;
use NeaceLukens\Bundle\Entity\CertificateInfo;

class LoadInitialData implements FixtureInterface {
	public function load(ObjectManager $manager)
	{
		$filename = __DIR__ . DIRECTORY_SEPARATOR . '..' .
			DIRECTORY_SEPARATOR . '..' .
			DIRECTORY_SEPARATOR . 'Resources/data/data.csv';



		// strip column headers before processing
		$multi_value_columns;
		$handle = fopen($filename, "r");
		
		// grab a row and begin processing it
		while ($line = fgets($handle)){
			$row = explode('|', $line);

			// guaranteed to exist, no need to check
			$member = new Member();
			$member setFirstName($row[2]);
			$member setLastName($row[3]);
			$member setAddressOne($row[4]);
			$member setCity($row[6]);
			$member setState($row[7]);
			$member setZip($row[8]);
			$member setPhone($row[9]);

			$manager->persist($member);
		}
		$manager->flush();
	}
}
