<?php

require_once("markdown.php");
require_once("HCard.php");

class VCard {

    const LEVEL_ALL = 0;
    const LEVEL_ACQUAINTANCE = 5;
    const LEVEL_FRIEND = 10; 

    private $iLevel = self::LEVEL_ALL;
    private $aLevels = array(
        self::LEVEL_ALL,
        self::LEVEL_ACQUAINTANCE,
        self::LEVEL_FRIEND,
    );
    private $aVCard = array();
    public $aFilters = array(
        'boilerplate' => array(
            'BEGIN:VCARD',
            'VERSION:3.0',
            'END:VCARD'
        ),
        'everyone' => array(
            'N:',
            'FN:',
            'ORG',
            'ADR;type=WORK',
            'NOTE',
            'URL:',
            'URL;',
            'PHOTO;BASE64',
        ),
        'acquaintance' => array(
            'X-JABBER;type=HOME',
            'EMAIL;type=INTERNET;type=HOME',
            'BDAY;',
            'X-YAHOO;type=HOME',
            'X-YAHOO-ID;type=HOME',
            'X-AIM;type=HOME;',
            'X-JABBER;type=HOME;',
            'TEL;type=CELL',
        ),
        'friend' => array(
            'X-YAHOO;type=WORK',
            'X-YAHOO-ID;type=WORK',
            //'X-ABLabel:holding',
            'EMAIL;type=INTERNET;type=WORK',
            'TEL;type=HOME',
            'ADR;type=HOME',
            'TEL:',
            'TEL;type=WORK',
        ),
    );
    private $aKnownProfileSites = array(
        'flickr' => 'flickr.com',
        'delicious' => 'delicious.com',
        'facebook' => 'facebook.com',
        'twitter' => 'twitter.com',
        'linkedin' => 'linkedin.com',
        'lovefilm' => 'lovefilm.com',
        'slideshare' => 'slideshare.net',
        'wikipedia' => 'en.wikipedia.org',
        'thetenwordreview' => 'thetenwordreview.com',
        'pixish' => 'pixish.com',
        'brightkite' => 'brightkite.com',
        'technorati' => 'technorati.com',
        'mybloglog' => 'mybloglog.com',
        'lastfm' => 'last.fm',
        'upcoming' => 'upcoming.yahoo.com',
        'geocaching' => 'geocaching.com',
        'etsy' => 'etsy.com',
        'threadless' => 'threadless.com',
        'stumbleupon' => 'stumbleupon.com',
        'xbox' => 'gamercard.xbox.com',
        'pownce' => 'pownce.com',
        'youtube' => 'youtube.com',
        'digg' => 'digg.com',
        'amazon' => 'amazon.co.uk',
        'github' => 'github.com',
        'calendaraboutnothing' => 'calendaraboutnothing.com',
        'threesixtyvoice' => '360voice.com',
        'lighthouseapp' => 'lighthouseapp.com',
    );
    private $sFilename = '';

    public function __construct( $filename=null ) {
        if ( !$filename || !file_exists($filename) ) {
            //echo "filename does not exist - $filename";
            throw new Exception('No file given, or file does not exist.');
            return false;
        }
        $this->sFilename = $filename;
        return true;
    }
    
    public function setLevel( $level=self::LEVEL_ALL) {
        if ( !is_numeric($level) ) {
            return false;
        }
        
        if ( in_array( $level, $this->aLevels ) ) {
            $this->iLevel = $level;
            return true;
        }
        
        return false;
    }
    
    private function loadVCardAsArray() {
        return file($this->sFilename);
    }
    
    private function isLineToBeFiltered( $line, $level ) {
        foreach ( $this->aFilters[$level] as $filter ) {
            if ( false !== strpos( $line, $filter ) ) {
                return true;
            }
        }
        return false;
    }
    
    private function getSpecialItemInfo( $line ) {
        if ( preg_match( '/^item\d+\./', $line, $matches ) ) {
            return $matches[0];
        }
        
        return false;
    }
    
