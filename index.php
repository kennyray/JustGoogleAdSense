<?php
/*
Plugin Name: Just Google AdSense
Plugin URI: http://phpblog.kennyray.com
Description: Allows you to add Google AdSense to your blog
Version: 1.0
Author: Kenny Ray
Author URI: http://phpblog.kennyray.com
License: MIT
*/
 
/*  MIT License

Copyright (c) 2017 Kenny Ray

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

define( 'JGAS_DIR_PATH', plugin_dir_path( __FILE__ ) );

class JustGoogleAdSense
{
    private  $message = "";

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action( 'wp_enqueue_scripts', array($this, 'AddGASCodeToHead') );
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_item'));
        register_activation_hook( JGAS_DIR_PATH,  array( 'JustGoogleAdSense', 'activate' ) );
        register_deactivation_hook( JGAS_DIR_PATH,  array( 'JustGoogleAdSense', 'deactivate' ) );



    }

    public static function activate()
    {

    }

    public static function deactivate()
    {
        delete_option('jgas_gascode');
    }

    public function add_menu_item()
    {
       add_menu_page('Google AdSense', 'Google AdSense', 'manage_options', 'google-adsense', array($this,'google_adsense_code_entry_page'), '/wp-admin/images/icon-people.png');
    }


    public function google_adsense_code_entry_page()
    {
        $this->CheckForPost();

        $jgas_gascode = $this->GetGASCode();

        echo $this->FormContent($jgas_gascode);

    }

    private function CheckForPost()
    {
        if(isset($_POST['jgas_gascode']))
        {
            $this->SaveCode($_POST['jgas_gascode']);
            $this->SetMessage();
        }
    }

    private function FormContent($jgas_gascode)
    {
        $default_text = 'YOUR_CODE_HERE';
        $html = "";
        $html .= '<form action="' . $_SERVER['REQUEST_URI'] . '" name="jgas-form" method="post">';
        $html .= '<h2>Google Adsense Code</h2>';
        $html .= 'Pub Code: <input type="text" name="jgas_gascode" value="' . $jgas_gascode . '" id="jgas_gascode"> <input type="submit" value="Save"> ';
        $html .= $this->message;
        $html .= '</form>';
        $html .= '<p>The following code will be inserted:</p>';
        $html .= '<pre>&lt;script src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"&gt;&lt;/script&gt;
&lt;script&gt;
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "' . ($jgas_gascode ? $jgas_gascode : $default_text) . '",
    enable_page_level_ads: true
  });
&lt;/script&gt;</pre>';


        return $html;
    }

    private function SetMessage()
    {
        $this->message = "<span style='color:darkgreen;'>Code saved</span>";
    }

    private function SaveCode($value)
    {
        update_option('jgas_gascode', sanitize_text_field($value));
    }

    public function AddGASCodeToHead()
    {   $jgas_gascode = $this->GetGASCode();
        if($jgas_gascode) {
            wp_enqueue_script('jgas-gascodescript', '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', array(), '1.0');
            wp_add_inline_script('jgas-gascodescript', '(adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "'. $jgas_gascode.'",
    enable_page_level_ads: true
  });');
        }
    }

    /**
     * @return mixed
     */
    private function GetGASCode()
    {
        return get_option('jgas_gascode', '');
    }
}

$jgas = new JustGoogleAdSense();

