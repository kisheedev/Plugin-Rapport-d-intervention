<?php

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

//TRANS: The name of the report =Rapport d'intervention
$report = new PluginReportsAutoReport(__('rapportintervention_report_title', 'reports'));

new PluginReportsDateIntervalCriteria($report, "`glpi_plugin_rapportinter_rdidetails`.`date`");
new PluginReportsUserCriteria($report,"technicians");

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $cols = array(
		new PluginReportsColumn('entities', __('Entité')),
		new PluginReportsColumnDateTime('date', __('Date')),
		new PluginReportsColumn('technicians', __('Intervenants')),
		new PluginReportsColumn('realtime', __('Temps passé')),
		new PluginReportsColumn('ticket_id', __('ID du Ticket')),
		new PluginReportsColumnLink('docid', __('PDF'),'document'),	  
   );

   $report->setColumns($cols);


   $sql = "SELECT  
				   `glpi_entities`.`name` AS entities, 
				   `glpi_plugin_rapportinter_rdidetails`.`date` AS date,
				    concat(`glpi_users`.`firstname`,' ',`glpi_users`.`realname`) AS technicians,
				   `glpi_plugin_rapportinter_rdidetails`.`realtime` AS realtime, 
				   `glpi_plugin_rapportinter_rdidetails`.`ticket_id` AS ticket_id, 
				   `glpi_documents`.`id` AS docid 
	FROM glpi_plugin_rapportinter_rdidetails
	LEFT JOIN `glpi_tickets` ON (`glpi_tickets`.`id`=`glpi_plugin_rapportinter_rdidetails`.`ticket_id`)
	LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_tickets`.`entities_id`)
	LEFT JOIN `glpi_users` ON (`glpi_users`.`id`=`glpi_plugin_rapportinter_rdidetails`.`technicians`)
	LEFT JOIN `glpi_documents` ON (`glpi_plugin_rapportinter_rdidetails`.`filename`=`glpi_documents`.`filename`) "
   .$report->addSqlCriteriasRestriction("WHERE")."
   ORDER BY `glpi_plugin_rapportinter_rdidetails`.`date`";

   $report->setSqlRequest($sql);
   $report->execute();
}

   Html::footer();
