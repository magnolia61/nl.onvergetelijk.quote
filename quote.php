<?php

/**
 * @file
 * Hoofdmodule voor de Quote extensie.
 * Zorgt voor de koppeling tussen CiviCRM hooks en de Processor logica.
 */

// Gebruik __DIR__ om inladen van de boilerplate te garanderen
// Dit voorkomt 'undefined function' errors bij Drush/Cron aanroepen
require_once __DIR__ . DIRECTORY_SEPARATOR . 'quote.civix.php';

use CRM_Quote_ExtensionUtil as E;

/**
 * Implements hook_civicrm_custom().
 * * Verwerkt wijzigingen in custom velden (Groep 150) naar activiteiten.
 */
function quote_civicrm_custom($op, $groupID, $entityID, &$params) {
  // ########################################################################
  // ### QUOTE [HOOK] 1.0 START TRIGGER VOOR PROMOTIE (GROEP 150)
  // ########################################################################
  
  // We initialiseren de PSR-4 Processor voor de inhoudelijke afhandeling
  $processor = new \Civi\Quote\Processor();
  $processor->handle($op, $groupID, $entityID, $params);
}

/**
 * ########################################################################
 * ### STANDAARD CIVIX HOOKS
 * ### Deze roepen de functies aan in quote.civix.php
 * ########################################################################
 */

function quote_civicrm_config(&$config) {
  if (function_exists('_quote_civix_civicrm_config')) {
    _quote_civix_civicrm_config($config);
  }
}

function quote_civicrm_xmlMenu(&$files) {
  if (function_exists('_quote_civix_civicrm_xmlMenu')) {
    _quote_civix_civicrm_xmlMenu($files);
  }
}

function quote_civicrm_install() {
  _quote_civix_civicrm_install();
}

function quote_civicrm_postInstall() {
  _quote_civix_civicrm_postInstall();
}

function quote_civicrm_uninstall() {
  _quote_civix_civicrm_uninstall();
}

function quote_civicrm_enable() {
  _quote_civix_civicrm_enable();
}

function quote_civicrm_disable() {
  _quote_civix_civicrm_disable();
}

function quote_civicrm_managed(&$entities) {
  _quote_civix_civicrm_managed($entities);
}
