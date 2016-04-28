<?php
require('../lib/tcpdf/tcpdf_import.php');
include('../../../inc/includes.php');
class PDF extends TCPDF
{
// En-tête
function Header()
{
    // Logo
    $this->Image('../pics/logo.png',15,5,30,30,'PNG');
    // Police Arial gras 15
    $this->SetFont('helvetica','B',15);
    // Titre
    $this->Cell(0,20,'Compte rendu d\'intervention',1,1,'C');

}

// Pied de page
function Footer()
{
    // Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('helvetica','I',8);
    // Numéro de page
    $this->Cell(0,10,'Page '.$this->PageNo().'/'.$this->numpages,0,0,'C');
}


function haut_ticket()
{
		global $DB;
                $id=$_GET['id'];
		$this->setFillColor(180,180,180);
		$this->Ln(5);
		$sql = "SELECT * FROM `glpi_tickets` WHERE id=".$id;
		$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
		$row = $DB->fetch_assoc($res);	
			$titre=$row['name'];
			$description=$row['content'];
			$date_ouverture=$row['date'];
			$date_close=$row['solvedate'];
			$id_user=$row['users_id_recipient'];	
                        $id_entities=$row['entities_id'];
			
		$this->ajout_ligne(190,6,$titre,'C',true,true);
		
		$this->ajout_ligne(50,5,"N° du ticket associée :",'L',true,false);
		$this->ajout_ligne(0,5,$id,'L',false,true);
		
		$this->ajout_ligne(50,5,"Intervenant :",'L',true,false);
		$this->ajout_ligne(0,5,$this->get_user($id_user),'L',false,true);
		
		$this->ajout_ligne(50,5,"Date d'intervention :",'L',true,false);	
		$this->ajout_ligne(10,5,"Du :",'L',true,false);
		$this->ajout_ligne(60,5,  $this->convertit_date_FR($date_ouverture),'L',false,false);
		$this->ajout_ligne(10,5,"Au :",'L',true,false);
		$this->ajout_ligne(60,5,  $this->convertit_date_FR($date_close),'L',false,true);
		
		$this->Ln(5);
		
                $sql = "SELECT * FROM `glpi_entities` WHERE id=$id_entities";
		$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
		$row = $DB->fetch_assoc($res);
                        $name=$row['name'];
                        $ville=$row['town'];
                        $pays=$row['country'];
                        $comment=$row['comment'];
                        
		$this->ajout_ligne(50,5,"Nom de la societé :",'L',true,false);
		$this->ajout_ligne(0,5,$name,'L',false,true);
		$this->ajout_ligne(50,5,"Ville :",'L',true,false);
		$this->ajout_ligne(0,5,$ville.', '.$pays,'L',false,true);
		$this->ajout_ligne(50,5,"Résponsable :",'L',true,false);
		$this->ajout_ligne(0,5,$comment,'L',false,true);
		
		$this->Ln(5);
		$this->ajout_ligne(0,5,"Description :",'L',true,true);
                $description=  htmlspecialchars_decode($description);
		$this->MultiCell(0,5,$description,1,false);

		$this->Ln(5);
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

function tache_ticket($date,$time,$redacteur,$description)
{
	$this->ajout_ligne(15,5,"Date :",'L',true,false);
        $this->ajout_ligne(40,5, $this->convertit_date_FR($date),'L',false,false);
	$this->ajout_ligne(16,5,"Durée :",'L',true,false);
        $this->ajout_ligne(35,5,$time,'L',false,false);
	$this->ajout_ligne(23,5,"Rédacteur :",'L',true,false);
	$this->ajout_ligne(61,5,$redacteur,'L',false,true);
    
        $description=  htmlspecialchars_decode($description);
	$this->MultiCell(0,5,"Description : ".$description,1,false);
}

function affiche_toutes_les_taches(){
        $this->ajout_ligne(0,6,"Tâche(s) du ticket:",'C',true,true);
	$id=$_GET['id'];
	global $DB;
	$sql="SELECT * FROM glpi_tickettasks WHERE tickets_id=$id";
	$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
	while($row = $DB->fetch_assoc($res)){
		$date=$row['date'];
		$description=$row['content'];
		$datebegin=new DateTime($row['begin']);
		$dateend=new DateTime($row['end']);
		$time=$dateend->diff($datebegin);
		$user_id=$row['users_id'];
		$this->tache_ticket($date,$time->format('%d jours %hh %mmin'),  $this->get_user($user_id),$description);
	}	
	

}

function ajout_ligne($larg,$haut,$text,$aligne,$b,$retourligne){
		$this->Cell($larg,$haut,$text,1,0,$aligne,$b);
		if ($retourligne == true) {
                    $this->Ln();
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

}


// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->setHeaderMargin(10);
$pdf->SetMargins(10, 30);
$pdf->AddPage();
$pdf->SetFont('','',10);
$pdf->haut_ticket();
$pdf->affiche_toutes_les_taches();
ob_end_clean();
$pdf->Output();
?>