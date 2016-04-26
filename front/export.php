<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include ("../../../inc/includes.php");
require '../inc/rapport_intervention.class.php';


$id=$_POST['id'];
$observation=$_POST['observation'];
$sig=$_POST['signature'];
$chemin = '../files/rapport_'.$id.'.pdf';
//echo $chemin;
$query="INSERT INTO glpi_plugin_rapportinter_rapport (id_ticket, Observation, Signature, PDF_chemin) VALUES('$id', '$observation', '$sig', '$chemin' )";
if($DB->query($query) or die("error creating glpi_plugin_example_data ". $DB->error())){
    $pdf=new PDF($id, $observation, $sig);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('','',10);
    $pdf->haut_ticket();
    $pdf->affiche_toutes_les_taches();
    $pdf->fin_de_page();
    $pdf->Output('F',$chemin);
}



