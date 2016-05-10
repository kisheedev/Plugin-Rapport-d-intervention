<?php
/**
 * Class de gestion pour la partie profil
 */
class PluginRapportinterProfile extends CommonDBTM
    {
    /**
     * Récupère le nom de l'onglet si l'utilisateur est autorisé
     * @param CommonGLPI $item
     * @param type $withtemplate
     * @return boolean|string
     */
   static function canCreate() {

      if (isset($_SESSION["glpi_plugin_rapportinter_profiles"])) {
         return ($_SESSION["glpi_plugin_rapportinter_profiles"]['right'] == '4');
      }
      return false;
   }

   static function canView() {

      if (isset($_SESSION["glpi_plugin_rapportinter_profiles"])) {
         return ($_SESSION["glpi_plugin_rapportinter_profiles"]["right"] == 4
                 || $_SESSION["glpi_plugin_rapportinter_profiles"]["right"] == 1);
      }
      return false;
   }
   static function createAdminAccess($ID) {

       global $DB;
       $myProf = new self();
       // si le profile n'existe pas déjà dans la table profile de mon plugin
       if (!$myProf->getFromDB($ID)) {
       // ajouter un champ dans la table comprenant l'ID du profil d la personne connecté et le droit d'écriture
            $sql="INSERT INTO glpi_plugin_rapportinter_profiles VALUES ('$ID','4')";
            $DB->query($sql);
       }
   }
   function showForm($id, $options=array()) {

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!self::canView()) {
         return false;
      }

      $canedit = self::canCreate();
      $prof = new Profile();
      if ($id){
         $this->getFromDB($id);
         $prof->getFromDB($id);
      }

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2' class='center b'>".sprintf(__('%1$s %2$s'), ('gestion des droits :'),
                                                           Dropdown::getDropdownName("glpi_profiles",
                                                                                     $this->fields["id"]));
      echo "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>Utiliser Mon Plugin</td><td>";
      Profile::dropdownNoneReadWrite("right", $this->fields["right"], 1, 1, 1);
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value=$id>";
         echo "<input type='submit' name='update_user_profile' value='Mettre à jour'
                class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $_GET['id'];
         // si le profil n'existe pas dans la base, je l'ajoute
         if (!$prof->GetfromDB($ID)) {
            $prof->createAccess($ID);
         }
        // j'affiche le formulaire*/
         $prof->showForm($ID);
      }
      return true;
   }
   
   function createAccess($ID) {
        global $DB;
        $sql="INSERT INTO glpi_plugin_rapportinter_profiles (`id`) VALUES ('$ID')";
        $DB->query($sql);
}
   static function changeProfile() {

   $prof = new self();
   if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
      $_SESSION["glpi_plugin_rapportinter_profiles"] = $prof->fields;
   } else {
      unset($_SESSION["glpi_plugin_rapportinter_profiles"]);
   }
}
 function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         return "Rapport d'intervention";
      }
      return '';
   }
   
static function PeutVoir() {
       global $DB;
       $ID=$_SESSION['glpiactiveprofile']['id'];
       $sql="SELECT `right` FROM glpi_plugin_rapportinter_profiles WHERE id=$ID";
       $res=$DB->query($sql);
       if ($DB->numrows($res)>0){
            $row=$DB->fetch_assoc($res);
            if ($row['right'] == 4 || $row['right']==1)
                return true;           
       }
      return false;
      }

             
      static function PeutCree() {
       global $DB;
       $ID=$_SESSION['glpiactiveprofile']['id'];
       $sql="SELECT `right` FROM glpi_plugin_rapportinter_profiles WHERE id=$ID";
       $res=$DB->query($sql);
       if ($DB->numrows($res)>0){
            $row=$DB->fetch_assoc($res);
            if ($row['right'] == '4')
                return true;           
       }
      return false;
      }
}
?>