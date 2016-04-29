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
$chemin = GLPI_ROOT.'/files/PDF/rapport_'.$id.'.pdf';
$chemin=  str_replace('\\', '/',$chemin);
$exploded = explode(',', $sig, 2);
$encoded = $exploded[1];
$encoded=  htmlspecialchars_decode($encoded);
$encoded=  str_replace("\\\\\\", "", $encoded);
$date=date("d/m/Y \n à \n H:i");

$res = $DB->query("SELECT COUNT(id_ticket) FROM glpi_plugin_rapportinter_rapport WHERE id_ticket = $id");
//$row = $DB->fetch_assoc($res);
if($res['COUNT(id_ticket)']>= 1){
   echo 'Rapport déjà crée';
}else{
        $monfichier = fopen(GLPI_ROOT.'/files/PDF/sig.svg', 'a+');
        fputs($monfichier, $encoded);
        $filename="rapport_$id.pdf";
        $name="RDI_$id";
        $d=date();
        $filepath='PDF/rapport_'.$id.'.pdf';
        $query="INSERT INTO glpi_documents (name, filename, filepath, tickets_id, mime, date_mod) VALUES('$name', '$filename', '$filepath', '$id','application/pdf',NOW() )";
        if($DB->query($query) or die("error". $DB->error())){

            $pdf=new PDF($id, $observation, $sig,$date);
            $pdf->setHeaderMargin(10);
            $pdf->SetMargins(10, 30);
            $pdf->AddPage();
            $pdf->SetFont('','',10);
            $pdf->haut_ticket();
            $pdf->affiche_toutes_les_taches();
            $pdf->fin_de_page();
            $pdf->Output($chemin,'F');
            fclose($monfichier);
            unlink(GLPI_ROOT.'/files/PDF/sig.svg');
            header('Location:http://support.rsm-c.com/front/ticket.form.php?id='.$id);
           // mail("hichem.melik@gmail.com","test" ,"test");
            
        }
}
?>



