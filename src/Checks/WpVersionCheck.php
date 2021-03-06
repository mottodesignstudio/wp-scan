<?php

namespace Motto\Checks;

use Motto\Checks\WpCheck;

class WpVersionCheck extends WpCheck {
    
    const VERSION_API = 'https://api.wordpress.org/core/stable-check/1.0/';

    public function run()
    {
        $meta = false;
        if( $this->checker->getXpath() )
            $meta = $this->checker
                        ->getXpath()
                        ->query("//meta[contains(@name,'generator')]");
                        
        $version = false;
        $generator = false;

        if( $meta ) {
            if( $meta->length > 0 ) {
                $generator = $meta[0]->getAttribute('content');
            }
    
            if( strpos(strtolower($generator), 'wordpress') !== false ) {
                preg_match('/(\d+\.)?(\d+\.)?(\*|\d+)/', $generator, $matches);
    
                if( !empty($matches) )
                    $version = $matches[0];
                    $this->getVersionInfo($version);
            }    
        }

        $this->addProp('generator', $generator);
        $this->addProp('version', $version);
    }

    private function getVersionInfo( $version )
    {
        $client = $this->checker->getClient();
        $versions = json_decode(
            $client->get(self::VERSION_API)->getBody()->getContents(), true
        );

        if( isset($versions[$version]) )
            $this->addProp('status', $versions[$version]);
    }
}