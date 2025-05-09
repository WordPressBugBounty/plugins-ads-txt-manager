<?php

interface iAdsTxtManager_Solution
{
    public function SetupSolution();
    public function TearDownSolution();
}

require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-htaccess-modifier.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-file-modifier.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-adstxtmanager-empty-solution.php';

class AdsTxtManager_Solution_Factory
{

    public function GetBestSolution()
    {

        $emptyModifier = new AdsTxtManager_Empty_Solution();
        //Do we have a ads txt manager id?
        $adstxtmanager_id = get_option('adstxtmanager_id');

        // Use our helper function to check if we have a valid ads.txt URL
        $ads_txt_url = AdstxtManager::get_ads_txt_url();

        if (!$ads_txt_url) {
            //we don't have a valid adstxtmanager_id, lets return the empty solution
            return $emptyModifier;
        }

        // Get the ID value using the helper function for consistency
        $adstxtmanager_id_value = AdstxtManager::get_adstxtmanager_id_value($adstxtmanager_id);

        //If we have apache, lets modify the sites htaccess file
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            //return htaccess solution
            $htaccessModifier = new AdstxtManager_HTACCESS_Modifier();
            return $htaccessModifier;
        } else {
            //return file modification solution
            $fileModifier = new AdsTxtManager_File_Modifier();
            return $fileModifier;
        }
    }
}
