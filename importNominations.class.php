<?php

class importNominations extends importBase
{
	public $type = 'month'; 
	
	function importNominations($file, $hasHeaderRow=true, $debug=false)
	{
		//$this->importBase($SQLAuth);
		$this->debug = $debug;
		$this->requiredColumns = array();
		$this->useHeaderRow($hasHeaderRow);
		
		
		$this->openFile($file);
		$this->getColumns();
	}
	
	function doTheWork()
	{
		$updated = date('Y-m-d G:i:s');
		$this->debug('Do The Work Function Called');
		

		while($data = $this->getArrayLine())
		{
			
			
			//$account = new bgAccount();
			//$account->loadByField('bgstaking_id', $data['IN_NNUMBER']); 
			
			/*$horse = new bgHorse();
			$horse->prepare()
					->addStatementEquals('bgstaking_horse_id', $data['YN_HNUMBER']) 
					->execute();*/
					
			$horse = new bgHorse();
			$horse->prepare()
					->addStatementBeginGroup()
					->addStatementEquals('bgstaking_nom_horse_id', $data['YN_HNUMBER'])
					->addStatementEndGroup('OR')
					/*->addStatementBeginGroup()
					->addStatementEquals('name', $data['HR_NAME'])
					->addStatementEquals('sire', $data['HR_SIRE']) 
					->addStatementEquals('dam', $data['HR_DAM']) 
					->addStatementEndGroup()*/
					->execute();
					
			$horse->bgstaking_nom_horse_id = $data['YN_HNUMBER'];
			$horse->name = $data['HR_NAME'];
			$horse->age = '1';
			$horse->sex = $data['HR_SEX'];
			$horse->sire = $data['HR_SIRE'];
			$horse->dam = $data['HR_DAM'];
			$horse->gait = $data['HR_GAIT'];
			$horse->save();
			
			if ($horse->hasRecord())
			{
				$nomination = new bgYearlingNomination();
				$nomination->prepare()
							->addStatementEquals('year', $data['YN_YEAR'])
							->addStatementEquals('bg_horse_id', $horse->id) 
							->addStatementEquals('type', $this->type)
							->addStatementEquals('event_sort', $data['EV_SORT'])
							->execute();
							
				$nomination->bg_horse_id = $horse->id;
				$nomination->status = $data['YN_STATUS'];
				$nomination->stake = $data['HR_SSTAKE'];
				$nomination->stake2 = $data['HR_SSTAKE2'];
				$nomination->reg_pap = $data['HR_REG_PAP'];
				$nomination->group = $data['YN_GROUP'];
				$nomination->sort = $data['NA_SORT'];
				$nomination->memo = $data['YN_MEMO'];
				$nomination->yes_no = ($data['YN_YESNO']=='Y') ? '1' : '0';
				$nomination->year = $data['YN_YEAR'];
				$nomination->submission_date = $data['YN_SUB_DT'];
				$nomination->submitted_by = $data['YN_SUN_BY'];
				$nomination->nomination_update_date = $data['YN_UPT_DT'];
				$nomination->save_us_amount = $data['YN_SAV_US'];
				$nomination->save_ca_amount = $data['YN_SAV_CA'];
				$nomination->event_name = $data['EV_NAME'];
				$nomination->event_sort = $data['EV_SORT'];
				$nomination->event_track = $data['EV_TRACK'];
				$nomination->event_age_rac = $data['EV_AGE_RAC'];
				$nomination->event_comment = $data['EV_COMM2'];
				$nomination->event_us_amt = $data['EV_AMT1'];
				$nomination->event_ca_amt = $data['EV_CAMT1'];
				$nomination->event_us_ca = $data['EV_USCAN'];
				$nomination->event_state_stake = $data['EV_SSTAKE'];
				$nomination->event_state_stake2 = $data['EV_SSTAKE2'];
				$nomination->date_updated = $updated;
				$nomination->type = $this->type;
				
				if (!$nomination->isExistingObject())
				{
					$nomination->date_created = date('m/d/Y G:i:s');
				}
				$nomination->save();
				
			}
			else
			{
				//$this->debug('Unable to Add Invoice '.$data['IN_INVNUM'].' Because the account with bgstaking_id "'.$data['IN_NNUMBER'].'" can\'t be found.');	
			}

		}
		$this->closeFile();
	}


}

?>