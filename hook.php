<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Fonction d'installation du plugin
 * @return boolean
 */
function plugin_rapportinter_install() 
    {
    global $DB;
    $migration = new Migration(100);    
    // Création de la table uniquement lors de la première installation
    if (!TableExists("glpi_plugin_rapportinter_profiles")) 
        {
        // requête de création de la table    
        $query = "CREATE TABLE `glpi_plugin_rapportinter_profiles` (
                    `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
                    `right` char(1) collate utf8_unicode_ci default NULL,
                    PRIMARY KEY  (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $DB->query($query) or die($DB->error());

        //création du premier accès nécessaire lors de l'installation du plugin
        $id = $_SESSION['glpiactiveprofile']['id'];
        $query = "INSERT INTO glpi_plugin_rapportinter_profiles VALUES ('$id','w')";

        $DB->query($query) or die($DB->error());
        $migration->executeMigration();

        //creation du premier accès nécessaire lors de l'installation du plugin
        include_once(GLPI_ROOT."/plugins/rapportinter/inc/profile.class.php");
        PluginRapportinterProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
        }   
        
    // Création de la table uniquement lors de la première installation
    if (!TableExists("glpi_plugin_rapportinter_rapport")) 
        {
        // Création de la table des rapports
        $query = "CREATE TABLE `glpi_plugin_rapportinter_rapport` (
        `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `id_ticket` int(11) NOT NULL,
        `temps_total` double(3,2) NOT NULL,
        `PDF_chemin` TEXT NOT NULL
        )ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->query($query) or die($DB->error());
        }      
    return true ;
    }
    
    /**
 * Fonction de désinstallation du plugin
 * @return boolean
 */
function plugin_rapportinter_uninstall() 
    {
    global $DB;

    $tables = array("glpi_plugin_rapportinter_profiles", "glpi_plugin_rapportinter_rapport");

    foreach($tables as $table) 
        {$DB->query("DROP TABLE IF EXISTS `$table`;");}
    return true;
    }    
?>
