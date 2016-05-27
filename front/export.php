<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include ("../../../inc/includes.php");
require '../inc/rapport_intervention.class.php';
$d=date("dmYHis");
$id=$_POST['id'];
$observation=$_POST['observation'];
$sig=$_POST['signature'];
$chemin = GLPI_ROOT.'/files/PDF/rapport_'.$id.'_'.$d.'.pdf';
$chemin=  str_replace('\\', '/',$chemin);
$exploded = explode(',', $sig, 2);
$encoded = $exploded[1];
$encoded=  htmlspecialchars_decode($encoded);
$encoded=  str_replace("\\\\\\", "", $encoded);
$date=date("d/m/Y \n à \n H:i");


$realtime=  $_POST['realtime'];
$last_task= $_POST['last_task'];


        $monfichier = fopen(GLPI_ROOT.'/files/_tmp/sig.svg', 'a+');
        fputs($monfichier, $encoded);
        $filename='rapport_'.$id.'_'.$d.'.pdf';
        $name='RDI_'.$id;
        $filepath='PDF/rapport_'.$id.'_'.$d.'.pdf';
        $inter=$_SESSION['glpiID'];
        $query="INSERT INTO glpi_documents (name, filename, filepath, tickets_id, mime, date_mod, users_id) VALUES('$name', '$filename', '$filepath', '$id','application/pdf',NOW(),$inter )";
        
        if($DB->query($query) or die("error". $DB->error())){
            
            $sql="INSERT INTO `glpi_plugin_rapportinter_rdidetails`(ticket_id,realtime,date,technicians,name,filename) VALUES ('$id', '$realtime',NOW(),'$inter','$name','$filename') ";
            $DB->query($sql) or die($DB->error());
            //echo $realtime;
            $pdf=new PDF($id, $observation, $sig,$date,$realtime,$last_task,$name);
            $pdf->setHeaderMargin(10);
            $pdf->SetMargins(10, 30);
            $pdf->AddPage();
            $pdf->SetFont('','',10);
            $pdf->haut_ticket();
            $pdf->affiche_toutes_les_taches();
            $pdf->fin_de_page();
            $pdf->Output($chemin,'F');
			ob_end_clean();
            fclose($monfichier);
            unlink(GLPI_ROOT.'/files/_tmp/sig.svg');

            
            $sql="UPDATE `glpi_tickets` SET `itilcategories_id`=1 WHERE id=$id";
            $DB->query($sql) or die ($DB->error());
            header ("Location: $_SERVER[HTTP_REFERER]" );
            
}





?>



