<?php namespace Diveramkt\WhatsappFloat\Classes;

use Backend, BackendAuth;
use System\Models\PluginVersion;

class BackendHelpers {

    /**
     * Check if Diveramkt Tranlate plugin is installed
     *
     * @return boolean
     */
    public static $getIsMiscelanious=null;
    public static function isMiscelanious($method=false) :bool {
        if(!Self::$getIsMiscelanious){
            $plugins=new PluginVersion();
            Self::$getIsMiscelanious=class_exists('\Diveramkt\Miscelanious\Classes\Functions') && class_exists('\Diveramkt\Miscelanious\Plugin') && $plugins->where('code','Diveramkt.Miscelanious')->ApplyEnabled()->count();
        }
        return Self::$getIsMiscelanious;
    }

}

?>