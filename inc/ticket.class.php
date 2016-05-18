<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PluginpluginrsmTicket
 *
 * @author h.melik
 */

class PluginRapportinterTicket extends CommonDBTM
    {
    /**
     * Récupère le nom de l'onglet si l'utilisateur est autorisé
     * @param CommonGLPI $item
     * @param type $withtemplate
     * @return boolean|string
     */
            
    static function get_user($id){
	$sql="SELECT realname,firstname FROM glpi_users WHERE id=$id";
	global $DB;
	$res=$DB->query($sql) or die ("error creating glpi_plugin_example_data ". $DB->error());
	$row=$DB->fetch_assoc($res);
	$realname=$row['realname'];
	$firstname=$row['firstname'];
	return "$realname $firstname";
    }
    
   static function get_last_task($id){
        global $DB;
        $sql="SELECT `tache_deja_fait` FROM `glpi_plugin_rapportinter_rdidetails` WHERE ticket_id=$id ORDER BY glpi_plugin_rapportinter_rdidetails.date DESC";
        $res=$DB->query($sql) or die($DB->error());
        if($DB->numrows($res)>0){
            $row=$DB->fetch_assoc($res);
            return $row['tache_deja_fait'];
        }
        else 
            return 0;
    }
    
    static function get_realtime($id){
        global $DB;
        $last_task=  self::get_last_task($id);
        $sql="SELECT SUM(actiontime) AS realtime FROM `glpi_tickettasks` WHERE `tickets_id`=$id AND `id`>$last_task";
        $res=$DB->query($sql) or die ($DB->error());
        $row=$DB->fetch_assoc($res);
        if($row['realtime']==NULL)
            return 0.25;
        else
            return self::temps_passe ($row['realtime']);
    }
    
    static function can_create_report($id){
        global $DB;
        $last_task=  self::get_last_task($id);
        $sql="SELECT SUM(actiontime) AS realtime FROM `glpi_tickettasks` WHERE `tickets_id`=$id AND `id`>$last_task";
        $res=$DB->query($sql) or die ($DB->error());
        $row=$DB->fetch_assoc($res);
        if($row['realtime']==NULL)
            return FALSE;
        else            
            return true;
        
        }
            
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) 
        {
        //if (!isset($withtemplate) || empty($withtemplate)){
            if(PluginRapportinterProfile::PeutVoir())
                {return "Rapport d'intervention";}                
           // }
        }

    /**
     * Gère ce qui doit être affiché en accédant à l'onglet
     * @param CommonGLPI $item
     * @param type $tabnum
     * @param type $withtemplate
     * @return boolean
     */        
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) 
        {
        if ($item->getType() == 'Ticket'){
                global $DB;
               
                $id=$_GET['id'];
                $sql="SELECT COUNT(*) FROM glpi_documents WHERE tickets_id=$id AND mime='application/pdf' AND name='RDI_$id'";
                $res = $DB->query($sql) or die($DB->error());
                $row = $DB->fetch_assoc($res);
                if($row['COUNT(*)']>=1){
                    $sql="SELECT DISTINCT glpi_documents.id AS docid,"
                            ."glpi_plugin_rapportinter_rdidetails.date AS date, "
                            ."glpi_plugin_rapportinter_rdidetails.technicians AS user, "
                            ."glpi_plugin_rapportinter_rdidetails.realtime AS realtime "
                            ."FROM glpi_documents INNER JOIN glpi_plugin_rapportinter_rdidetails ON glpi_documents.filename=glpi_plugin_rapportinter_rdidetails.filename WHERE tickets_id=$id AND mime='application/pdf'  ORDER BY glpi_plugin_rapportinter_rdidetails.date ASC ";
                        $res=$DB->query($sql) or die ($DB->error());
                        $somme=0;
                        echo "<table id='tabPlugin'>
                                <tr>
                                    <th>Date</th>
                                    <th>Temps passé (trajet inclus)</th>
                                    <th>Intervenants</th>
                                    <th>Fichier</th>
                                </tr>";
                        while($row=$DB->fetch_assoc($res)){
                            $docid=$row['docid'];
                        echo    '<tr>
                                    <td>'.$row['date'].'</td>
                                    <td>'.$row['realtime'].'</td>';
                                    $somme+=$row['realtime'];
                        echo       '<td>'.self::get_user($row['user']).'</td>
                                    <td>'."<a href='' target='popup' onclick=window.open('../front/document.send.php?docid=$docid','name',width=600,height=400)><img src='../plugins/rapportinter/pics/pdf-dist.png'> Rapport</a></td>
                                </tr>";
                        }
                        echo "<tr id='total'>
                                <td >Total :</td>
                                <td>$somme</td>
                             </tr>";   
                        echo "</table>";
                        echo "<link rel='stylesheet' type='text/css' href='../plugins/rapportinter/lib/table.css' /> ";


                }
                if(PluginRapportinterProfile::PeutCree() && self::can_create_report($id)){
                    $last_task=self::get_last_task($id);
                    $realtime=self::get_realtime($id);

                    
                    echo"                          
                            <form method='POST' action='../plugins/rapportinter/front/export.php'> 
                                <input id='id_ticket' type='hidden' name='id'  value='$id' />
                                <input id='sigdata'   type='hidden' name='signature'  value=''/>
                                <input id='realtime' type='hidden' name='realtime' value='$realtime'/>
                                <input id='last_task' type='hidden' name='last_task' value='$last_task'/>

                                <script src='https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js'></script>
                                <p>Prévisualisation du rapport:</p>
                                <iframe src='../plugins/rapportinter/inc/rapport_previsu.class.php?id=$id&last_task=$last_task&realtime=$realtime' width='80%'; height='1000' align='middle'></iframe>
                                <p>Observation(s):</p>
                                <textarea name='observation' style='width:80%; height:100px;'></textarea>      
                                <p>Signature :</p>
                                <p style='margin:0px; padding-right:0px;'><span id='clear' style='border:1px solid black; cursor:pointer; background-color:#FEC95C; padding:5px;'>effacer</span></p>
                                <script src='../plugins/rapportinter/lib/jSignature/jSignature.min.js'></script>
                                <div id='signature' style='border:solid gray 1px; width:80%; margin:auto;'></div>
                                <script>
                                    $(document).ready(function() {
                                        $('#signature').jSignature();
                                        $('#clear').click(function(){
                                            $('#signature').jSignature('clear');
                                        });
                                        $('button').click(function(e){
                                            if( $('#signature').jSignature('getData', 'native').length == 0) {
                                                e.preventDefault();
                                                alert('Veuillez entrez une signature..');
                                            }
                                            else {
                                                var sigData=$('#signature').jSignature('getData','svg');
                                                $('#sigdata').val(sigData);
                                            }
                                        });                                   
                                    });
                                </script>
                                <button type='submit' style='background-color:#FEC95C; color:#8f5a0a; padding:5px; margin:10px; font:bold 12px Arial, Helvetica' >Générer le rapport</button>";
                                Html::closeForm();
                }
                else 
                    echo "<p>Pas de nouvelle tâche(s) depuis le dernier rapport.</p>";
            
        }
        return true;
        }
        
        
