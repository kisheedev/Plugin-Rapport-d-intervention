<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function plugin_init_rapportinter() 
    {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['rapportinter'] = true;

   
    Plugin::registerClass('PluginRapportinterTicket' , array('addtabon' => 'Ticket'));     //LIGNE POUR ONGLET TICKET 
    Plugin::registerClass('PluginRapportinterProfile', array('addtabon' =>'Profile'));     //LIGNE POUR ONGLET PROFILE
    $PLUGIN_HOOKS['change_profile']['rapportinter'] = array('PluginRapportinterProfile','changeProfile');

    }   
    
    
function plugin_version_rapportinter() 
    {
    return array('name'           => "Rapport d'intervention",
                 'version'        => '1.0.0',
                 'author'         => 'Hichem MELIK',
                 'license'        => 'GPLv3+',
                 'minGlpiVersion' => '0.90');
    }
    
 /**
 * Fonction de vérification des prérequis
 * @return boolean
 */
function plugin_rapportinter_check_prerequisites() 
    {
    if (GLPI_VERSION >= 0.90)
        return true;
    echo "A besoin de la version 0.90 au minimum";
    return false; 
    }
    
    /**
 * Fonction de vérification de la configuration initiale
 * @param type $verbose
 * @return boolean
 */
function plugin_rapportinter_check_config($verbose=false) 
    {
    if (true) 
        { // Your configuration check
        return true;
        }
    if ($verbose) 
        {
        echo 'Installed / not configured';
        }
    return false;
    }


?>    