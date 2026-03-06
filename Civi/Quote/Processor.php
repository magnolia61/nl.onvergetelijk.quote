<?php

namespace Civi\Quote;

class Processor {

  public function handleCustomFields($op, $groupID, $entityID, &$params) {
    $extdebug = 1;

    // ########################################################################
    // ### 1. FILTER: ALLEEN GROEP 150 (PROMOTIE)
    // ########################################################################
    if ($groupID != 150 || !in_array($op, ['create', 'edit'])) {
      return;
    }

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### QUOTE PROCESSOR [START] groupID: $groupID | op: $op", "[QUOTE]");
    wachthond($extdebug, 2, "########################################################################");

    $todaytime     = date("H:i:s");
    $todaydatetime = date("Y-m-d H:i:s");

    // ########################################################################
    // ### 2. GET CONTACT & CUSTOM DATA (API4)
    // ########################################################################
    $contact = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id', 'first_name', 'display_name')
      // Custom velden ophalen (veldnamen mappen aan jouw ID's)
      ->addSelect('custom_631', 'custom_632', 'custom_633')   // Quotes
      ->addSelect('custom_648', 'custom_642', 'custom_644')   // OK status
      ->addSelect('custom_1091', 'custom_1092', 'custom_1090') // Data
      ->addWhere('id', '=', $entityID)
      ->execute()
      ->first();

    if (!$contact) {
      wachthond($extdebug, 1, "EXIT: Geen contact gevonden voor ID $entityID", "[ERR]");
      return;
    }

    $contactId    = $contact['id'];
    $quote_deel   = $contact['custom_631']  ?? NULL;
    $quote_ouder  = $contact['custom_632']  ?? NULL;
    $quote_leid   = $contact['custom_633']  ?? NULL;
    
    $ok_deel      = $contact['custom_648']  ?? NULL;
    $ok_ouder     = $contact['custom_642']  ?? NULL;
    $ok_leid      = $contact['custom_644']  ?? NULL;

    $datum_deel   = !empty($contact['custom_1091']) ? date('Y-m-d', strtotime($contact['custom_1091'])) : NULL;
    $datum_ouder  = !empty($contact['custom_1092']) ? date('Y-m-d', strtotime($contact['custom_1092'])) : NULL;
    $datum_leid   = !empty($contact['custom_1090']) ? date('Y-m-d', strtotime($contact['custom_1090'])) : NULL;

    // ########################################################################
    // ### 3. ACTIVITEITEN BEHEREN (DEEL / OUDER / LEID)
    // ########################################################################
    
    $mapping = [
      'deel'  => ['type' => 126, 'subject' => 'QUOTE toestemming deelnemer', 'val' => $quote_deel,  'ok' => $ok_deel,  'date' => $datum_deel],
      'ouder' => ['type' => 127, 'subject' => 'QUOTE toestemming ouder',      'val' => $quote_ouder, 'ok' => $ok_ouder, 'date' => $datum_ouder],
      'leid'  => ['type' => 129, 'subject' => 'QUOTE toestemming leiding',    'val' => $quote_leid,  'ok' => $ok_leid,  'date' => $datum_leid],
    ];

    foreach ($mapping as $key => $m) {
      $existing = \Civi\Api4\Activity::get(FALSE)
        ->addSelect('id', 'activity_date_time', 'status_id')
        ->addWhere('target_contact_id', 'CONTAINS', $contactId)
        ->addWhere('activity_type_id', '=', $m['type'])
        ->setLimit(1)
        ->execute()
        ->first();

      // --- CREATE OF UPDATE ---
      if ($m['val'] && $m['ok'] != 'nee') {
        $actStatus = $m['date'] ? 2 : 1; // 2=Completed, 1=Scheduled (Pending)
        $actDate   = $m['date'] ? "$m[date] $todaytime" : $todaydatetime;

        if (!$existing) {
          wachthond($extdebug, 1, "Nieuwe activiteit aanmaken voor: $key", "[CREATE]");
          \Civi\Api4\Activity::create(FALSE)
            ->addValue('source_contact_id', 1) // Domeinbeheerder / System
            ->addValue('target_contact_id', [$contactId])
            ->addValue('activity_type_id', $m['type'])
            ->addValue('subject', $m['subject'])
            ->addValue('activity_date_time', $actDate)
            ->addValue('status_id', $actStatus)
            ->execute();
        } else {
          wachthond($extdebug, 1, "Bestaande activiteit updaten voor: $key (ID: " . $existing['id'] . ")", "[UPDATE]");
          \Civi\Api4\Activity::update(FALSE)
            ->addWhere('id', '=', $existing['id'])
            ->addValue('status_id', $actStatus)
            ->addValue('activity_date_time', $actDate)
            ->execute();
        }
      } 
      // --- DELETE INDIEN LEEG ---
      elseif (empty($m['val']) && $existing) {
        wachthond($extdebug, 1, "Activiteit verwijderen (waarde leeg): $key", "[DELETE]");
        \Civi\Api4\Activity::delete(FALSE)->addWhere('id', '=', $existing['id'])->execute();
      }
    }

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### QUOTE PROCESSOR [END] contact: " . $contact['display_name'], "[FINISH]");
    wachthond($extdebug, 2, "########################################################################");
  }
}
