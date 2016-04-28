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
$exploded = explode(',', $sig, 2);
$encoded = $exploded[1];
$encoded=  htmlspecialchars_decode($encoded);
$encoded=  str_replace("\\\\\\", "", $encoded);

$res = $DB->query("SELECT COUNT(id_ticket) FROM glpi_plugin_rapportinter_rapport WHERE id_ticket = '$id'");
$row = $DB->fetch_assoc($res);
if($row['COUNT(id_ticket)']>= 1){
   echo 'Rapport déjà crée';
}else{
        $monfichier = fopen('../files/sig.svg', 'a+');
        fputs($monfichier, $encoded);
     
        $query="INSERT INTO glpi_plugin_rapportinter_rapport (id_ticket, Observation, Signature, PDF_chemin) VALUES('$id', '$observation', '$sig', '$chemin' )";
        if($DB->query($query) or die("error creating glpi_plugin_example_data ". $DB->error())){
            echo 'fait ...';
            $pdf=new PDF($id, $observation, $sig);
            $pdf->setHeaderMargin(10);
            $pdf->SetMargins(10, 30);
            $pdf->AddPage();
            $pdf->SetFont('','',10);
            $pdf->haut_ticket();
            $pdf->affiche_toutes_les_taches();
            $pdf->fin_de_page();
            $pdf->Output($chemin,'F');
            fclose($monfichier);
            unlink('../files/sig.svg');
            
        }
}
?>



