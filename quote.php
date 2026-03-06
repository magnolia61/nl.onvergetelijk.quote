<?php

require_once 'quote.civix.php';
use CRM_Quote_ExtensionUtil as E;

/**
 * Implements hook_civicrm_custom().
 *
 * Verwerking van Quotes (Deelnemer, Ouder, Leiding) naar activiteiten.
 */
function quote_civicrm_custom($op, $groupID, $entityID, &$params) {
  // We verhuizen de logica naar een aparte class voor rust in de main file
  $processor = new \Civi\Quote\Processor();
  $processor->handleCustomFields($op, $groupID, $entityID, $params);
}

/**
 * De standaard civix hooks hieronder laten staan...
 */
function quote_civicrm_config(&$config) { _quote_civix_civicrm_config($config); }
function quote_civicrm_xmlMenu(&$files) { _quote_civix_civicrm_xmlMenu($files); }
function quote_civicrm_install() { _quote_civix_civicrm_install(); }
function quote_civicrm_postInstall() { _quote_civix_civicrm_postInstall(); }
function quote_civicrm_uninstall() { _quote_civix_civicrm_uninstall(); }
function quote_civicrm_enable() { _quote_civix_civicrm_enable(); }
function quote_civicrm_disable() { _quote_civix_civicrm_disable(); }