    private function reduceVCard($aVCard) {
        $aOutput = array();
        $aExtraThingsToFilter = array();
        
        foreach ( $aVCard as $line ) {
            // first get rid of the boilerplate swtuff
            if ( 0 === strpos( $line, 'BEGIN:VCARD' ) ) {
                continue;
            }

            if ( 0 === strpos( $line, 'VERSION' ) ) {
                continue;
            }

            if ( 0 === strpos( $line, 'END:VCARD' ) ) {
                continue;
            }
            
            if ( 0 === strpos( $line, 'X-ABUID' ) ) {
                continue;
            }
            
            // now get rid of friend stuff if the level is lower than friend
            if ( $this->iLevel < self::LEVEL_FRIEND ) {
                if ($this->isLineToBeFiltered( $line, 'friend' )) {
                    if ( $item = $this->getSpecialItemInfo($line) ) {
                        array_push($aExtraThingsToFilter, $item);
                    }
                    continue;
                }
            }
            
            // now get rid of acquaintance stuff if the level is lower than acquaintance
            if ( $this->iLevel < self::LEVEL_ACQUAINTANCE ) {
                if ($this->isLineToBeFiltered( $line, 'acquaintance' )) {
                    if ( $item = $this->getSpecialItemInfo($line) ) {
                        array_push($aExtraThingsToFilter, $item);
                    }
                    continue;
                }
            }
            
            // if we get all the way here then this line is allowed through
            array_push( $aOutput, $line );
        }
        
        // now we check the extra special $aExtraThingsToFilter array
        foreach ( $aExtraThingsToFilter as $filter ) {
            foreach ( $aOutput as $key=>$line ) {
                if ( 0 === strpos( $line, $filter ) ) {
                    unset( $aOutput[$key] );
                }
            }
        }
        
        return $aOutput;
    }
    
    public function toVCard() {
        if (self::LEVEL_FRIEND == $this->iLevel ) {
            return file_get_contents($this->sFilename);
        }

        $aVCard = $this->loadVCardAsArray();
        $aReducedVCard = $this->reduceVCard($aVCard);
        return "BEGIN:VCARD\nVERSION:3.0\n".implode("", $aReducedVCard)."\nEND:VCARD\n";
    }
    
    public function toHCard() {
        $aVCard = $this->loadVCardAsArray();
        $aReducedVCard = $this->reduceVCard($aVCard);
        
        $html = '';
        $htmlProfile = '';
        $htmlProfileUnknown = '';
        $htmlSites = '';
        $htmlName = '';
        $htmlBasic = '';
        $htmlAbout = '';
        $htmlImage = '';
        
        foreach ( $aReducedVCard as $line ) {
            
            $isBasic = false;

            if ( false !== strpos( $line, 'X-ABLabel' ) ) {
                continue;
            }
            
            if ( false !== strpos( $line, 'X-ABADR' ) ) {
                continue;
            }
            
            if ( false !== strpos( $line, 'X-YAHOO-ID' ) ) {
                continue;
            }
            
            if ( 0 === strpos( $line, 'N:' ) ) {
                continue;
            }

            if (false === strpos( $line, ':' )) {
                continue;
            }
            
            $colonPos = strpos( $line, ':' );
            $key = substr( $line, 0, $colonPos );
            $value = substr( $line, $colonPos + 1 );
            
            $value = str_replace('\n', "\n", $value);
            $value = stripslashes($value);

            $outKey = $key;
            $outValue = $value;
            
            $extraInfo = '';
            if ( $item = $this->getSpecialItemInfo($line) ) {
                foreach ( $aReducedVCard as $tempLine ) {
                    if ( $tempLine != $line && 0 === strpos( $tempLine, $item."X-ABLabel:" ) ) {
                        $extraInfo = ucwords(trim(substr( $tempLine, strlen($item."X-ABLabel:") )));
                    }
                }
            }

            if ( false !== strpos( $key, 'NOTE' ) ) {
                $htmlAbout = $this->getMarkdown($value);
                continue;
            } else if ( false !== strpos( $key, 'URL' ) ) {
                if ( 'Profile' == trim($extraInfo) || 'Backnetwork' == trim($extraInfo) ) {
                    $site = $this->getProfileSite($value);
                    if ($site) {
                        $htmlProfile .= "<li class=\"known $site\">".HCard::getLink($value).'</li>';
                    } else {
                        $htmlProfileUnknown .= "<li>".HCard::getLink($value).'</li>';
                    }
                    continue;
                } else {
                    $extra = ($extraInfo) ? " ($extraInfo)" : '';
                    $site = $this->getProfileSite($value);
                    if ($site) {
                        $htmlSites .= "<li class=\"known $site\">".HCard::getLink($value).$extra.'</li>';
                    } else {
                        $htmlSites .= "<li>".HCard::getLink($value).$extra.'</li>';
                    }
                    continue;
//                  $outValue = HCard::getLink($value);
//                  $outKey = 'URL';
                }
            } elseif ( false !== strpos( $key, 'EMAIL' ) ) {
                $outValue = HCard::getEmail($value);
                $outKey = 'E-Mail';
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'ORG' ) ) {
                $outValue = HCard::getOrganisation($value);
                $outKey = 'Organisation';
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'ADR;' ) || false !== strpos( $key, 'ADR:' ) ) {
                $outValue = HCard::getAddress($value, $key);
                $outKey = 'Address';
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'TEL;' ) || false !== strpos( $key, 'TEL:' ) ) {
                $outValue = HCard::getTelephone($value);
                $outKey = 'Telephone';
                $isBasic = true;
            } elseif ( 0 === strpos( $key, 'FN' ) ) {
                $htmlName = HCard::getFN($value);
                continue;
            } elseif ( false !== strpos( $key, 'BDAY' ) ) {
                $outValue = HCard::getBDay($value);
                $outKey = 'Birthday';
            } elseif ( false !== strpos( $key, 'X-AIM' ) ) {
                $outValue = HCard::getAIM($value);
                $outKey = "<acronym title='AOL Instant Messenger'>AIM</acronym>";
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'X-YAHOO' ) ) {
                $outValue = HCard::getYahooMessenger($value);
                $outKey = "Yahoo! Messenger";
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'X-JABBER' ) ) {
                $outValue = HCard::getJabber($value);
                $outKey = "Jabber";
                $isBasic = true;
            } elseif ( false !== strpos( $key, 'X-ABRELATEDNAMES' ) ) {
                $outValue = $value;
                $outKey = "";
            } elseif ( false !== strpos( $key, 'PHOTO' ) ) {
                $image = $this->getImageBase64String($aReducedVCard);
                $htmlImage = "<img src='data:image/png;base64,$image' alt='' class='photo'>";
                continue;
            }
            
            if ( false !== strpos( $key, 'type=HOME' ) ) {
                $outKey .= ' - Home';
            } elseif( false !== strpos( $key, 'type=WORK' ) ) {
                $outKey .= ' - Work';
            } elseif( false !== strpos( $key, 'type=CELL' ) ) {
                $outKey .= ' - Mobile';
            }
            
            if ($extraInfo) {
                if (isset($outkey) && $outKey) {
                    $outkey .= ' - ';
                }
                $outKey .= $extraInfo;
            }

            if ( false !== strpos( $key, 'type=pref' ) ) {
                $outKey .= ' - Preferred';
            }
            
            if ( $isBasic ) {
                $htmlBasic .= "<tr><th>$outKey</th><td>$outValue</td></tr>";
            } else {
                $html .= "<tr><th>$outKey</th><td>$outValue</td></tr>";
            }
        }
        
        
        $modEverythingElse = '';
        if ( $html ) {
            $modEverythingElse = <<<HTML
    <div class='mod'>
        <h2 class='hd'>Everything Else</h2>
        <table class='bd'>
            <thead>
                <tr>
                    <th>Key</th>
                    <th class="last">Value</th>
                </tr>
            </thead>
            <tbody>
                $html
            </tbody>
        </table>
    </div>
HTML;
        }

        return <<<HTML
