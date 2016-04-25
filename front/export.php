<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require '../inc/rapport_intervention.class.php';

$id=$_GET['id'];
$observation=$_GET['observation'];
$sig=$_GET['signature'];

$pdf=new PDF($id, $observation, $sig);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('','',10);
$pdf->haut_ticket();
$pdf->affiche_toutes_les_taches();
$pdf->fin_de_page();
$pdf->Output();

