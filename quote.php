<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

require_once 'quote.civix.php';

function quote_civicrm_custom($op, $groupID, $entityID, &$params) {

	$extdebug	= 1;
	$extpro		= 1;
	$extprowrite= 1;
        $extproupdate = 0;

	if (!in_array($groupID, array("150"))) { // ALLEEN PART PROFILES
		// 150  PROMOTIE
		#if ($extdebug == 1) { watchdog('php', '<pre>--- SKIP EXTENSION QUOTE (not in proper group) [groupID: '.$groupID.'] [op: '.$op.']---</pre>', null, WATCHDOG_DEBUG); }
		return; //   if not, get out of here
	}

	if (in_array($groupID, array("150"))) {
		// 150  PROMOTIE

    	if ($extdebug == 1) { watchdog('php', '<pre>*** 1. START EXTENSION QUOTE [groupID: '.$groupID.'] [op: '.$op.'] [entityID: '.$entityID.'] ***</pre>', null, WATCHDOG_DEBUG); }

		if ($op != 'create' && $op != 'edit') { //    did we just create or edit a custom object?
    		if ($extdebug == 1) { watchdog('php', '<pre>EXIT: op != create OR op != edit</pre>', NULL, WATCHDOG_DEBUG); }
			return; //    if not, get out of here
		}
		$contact_id 	= NULL;
		$todaytime  	= date("H:i:s");
		$todaydatetime  = date("Y-m-d H:i:s");

		$quotedeel_activity_datetime	= NULL;
		$quoteouder_activity_datetime	= NULL;
		$quoteleid_activity_datetime	= NULL;

		// ************************************************************************************************************
		// 2 GET BASIC CONTACT INFO
		// ************************************************************************************************************

   		if (in_array($groupID, array("150"))) {	// TAB PROMOTIE
      		$params_contactinfo['return'] 		= array("id","contact_id","first_name","display_name","custom_631","custom_632","custom_633","custom_648","custom_642","custom_644","custom_1091","custom_1092","custom_1090");
			$params_contactinfo['sequential']	= 1;
			$params_contactinfo['id'] 			= $entityID;
   		}
   		try{
   			#if ($extdebug == 1) { watchdog('php', '<pre>params_contactinfo:' . print_r($params_contactinfo, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  			$result = civicrm_api3('Contact', 'get', $params_contactinfo);
   			#if ($extdebug == 1) { watchdog('php', '<pre>result_contactinfo:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		}
		catch (CiviCRM_API3_Exception $e) {
   			// Handle error here.
   			$errorMessage 	= $e->getMessage();
   			$errorCode 		= $e->getErrorCode();
   			$errorData 		= $e->getExtraParams();
   			if ($extdebug == 1) { watchdog('php', '<pre>ERROR: errorMessage:' . print_r($errorMessage, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		}

		$contact_id 			= $result['values'][0]['contact_id'];
		$first_name				= $result['values'][0]['first_name'];
		$display_name			= $result['values'][0]['display_name'];
		$quote_deel 			= $result['values'][0]['custom_631'];
 		$quote_ouder 			= $result['values'][0]['custom_632'];
 		$quote_leid 			= $result['values'][0]['custom_633'];
 		$quote_ok_deel 			= $result['values'][0]['custom_648'];
 		$quote_ok_ouder 		= $result['values'][0]['custom_642'];
 		$quote_ok_leid 			= $result['values'][0]['custom_644'];
 		$quote_datumok_deel 	= $result['values'][0]['custom_1091'];
 		$quote_datumok_ouder	= $result['values'][0]['custom_1092'];
 		$quote_datumok_leid		= $result['values'][0]['custom_1090'];
 		if ($quote_datumok_deel) 	{ $quote_datumok_deel 	= date('Y-m-d', strtotime($result['values'][0]['custom_1091']));	}
 		if ($quote_datumok_ouder) 	{ $quote_datumok_ouder 	= date('Y-m-d', strtotime($result['values'][0]['custom_1092']));	}
 		if ($quote_datumok_leid) 	{ $quote_datumok_leid 	= date('Y-m-d', strtotime($result['values'][0]['custom_1090']));	}

		if ($extdebug == 1) { watchdog('php', '<pre>Display Name:' . print_r($display_name, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>1. quote_deel:' . print_r($quote_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>2. quote_ouder:' . print_r($quote_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>3. quote_leid:' . print_r($quote_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }

		if ($extdebug == 1) { watchdog('php', '<pre>1. quote_ok_deel:' . print_r($quote_ok_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>2. quote_ok_ouder:' . print_r($quote_ok_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>3. quote_ok_leid:' . print_r($quote_ok_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }

 		if ($extdebug == 1) { watchdog('php', '<pre>a. quote_datumok_deel:' . print_r($quote_datumok_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>b. quote_datumok_ouder:' . print_r($quote_datumok_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
		if ($extdebug == 1) { watchdog('php', '<pre>c. quote_datumok_leid:' . print_r($quote_datumok_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }

   			if ($extpro == 1 AND (in_array($groupID, array("150")))) {
			// ************************************************************************************************************
			// 3 GET ACTIVITIES MBT. QUOTE
			// ************************************************************************************************************
   				if ($extdebug == 1) { watchdog('php', '<pre>### 3. QUOTE ACTIVITIES [GET] [groupID: '.$groupID.'] [op: '.$op.'] ###</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				// 3.1 GET ACTIVITIES 'QUOTE deel'
				// ************************************************************************************************************
   				$params_quote_activity_deel_get = [		// zoek activities 'QUOTE deel'
  					'sequential' 		=> 1,
  					'return' 			=> array("id", "activity_date_time", "status_id", "subject"),
  					'target_contact_id'	=> $contact_id,
  					'activity_type_id'	=> "QUOTE_deel",
  				];
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_deel_get:' . print_r($params_quote_activity_deel_get, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				$result_quotedeel = civicrm_api3('Activity', 'get', $params_quote_activity_deel_get);
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_deel_get_result:' . print_r($result_quotedeel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				#if ($extdebug == 1) { watchdog('php', '<pre>result_count_verzoek:' . print_r($result_quotedeel['count'], TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				if ($result_quotedeel['count'] == 1) {
  					$quotedeel_activity_id		= $result_quotedeel['values'][0]['id'];
  					$quotedeel_activity_datetime= $result_quotedeel['values'][0]['activity_date_time'];
  					$quotedeel_activity_status	= $result_quotedeel['values'][0]['status_id'];
	  				if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_id:' . print_r($quotedeel_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_status:' . print_r($quotedeel_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				} else {
					$quotedeel_activity_id		= NULL;
  					$quotedeel_activity_status	= NULL;
  					if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity: No Activity Found</pre>', NULL, WATCHDOG_DEBUG); }
  				}
				// ************************************************************************************************************
				// 3.2 GET ACTIVITIES 'QUOTE ouder'
				// ************************************************************************************************************
  				$params_quote_activity_ouder_get = [		// zoek activities 'QUOTE ouder'
   					'sequential' 		=> 1,
  					'return' 			=> array("id", "activity_date_time", "status_id", "subject"),
  					'target_contact_id'	=> $contact_id,
  					'activity_type_id'	=> "QUOTE_ouder",
  				];
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_ouder_get:' . print_r($params_quote_activity_ouder_get, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
   				$result_quoteouder = civicrm_api3('Activity', 'get', $params_quote_activity_ouder_get);
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_ouder_get_result:' . print_r($result_quoteouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				#if ($extdebug == 1) { watchdog('php', '<pre>result_count_aanvraag:' . print_r($result_quoteouder['count'], TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				if ($result_quoteouder['count'] == 1) {
  					$quoteouder_activity_id		= $result_quoteouder['values'][0]['id'];
  					$quoteouder_activity_datetime= $result_quoteouder['values'][0]['activity_date_time'];
  					$quoteouder_activity_status	= $result_quoteouder['values'][0]['status_id'];
	  				if ($extdebug == 1) { watchdog('php', '<pre>quoteouder_activity_id:' . print_r($quoteouder_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  					if ($extdebug == 1) { watchdog('php', '<pre>quoteouder_activity_status:' . print_r($quoteouder_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				} else {
					$quoteouder_activity_id		= NULL;
  					$quoteouder_activity_status	= NULL;
  					if ($extdebug == 1) { watchdog('php', '<pre>quoteouder_activity: No Activity Found</pre>', NULL, WATCHDOG_DEBUG); }
  				}
				// ************************************************************************************************************
				// 3.3 GET ACTIVITIES 'QUOTE leid'
				// ************************************************************************************************************
  				$params_quote_activity_leid_get = [		// zoek activities 'QUOTE leid'
   					'sequential' 		=> 1,
  					'return' 			=> array("id", "activity_date_time", "status_id", "subject"),
  					'target_contact_id'	=> $contact_id,
  					'activity_type_id'	=> "QUOTE_leid",
  				];
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_leid_get:' . print_r($params_quote_activity_leid_get, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				$result_quoteleid = civicrm_api3('Activity', 'get', $params_quote_activity_leid_get);
  				#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_leid_get_result:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				#if ($extdebug == 1) { watchdog('php', '<pre>result_count_ontvangst:' . print_r($result['count'], TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				if ($result_quoteleid['count'] == 1) {
  					$quoteleid_activity_id		= $result_quoteleid['values'][0]['id'];
  					$quoteleid_activity_datetime= $result_quoteleid['values'][0]['activity_date_time'];
  					$quoteleid_activity_status	= $result_quoteleid['values'][0]['status_id'];
	  				if ($extdebug == 1) { watchdog('php', '<pre>quoteleid_activity_id:' . print_r($quoteleid_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					if ($extdebug == 1) { watchdog('php', '<pre>quoteleid_activity_status:' . print_r($quoteleid_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  				} else {
					$quoteleid_activity_id		= NULL;
  					$quoteleid_activity_status	= NULL;
  					if ($extdebug == 1) { watchdog('php', '<pre>quoteleid_activity: No Activity Found</pre>', NULL, WATCHDOG_DEBUG); }
  				}
			}

			if ($extpro == 1 AND (in_array($groupID, array("150")))) {
			// ************************************************************************************************************
			// 4. BEPAAL DE JUISTE DATUMS VOOR ACTIVITIES AANVRAAG & ONTVANGST
			// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 4. QUOTE ACTIVITIES [DEFINE NEW DATE] [groupID: '.$groupID.'] [op: '.$op.'] ###</pre>', NULL, WATCHDOG_DEBUG); }

				// ************************************************************************************************************
				// 4.1 BEPAAL (NIEUWE) DATUM ACTIVITY DEEL
				// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 4.1 BEPAAL (NIEUWE) DATUM ACTIVITY DEEL ###</pre>', NULL, WATCHDOG_DEBUG); }
				if (empty($quote_datumok_deel) AND isset($quote_deel)) {
					$quote_datumact_deel  = $todaydatetime;
				} else {
					$quote_datumact_deel = date('Y-m-d H:i:s', strtotime($quote_datumok_deel . ' ' . $todaytime));
				}
				if ($extdebug == 1) { watchdog('php', '<pre>*. quote_datumact_deel:' . print_r($quote_datumact_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				// 4.2 BEPAAL (NIEUWE) DATUM ACTIVITY OUDER
				// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 4.2 BEPAAL (NIEUWE) DATUM ACTIVITY OUDER ###</pre>', NULL, WATCHDOG_DEBUG); }
				if (empty($quote_datumok_ouder) AND isset($quote_ouder)) {
					$quote_datumact_ouder  = $todaydatetime;
				} else {
					$quote_datumact_ouder = date('Y-m-d H:i:s', strtotime($quote_datumok_ouder . ' ' . $todaytime));
				}
				if ($extdebug == 1) { watchdog('php', '<pre>*. quote_datumact_ouder:' . print_r($quote_datumact_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				// 4.2 BEPAAL (NIEUWE) DATUM ACTIVITY LEID
				// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 4.3 BEPAAL (NIEUWE) DATUM ACTIVITY LEIDING ###</pre>', NULL, WATCHDOG_DEBUG); }
				if (empty($quote_datumok_leid) AND isset($quote_leid)) {
					$quote_datumact_leid  = $todaydatetime;
				} else {
					$quote_datumact_leid = date('Y-m-d H:i:s', strtotime($quote_datumok_leid . ' ' . $todaytime));
				}
				if ($extdebug == 1) { watchdog('php', '<pre>*. quote_datumact_leid:' . print_r($quote_datumact_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
			}

			if ($extprowrite == 1 AND (in_array($groupID, array("150")))) {
			// ************************************************************************************************************
			// 5. CREATE ACTIVITIES
			// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 5. QUOTE ACTIVITIES [CREATE] [groupID: '.$groupID.'] [op: '.$op.'] ###</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				// 5.1 CREATE AN ACTIVITY 'DEEL' ALS QUOTE ouder IS VERZOCHT EN ER IS NOG GEEN BIJBEHORENDE ACTIVITY
				// ************************************************************************************************************
				if (empty($quotedeel_activity_id) AND $quote_deel AND $quote_ok_deel != 'nee') {
					if ($extdebug == 1) { watchdog('php', '<pre>--- 5.1 CREATE AN ACTIVITY QUOTE DEEL ALS ER EEN QUOTE IS MAAR NOG GEEN ACTIVITY</pre>', NULL, WATCHDOG_DEBUG); }
					$results = \Civi\Api4\Activity::create()
					->setCheckPermissions(false)
  					->addValue('source_contact_id', 1)
  					->addValue('target_contact_id', $contact_id)
  					->addValue('activity_type_id', 126)
  					->addValue('activity_date_time', $quote_datumact_deel)
  					->addValue('subject', 'QUOTE toestemming deelnemer')
  					->addValue('status_id', 7) // initial status (draft = 7, afwachting = 9)
  					->setChain([
    					'name_me_0' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => $contact_id, 'record_type_id' => 3]], ],
    					'name_me_1' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => 1, 'record_type_id' => 2]], ]
  					])
  					->execute();
					foreach ($results as $result) {
  						// do something
  						if ($extdebug == 1) { watchdog('php', '<pre>QUOTE_deel_api4_create_results:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  						if (empty($quotedeel_activity_id))		{ $quotedeel_activity_id		= $result['id']; }
						if (empty($quotedeel_activity_status))	{ $quotedeel_activity_status	= 1; }
						if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_id2:' . print_r($quotedeel_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
						if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_status2:' . print_r($quotedeel_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
				// ************************************************************************************************************
				// 5.2 CREATE AN ACTIVITY 'OUDER' ALS QUOTE ouder IS VERZOCHT OF INGEDIEND EN ER IS NOG GEEN BIJBEHORENDE ACTIVITY
				// ***********************************************************************************************************
  				if (empty($quoteouder_activity_id) AND $quote_ouder AND $quote_ok_ouder != 'nee') {
  					if ($extdebug == 1) { watchdog('php', '<pre>--- 5.2 CREATE AN ACTIVITY QUOTE OUDER ALS ER EEN QUOTE IS MAAR NOG GEEN ACTIVITY</pre>', NULL, WATCHDOG_DEBUG); }
  					$results = \Civi\Api4\Activity::create()
  					->setCheckPermissions(false)
  					->addValue('source_contact_id', 1)
  					->addValue('target_contact_id', $contact_id)
  					->addValue('activity_type_id', 127)
  					->addValue('activity_date_time', $quote_datumact_ouder)
  					->addValue('subject', 'QUOTE toestemming ouder')
                                        ->addValue('status_id', 7) // initial status (draft = 7, afwachting = 9)
  					->setChain([
    					'name_me_0' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => $contact_id, 'record_type_id' => 3]], ],
    					'name_me_1' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => 1, 'record_type_id' => 2]], ]
  					])
  					->execute();
					foreach ($results as $result) {
  						// do something
  						if ($extdebug == 1) { watchdog('php', '<pre>QUOTE_ouder_api4_create_results:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  						if (empty($quoteouder_activity_id))		{ $quoteouder_activity_id		= $result['id']; }
						if (empty($quoteouder_activity_status))	{ $quoteouder_activity_status	= 1; }
						if ($extdebug == 1) { watchdog('php', '<pre>quoteouder_activity_id2:' . print_r($quoteouder_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
						if ($extdebug == 1) { watchdog('php', '<pre>quoteouder_activity_status2:' . print_r($quoteouder_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
				// ************************************************************************************************************
				// 5.3 CREATE AN ACTIVITY 'LEID' ALS QUOTE ouder IS INGEDIEND OF ONTVANGST BEVESTIGD EN ER IS NOG GEEN BIJBEHORENDE ACTIVITY
				// ************************************************************************************************************
				if (empty($quoteleid_activity_id) AND $quote_leid AND $quote_ok_leid != 'nee') {
					if ($extdebug == 1) { watchdog('php', '<pre>--- 5.3 CREATE AN ACTIVITY QUOTE LEID ALS ER EEN QUOTE IS MAAR NOG GEEN ACTIVITY</pre>', NULL, WATCHDOG_DEBUG); }
					$results = \Civi\Api4\Activity::create()
					->setCheckPermissions(false)
  					->addValue('source_contact_id', 1)
  					->addValue('target_contact_id', $contact_id)
  					->addValue('activity_type_id', 129)
  					->addValue('activity_date_time', $quote_datumact_leid)
  					->addValue('subject', 'QUOTE toestemming leiding')
                                        ->addValue('status_id', 7) // initial status (draft = 7, afwachting = 9)
  					->setChain([
    					'name_me_0' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => $contact_id, 'record_type_id' => 3]], ],
    					'name_me_1' => ['ActivityContact', 'create', ['values' => ['activity_id' => '$id', 'contact_id' => 1, 'record_type_id' => 2]], ]
  					])
  					->execute();
					foreach ($results as $result) {
  						// do something
  						if ($extdebug == 1) { watchdog('php', '<pre>QUOTE_leid_api4_create_results:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
  						if (empty($quoteleid_activity_id))		{ $quoteleid_activity_id			= $result['id']; }
						if (empty($quoteleid_activity_status))	{ $quoteleid_activity_status		= 1; }
						if ($extdebug == 1) { watchdog('php', '<pre>quoteleid_activity_id2:' . print_r($quoteleid_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
						if ($extdebug == 1) { watchdog('php', '<pre>quoteleid_activity_status2:' . print_r($quoteleid_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
			}

			if ($extproupdate == 1 AND (in_array($groupID, array("150")))) {
			// ************************************************************************************************************
			// 6. UPDATE ACTIVITIES
			// ************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 6. QUOTE ACTIVITIES [UPDATE] [groupID: '.$groupID.'] [op: '.$op.'] ###</pre>', NULL, WATCHDOG_DEBUG); }

				// ************************************************************************************************************
				// 6.1 BEPAAL (NIEUWE) STATUS ACTIVITEITEN
				if ($extdebug == 1) { watchdog('php', '<pre>--- 6.1 BEPAAL (NIEUWE) STATUS ACTIVITEITEN</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				if ($quote_datumok_deel)	{ $quote_actstatus_deel 	= "Completed"; 	} else { $quote_actstatus_deel 	= "Pending"; }
				if ($quote_datumok_ouder)	{ $quote_actstatus_ouder 	= "Completed"; 	} else { $quote_actstatus_ouder = "Pending"; }
				if ($quote_datumok_leid)	{ $quote_actstatus_leid 	= "Completed"; 	} else { $quote_actstatus_leid 	= "Pending"; }

				if ($quote_ok_deel == 'nee'){ $quote_actstatus_deel 	= "Cancelled"; }
				if ($quote_ok_ouder== 'nee'){ $quote_actstatus_ouder 	= "Cancelled"; }
				if ($quote_ok_leid == 'nee'){ $quote_actstatus_leid 	= "Cancelled"; }

				if ($quote_datumok_deel)	{ $quote_datumact_deel 		= $quote_datumact_deel;	 } else { $quote_datumact_deel 	= $quotedeel_activity_datetime;  }
				if ($quote_datumok_ouder)	{ $quote_datumact_ouder 	= $quote_datumact_ouder; } else { $quote_datumact_ouder	= $quoteouder_activity_datetime; }
				if ($quote_datumok_leid)	{ $quote_datumact_leid 		= $quote_datumact_leid;  } else { $quote_datumact_leid	= $quoteleid_activity_datetime;  }
				if ($extdebug == 1) { watchdog('php', '<pre>1. quote_actstatus_deel:' . print_r($quote_actstatus_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				if ($extdebug == 1) { watchdog('php', '<pre>2. quote_actstatus_ouder:' . print_r($quote_actstatus_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				if ($extdebug == 1) { watchdog('php', '<pre>3. quote_actstatus_leid:' . print_r($quote_actstatus_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				if ($extdebug == 1) { watchdog('php', '<pre>a. quote_datumact_deel:' . print_r($quote_datumact_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				if ($extdebug == 1) { watchdog('php', '<pre>b. quote_datumact_ouder:' . print_r($quote_datumact_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				if ($extdebug == 1) { watchdog('php', '<pre>c. quote_datumact_leid:' . print_r($quote_datumact_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				// 6.2 UPDATE ACTIVITY DEEL
				if ($extdebug == 1) { watchdog('php', '<pre>--- 6.2 UPDATE ACTIVITY DEEL</pre>', NULL, WATCHDOG_DEBUG); }
				// *****************************************************************************************************************
				if ($quotedeel_activity_id AND $quote_deel) {
  					$params_quote_activity_change_deel = [
  						'id'					=> $quotedeel_activity_id,
  						'activity_type_id'		=> "QUOTE_deel",
  						'subject' 				=> "QUOTE toestemming deelnemer",
  						'activity_date_time'	=> $quote_datumact_deel,
  						'status_id'				=> $quote_actstatus_deel,
  					];
  					if ($extdebug == 1) { watchdog('php', '<pre>$params_quote_activity_change_deel:' . print_r($params_quote_activity_change_deel, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					if ($extprowrite == 1)	{
						$result = civicrm_api3('Activity', 'create', $params_quote_activity_change_deel);
						if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_deel EXECUTED [groupID: '.$groupID.']</pre>', NULL, WATCHDOG_DEBUG); }
						#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_deel_result:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
				// ************************************************************************************************************
				// 6.2 UPDATE ACTIVITY OUDER
				if ($extdebug == 1) { watchdog('php', '<pre>--- 6.2 UPDATE ACTIVITY OUDER</pre>', NULL, WATCHDOG_DEBUG); }
				// ************************************************************************************************************
				if ($quoteouder_activity_id AND $quote_ouder) {
  					$params_quote_activity_change_ouder = [
  						'id'					=> $quoteouder_activity_id,
  						'activity_type_id'		=> "QUOTE_ouder",
  						'subject' 				=> "QUOTE toestemming ouder",
  						'activity_date_time'	=> $quote_datumact_ouder,
  						'status_id'				=> $quote_actstatus_ouder,
  					];
  					if ($extdebug == 1) { watchdog('php', '<pre>$params_quote_activity_change_ouder:' . print_r($params_quote_activity_change_ouder, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					if ($extprowrite == 1)	{
						$result = civicrm_api3('Activity', 'create', $params_quote_activity_change_ouder);
						if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_ouder EXECUTED [groupID: '.$groupID.']</pre>', NULL, WATCHDOG_DEBUG); }
						#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_ouder_result:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
				// *****************************************************************************************************************
				// 6.2 UPDATE ACTIVITY LEID
				if ($extdebug == 1) { watchdog('php', '<pre>--- 6.2 UPDATE ACTIVITY LEID</pre>', NULL, WATCHDOG_DEBUG); }
				// *****************************************************************************************************************
				if ($quoteleid_activity_id AND $quote_leid) {
  					$params_quote_activity_change_leid	 = [
  						'id'					=> $quoteleid_activity_id,
  						'activity_type_id'		=> "QUOTE_leid",
  						'subject' 				=> "QUOTE toestemming leiding",
  						'activity_date_time'	=> $quote_datumact_leid	,
  						'status_id'				=> $quote_actstatus_leid,
  					];
  					if ($extdebug == 1) { watchdog('php', '<pre>$params_quote_activity_change_leid:' . print_r($params_quote_activity_change_leid, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					if ($extprowrite == 1)	{
						$result = civicrm_api3('Activity', 'create', $params_quote_activity_change_leid);
						if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_leid EXECUTED [groupID: '.$groupID.']</pre>', NULL, WATCHDOG_DEBUG); }
						#if ($extdebug == 1) { watchdog('php', '<pre>params_quote_activity_change_leid_result:' . print_r($result, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
					}
				}
			}

			if ($extprowrite == 1 AND (in_array($groupID, array("150")))) {
			// *****************************************************************************************************************
			// 7. DELETE ACTIVITIES (indien ze waren aangemaakt maar QUOTE nog goed was - deze actie zou niet nodig hoeven zijn)
			// *****************************************************************************************************************
				if ($extdebug == 1) { watchdog('php', '<pre>### 7. QUOTE ACTIVITIES [DELETE] [groupID: '.$groupID.'] [op: '.$op.'] ###</pre>', NULL, WATCHDOG_DEBUG); }
				#if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_status:' . print_r($quotedeel_activity_status, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
				#if ($extdebug == 1) { watchdog('php', '<pre>quotedeel_activity_id:' . print_r($quotedeel_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
			    if (empty($quote_deel) 	AND $quotedeel_activity_id)	{
			    	$result = civicrm_api3('Activity', 'delete', array('id' => $quotedeel_activity_id,));
			    	if ($extdebug == 1) { watchdog('php', '<pre>7.1 ACTIVITY VERWIJDERD QUOTE_DEEL:' . print_r($quoteouder_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
			    }
			    if (empty($quote_ouder) AND $quoteouder_activity_id)	{
			    	$result = civicrm_api3('Activity', 'delete', array('id' => $quoteouder_activity_id,));
			    	if ($extdebug == 1) { watchdog('php', '<pre>7.2 ACTIVITY VERWIJDERD QUOTE_OUDER:' . print_r($quoteouder_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
			    }
			    if (empty($quote_leid) 	AND $quoteleid_activity_id)	{
			    	$result = civicrm_api3('Activity', 'delete', array('id' => $quoteleid_activity_id,));
			    	if ($extdebug == 1) { watchdog('php', '<pre>7.3 ACTIVITY VERWIJDERD QUOTE_LEID:' . print_r($quoteleid_activity_id, TRUE) . '</pre>', NULL, WATCHDOG_DEBUG); }
			    }
			}
			if ($extpro == 1 AND (in_array($groupID, array("150")))) {
			// ************************************************************************************************************
			// 8 GET ACTIVITIES MBT. QUOTE
			// ************************************************************************************************************
  			}
			if ($extdebug == 1) { watchdog('php', '<pre>*** END EXTENSION QUOTE [groupID: '.$groupID.'] [op: '.$op.'] [entityID: '.$entityID.'] [kampleider: '.$display_name.'] ***</pre>', NULL, WATCHDOG_DEBUG); }
	}
}

/**
 * Implementation of hook_civicrm_config
 */
function quote_civicrm_config(&$config) {
	_quote_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function quote_civicrm_xmlMenu(&$files) {
	_quote_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function quote_civicrm_install() {
	#CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, __DIR__ . '/sql/auto_install.sql');
	return _quote_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function quote_civicrm_uninstall() {
	#CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, __DIR__ . '/sql/auto_uninstall.sql');
	return _quote_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function quote_civicrm_enable() {
	return _quote_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function quote_civicrm_disable() {
	return _quote_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function quote_civicrm_managed(&$entities) {
	return _quote_civix_civicrm_managed($entities);
}

?>
