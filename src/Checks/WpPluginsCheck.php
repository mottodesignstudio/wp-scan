<?php

namespace Motto\Checks;

use Motto\Checks\WpCheck;

class WpPluginsCheck extends WpCheck {
    
    const PLUGIN_API = 'https://api.wordpress.org/plugins/info/1.0/{slug}.json';

    protected $regex = '/wp-content\/plugins\/([a-z0-9-]+)\/*/';
    protected $plugin_props = [
        'name',
        'slug',
        'version',
        'author',
        'author_profile',
        'requires',
        'tested',
        'requires_php',
    ];

    public function run()
    {
        preg_match_all($this->regex, $this->checker->getHtml(), $matches);
        $plugin_slugs = $matches[1];
        $client = $this->checker->getClient();
        $plugins = [];
        foreach( $plugin_slugs as $slug ) {
            $response = $client->get( $this->pluginRequest($slug) );
            $data = json_decode($response->getBody()->getContents());
            if( isset($data->error) )
                continue;

            foreach( $this->plugin_props as $prop ) {
                $plugins[$slug][$prop] = $data->{$prop};
            }
        }
        $this->addProps($plugins);
    }

    private function pluginRequest( $slug )
    {
        return str_replace('{slug}', $slug, self::PLUGIN_API);
    }
}