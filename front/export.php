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

        $monfichier = fopen(GLPI_ROOT.'/files/_tmp/sig.svg', 'a+');
        fputs($monfichier, $encoded);
        $filename='rapport_'.$id.'_'.$d.'.pdf';
        $name='RDI_'.$id.'_'.$d;
        $filepath='PDF/rapport_'.$id.'_'.$d.'.pdf';
        $inter=$_SESSION['glpiID'];
        $query="INSERT INTO glpi_documents (name, filename, filepath, tickets_id, mime, date_mod, users_id) VALUES('$name', '$filename', '$filepath', '$id','application/pdf',NOW(),$inter )";
        
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
            unlink(GLPI_ROOT.'/files/_tmp/sig.svg');
            
            
            ini_set("SMTP","172.30.2.3"); 
            $sql="SELECT name FROM glpi_users WHERE id=(SELECT users_id FROM `glpi_tickets_users` WHERE `tickets_id`=$id AND type=1)";
            $res=$DB->query($sql) or die($DB->error());
            $row=$DB->fetch_assoc($res);
            $mail_to = $row['name']; //Destinataire  
            //echo $mail_to;
            $from_mail = $_SESSION['glpiname']; //Expediteur  
            $from_name = "RSM-Consulting"; //Votre nom, ou nom du site  
            $reply_to = "helpdesk@rsm-c.com"; //Adresse de réponse  
            ini_set("sendmail_from",$from_mail);
            $subject = "Rapport d'intervention $id";      
            $path = GLPI_ROOT."/files/PDF/";  
            $typepiecejointe = filetype($path.$filename);  
            $data = chunk_split( base64_encode(file_get_contents($path.$filename)) );  
            //Génération du séparateur  
            
            $boundary = md5(uniqid(time()));  
            $entete = "From: $from_mail \n";  
            $entete .= "Reply-to: $reply_to \n";  
            $entete .= "X-Priority: 1 \n";  
            $entete .= "MIME-Version: 1.0 \n";  
            $entete .= "Content-Type: multipart/mixed; boundary=\"$boundary\" \n";  
            $entete .= " \n";  
            $message  = "--$boundary \n";  
            $message .= "Content-Type: text/html; charset=\"iso-8859-1\" \n";  
            $message .= "Content-Transfer-Encoding:8bit \n";  
            $message .= "\n";  
            $message .= "Bonjour,  
            Veuillez trouver ci-joint le rapport d'intervention.
             Cordialement";  
            $message .= "\n";  
            $message .= "--$boundary \n";  
            $message .= "Content-Type: $typepiecejointe; name=\"$filename\" \n";  
            $message .= "Content-Transfer-Encoding: base64 \n";  
            $message .= "Content-Disposition: attachment; filename=\"$filename\" \n";  
            $message .= "\n";  
            $message .= $data."\n";  
            $message .= "\n";  
            $message .= "--".$boundary."--";  
            mail($mail_to, $subject, $message, $entete); 

            header('Location:http://support.rsm-c.com/front/ticket.form.php?id='.$id);
            
}

?>



