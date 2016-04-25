<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include ("../../../inc/includes.php");

Session::checkRight("profile", "r");

$prof = new PluginRapportinterProfile();

if (isset($_POST['update_user_profile'])) {
   $prof->update($_POST);
   Html::back();
}

?>


