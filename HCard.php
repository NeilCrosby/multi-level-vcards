<?php

class HCard {

    public static function getLink( $url ) {
        $outUrl = trim($url);
        if ( false === strpos( $outUrl, '://' ) ) {
            $outUrl = 'http://'.$outUrl;
        }

        $linkText = trim($url);
		if (preg_match("/^(https?:\/\/)?(www\.)?([^\/]+)\//", $url, $matches)) {
			$linkText = $matches[3];
		}
		
		return "<a href='$outUrl' rel='me' class='url'>$linkText</a>";
    }

    public static function getEmail( $url ) {
        $outUrl = $url;
        if ( false === strpos( $url, ':' ) ) {
            $outUrl = 'mailto:'.$url;
        }

        return "<a href='$outUrl' class='email'>$url</a>";
    }

    public static function getAIM( $url ) {
        $outUrl = $url;
        if ( false === strpos( $url, ':' ) ) {
            $outUrl = 'aim:goim?screenname='.$url;
        }

        return "<a href='$outUrl' class='email'>$url</a>";
    }

    public static function getJabber( $url ) {
        $outUrl = $url;
        if ( false === strpos( $url, ':' ) ) {
            $outUrl = 'xmpp:'.$url;
        }

        return "<a href='$outUrl' class='email'>$url</a>";
    }

    public static function getYahooMessenger( $url ) {
        $outUrl = $url;
        if ( false === strpos( $url, ':' ) ) {
            $outUrl = 'ymsgr:sendIM?'.$url;
        }

        return "<a href='$outUrl' class='email'>$url</a>";
    }

    public static function getAddress( $line, $key = null ) {
        $items = explode( ';', $line );

        $pieces = array();
        if ( $items[2] ) {
            $pieces[] = "<span class='street-address'>${items[2]}</span>";
        }
        if ( $items[3] ) {
            $pieces[] = "<span class='locality'>${items[3]}</span>";
        }
        if ( $items[4] ) {
            $pieces[] = "<span class='region'>${items[4]}</span>";
        }
        if ( $items[5] ) {
            $pieces[] = "<span class='postal-code'>${items[5]}</span>";
        }
        if ( $items[6] ) {
            $pieces[] = "<span class='country-name'>${items[6]}</span>";
        }

        if ( !$pieces ) {
            return '';
        }

        $output = implode( ',</p><p>', $pieces );

        $keyClass='';
/*
        if ( $key ) {
            if ( false !== strpos( $key, 'type=HOME' ) ) {
                $keyClass .= ' home';
            } elseif( false !== strpos( $key, 'type=WORK' ) ) {
                $keyClass .= ' work';
            }
        }
*/        
        return "<div class='adr$keyClass'><p>$output</p></dov>";
    }

    public static function getTelephone( $line ) {
        return "<span class='tel'>$line</span>";
    }

    public static function getOrganisation( $line ) {
        $items = explode( ';', $line );
        return "<span class='org'>${items[0]}</span>";
    }

    public static function getFN( $line ) {
        return "<span class='fn'>$line</span>";
    }

    public static function getBDay( $line ) {
        $date = date( 'jS F Y', strtotime($line) );
        return "<abbr class='bday' title='$line'>$date</abbr>";
    }

}

?>