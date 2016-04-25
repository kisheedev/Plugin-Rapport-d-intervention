<?php
require('../lib/FPDF/fpdf.php');
include('../../../inc/includes.php');
class PDF extends FPDF
{
// En-tête
function Header()
{
    // Logo
    $this->Image('../pics/logo.png',15,6,30);
    // Police Arial gras 15
    $this->SetFont('Arial','B',15);
    // Décalage à droite
    //$this->Cell(80);
    // Titre
    $this->Cell(0,20,'Compte rendu d\'intervention',1,0,'C');
    // Saut de ligne
    $this->Ln(25);
}

// Pied de page
function Footer()
{
    // Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('Arial','I',8);
    // Numéro de page
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

function haut_ticket($pdf)
{
		global $DB;
		$pdf->setFillColor(180,180,180);
		$pdf->Ln(5);
		$id=$_GET["id"];
		$sql = "SELECT * FROM `glpi_tickets` WHERE id=$id";
		$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
		$row = $DB->fetch_assoc($res);	
			$titre=$row['name'];
			$description=$row['content'];
			$date_ouverture=$row['date'];
			$date_close=$row['solvedate'];
			$id_user=$row['users_id_recipient'];	
                        $id_entities=$row['entities_id'];
			
		ajout_ligne($pdf,190,6,$titre,'C',true,true);
		
		ajout_ligne($pdf,50,5,"N° du ticket associée :",'L',true,false);
		ajout_ligne($pdf,0,5,$id,'L',false,true);
		
		ajout_ligne($pdf,50,5,"Intervenant :",'L',true,false);
		ajout_ligne($pdf,0,5,get_user($id_user),'L',false,true);
		
		ajout_ligne($pdf,50,5,"Date d'intervention :",'L',true,false);	
		ajout_ligne($pdf,10,5,"Du :",'L',true,false);
		ajout_ligne($pdf,60,5,  convertit_date_FR($date_ouverture),'L',false,false);
		ajout_ligne($pdf,10,5,"Au :",'L',true,false);
		ajout_ligne($pdf,60,5,  convertit_date_FR($date_close),'L',false,true);
		
		$pdf->Ln(5);
		
                $sql = "SELECT * FROM `glpi_entities` WHERE id=$id_entities";
		$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
		$row = $DB->fetch_assoc($res);
                        $name=$row['name'];
                        $ville=$row['town'];
                        $pays=$row['country'];
                        $comment=$row['comment'];
                        
		ajout_ligne($pdf,50,5,"Nom de la societé :",'L',true,false);
		ajout_ligne($pdf,0,5,$name,'L',false,true);
		ajout_ligne($pdf,50,5,"Ville :",'L',true,false);
		ajout_ligne($pdf,0,5,$ville.', '.$pays,'L',false,true);
		ajout_ligne($pdf,50,5,"Résponsable :",'L',true,false);
		ajout_ligne($pdf,0,5,$comment,'L',false,true);
		
		$pdf->Ln(5);
		ajout_ligne($pdf,0,5,"Description :",'L',true,true);
		$pdf->MultiCell(0,5,utf8_decode($description),1,false);

		$pdf->Ln(10);
}
function get_user($id){
	$sql="SELECT realname,firstname FROM glpi_users WHERE id=$id";
	global $DB;
	$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
	$row=$DB->fetch_assoc($res);
	$realname=$row['realname'];
	$firstname=$row['firstname'];
	return "$realname $firstname";
}

function tache_ticket($pdf,$date,$time,$redacteur,$description)
{
	ajout_ligne($pdf,63,5,"Date :",'L',true,false);
	ajout_ligne($pdf,63,5,"Durée :",'L',true,false);
	ajout_ligne($pdf,64,5,"Rédacteur :",'L',true,true);
	ajout_ligne($pdf,63,5,  convertit_date_FR($date),'L',false,false);
	ajout_ligne($pdf,63,5,$time,'L',false,false);
	ajout_ligne($pdf,64,5,$redacteur,'L',false,true);
	ajout_ligne($pdf,0,5,"Description :",'L',true,true);
	$pdf->MultiCell(0,5,utf8_decode($description),1,false);
}

function affiche_toutes_les_taches($pdf){
	ajout_ligne($pdf,0,6,"Tâche(s) du ticket:",'C',true,true);
	
	global $DB;
	$id=$_GET["id"];
	$sql="SELECT * FROM glpi_tickettasks WHERE tickets_id=$id";
	$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
	while($row = $DB->fetch_assoc($res)){
		$date=$row['date'];
		$description=$row['content'];
		$datebegin=new DateTime($row['begin']);
		$dateend=new DateTime($row['end']);
		$time=$dateend->diff($datebegin);
		$user_id=$row['users_id'];
		tache_ticket($pdf,$date,$time->format('%d jours %hh %mmin'),get_user($user_id),$description);
	}	
	

}

function ajout_ligne($pdf,$larg,$haut,$text,$aligne,$b,$retourligne){
		$pdf->Cell($larg,$haut,utf8_decode($text),1,0,$aligne,$b);
                if ($retourligne == true) {
                    $pdf->Ln();
                }
}
function convertit_date_FR($date){
	if($date==null){
		return ' ';
	}
    $tab=explode(' ',$date);
    list($annee,$mois,$jour)= explode('-', $tab[0]);
    return $jour.'/'.$mois.'/'.$annee.' '.$tab[1];
}


// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('','',10);
haut_ticket($pdf);
affiche_toutes_les_taches($pdf);
$pdf->Output();
