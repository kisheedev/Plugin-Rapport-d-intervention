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
                $sql="SELECT COUNT(*) FROM glpi_documents WHERE tickets_id=$id AND mime='application/pdf'";
                $res = $DB->query($sql) or die($DB->error());
                $row = $DB->fetch_assoc($res);	
                if($row['COUNT(*)']>=1){
                    $sql="SELECT glpi_documents.id AS docid,"
                            ."glpi_tickets.entities_id AS entities,"
                            ."glpi_documents.date_mod AS date, "
                            ."glpi_documents.users_id AS user "
                            ."FROM glpi_documents INNER JOIN glpi_tickets ON glpi_documents.tickets_id=glpi_tickets.id WHERE tickets_id=$id AND mime='application/pdf'  ORDER BY glpi_documents.date_mod ASC ";
                        $res=$DB->query($sql);
                        echo "<table id='tabPlugin'>
                                <tr>
                                    <th>Date</th>
                                    <th>Intervenants</th>
                                    <th>Fichier</th>
                                </tr>";
                        while($row=$DB->fetch_assoc($res)){
                            $docid=$row['docid'];
                        echo    '<tr>
                                    <td>'.$row['date'].'</td>
                                    <td>'.self::get_user($row['user']).'</td>
                                    <td>'."<a href='' target='popup' onclick=window.open('../front/document.send.php?docid=$docid','name',width=600,height=400)><img src='../plugins/rapportinter/pics/pdf-dist.png'> Rapport</a></td>
                                </tr>";
                        }
                        echo "</table>";
                        echo "<link rel='stylesheet' type='text/css' href='../plugins/rapportinter/lib/table.css' /> ";


                }
                if(PluginRapportinterProfile::PeutCree()){
                    echo"                          
                            <form method='POST' action='../plugins/rapportinter/front/export.php'> 
                                <input id='id_ticket' type='hidden' name='id'  value=$id />
                                <input id='sigdata'type='hidden' name='signature'  value=''/>

                                <script src='https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js'></script>
                                <p>Prévisualisation du rapport:</p>
                                <iframe src='../plugins/rapportinter/inc/rapport_previsu.class.php?id=$id' width='80%'; height='1000' align='middle'></iframe>
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
                                <button type='submit' style='background-color:#FEC95C; color:#8f5a0a; padding:5px; margin:10px; font:bold 12px Arial, Helvetica' >Générer le rapport</button>
                            </form>";
                }
            
        }
        return true;
        }
       

}
