<?php namespace Diveramkt\WhatsappFloat\Classes;

use Backend, BackendAuth;
// use Diveramkt\Smart\Models\CookiesSettings;
// use Diveramkt\Smart\Classes\CSettings;
use System\Models\PluginVersion;

class Helpers {

    /**
     * Return a Backend URL based on a matrix of URLS and permissions
     *
     * @param array  $urls    Matrix of permissions and URLs
     * @param string $default Default URL
     *
     * @return string
     */
    public static function getBackendURL(array $urls, string $default) :string {
        $user = BackendAuth::getUser();
        foreach ($urls as $permission => $URL) {
            if ($user->hasAccess($permission)) {
                return Backend::url($URL);
            }
        }
        return Backend::url($urls[$default]);
    }

    /**
     * Check if Small GDPR plugin is installed
     *
     * @return boolean
     */
    public static function isTranslate() {
        if(class_exists('\RainLab\Translate\Classes\Translator') && class_exists('\RainLab\Translate\Models\Message')) return 1;
        else return 0;
    }

    public static function isTranslatePlugin() :bool {
        $plugins=new PluginVersion();
        return class_exists('\RainLab\Translate\Classes\Translator') && class_exists('\RainLab\Translate\Models\Message') && $plugins->where('code','RainLab.Translate')->ApplyEnabled()->count();
    }


    public static function isUploadsPlugin() :bool {
        $plugins=new PluginVersion();
        return class_exists('\Diveramkt\Uploads\Plugin') && $plugins->where('code','Diveramkt.Uploads')->ApplyEnabled()->count();
    }

    public static function isMiscelaniousPlugin() :bool {
        $plugins=new PluginVersion();
        return class_exists('\Diveramkt\Miscelanious\Plugin') && $plugins->where('code','Diveramkt.Miscelanious')->ApplyEnabled()->count();
    }

}

?>