<div class='vcard'>
    <h1>$htmlName</h1>
    <div class="yui-g">
        <div class="yui-u first">
            <div class='mod'>
                <h2 class='hd'>Basic Info</h2>
                $htmlImage
                <table class='bd'>
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th class="last">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        $htmlBasic
                    </tbody>
                </table>
            </div>
            <div class="yui-b">
                <div class='sites mod yui-u first'>
                    <h2 class='hd'>My sites</h2>
                    <ul class='bd'>$htmlSites</ul>
                </div>
                <div class='profiles mod yui-u'>
                    <h2 class='hd'>My profiles</h2>
                    <ul class='bd profiles'>$htmlProfile$htmlProfileUnknown</ul>
                </div>
            </div>
        </div>
        <div class="yui-u">
            <div class='mod'>
                <h2 class='hd'>About Me</h2>
                <div class='bd'>$htmlAbout</div>
            </div>
            $modEverythingElse
        </div>
    </div>
</div>
HTML;

    }
    

    private function getMarkdown($line) {
        return Markdown($line);
    }
    
    private function getProfileSite($url) {
        if (!preg_match("/^(https?:\/\/)?(www\.)?([^\/]+)\//", $url, $matches)) {
            return false;
        }
        
        $domain = $matches[3];

        foreach ( $this->aKnownProfileSites as $key=>$value ) {
            if ( $domain == $value || ".$value" == mb_substr( $domain, -mb_strlen( $value ) - 1 ) ) {
                return $key;
            }
        }
        
        return false;
    }
    
    private function getImageBase64String($aReducedVCard) {
        $imageString = '';
        $startedImage = false;
        $endedImage = false;

        foreach ($aReducedVCard as $line) {
            if ( false !== strpos( $line, 'PHOTO' ) ) {
                $startedImage = true;
                continue;
            }
            
            $pos = strpos( $line, '==' );
            if ( false !== $pos ) {
                $line = substr($line, 0, $pos);
                $endedImage = true;
            }
            
            if ($startedImage) {
                $imageString .= trim($line);
            }
            
            if ($endedImage) {
                return $imageString;
            }
        }
        
        return $imageString;
    }

}

?>