static function temps_passe($time)
{
    date_default_timezone_set('UTC');
    $t=date('H', $time);
    $i=  date('i',$time);
    if($i>1)
        $t=$t+1;
    $t=$t/2;
    $t=$t*0.25;
    $t=$t-0.01;
   
    
    $t=  self::arrondi($t);
    if($t>=0.25)
        return $t;
    else
        return 0.25;
}
static function arrondi($nombre,$niveau = 1, $mode =1 )
{
    $paliers = array();	
    $paliers[]=0;
    $nb_valeurs = 4*$niveau;
    $base = 1/$nb_valeurs;
    for ($i = 0; $i <= $nb_valeurs; $i++)
    {
        $paliers[] = $base*$i;	
    }
    $nb_paliers = count($paliers);
    $entier = intval($nombre);
    $decimal = $nombre - $entier;
    $ajustement = 0;
    $i =1;
    $trouve = false;
    while (($trouve === false) and ($i < $nb_paliers))
    {
        $v_min = $paliers[$i];
        $v_max = $paliers[$i+1];
        if (($decimal >= $v_min) and ($decimal < $v_max))
        {
            switch ($mode)
            {
            case 1:
            $ajustement = $paliers[$i+1];
            break;
            case 2:
            $ajustement = $paliers[$i];	
            break;
            case 3:
            $ajustement = ($paliers[$i]+ $paliers[$i+1]) / 2;	
            break;
            }
            $trouve = true;
        }
        $i++;
    }	

    return $entier + $ajustement;	
}

}
