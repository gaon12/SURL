<?php
class SURLHooks {
    public static function onBeforePageDisplay(OutputPage &$out, Skin &$skin) {
        $out->addModules('ext.SURL');
        $title = $out->getTitle();
        $oldId = $out->getRevisionId();

        if (is_null($oldId)) {
            return true;
        }

        $baseUrl = parse_url(wfExpandUrl(wfScript()), PHP_URL_SCHEME) . '://' . parse_url(wfExpandUrl(wfScript()), PHP_URL_HOST);
        $base62OldId = self::base_convert_arbitrary($oldId, 10, 62);
        $urlOldId = $baseUrl . '/' . $GLOBALS['wgShortURLoldIDPath'] . '/' . $oldId;
        $urlBase62OldId = $baseUrl . '/' . $GLOBALS['wgShortURLbase62OldIdPath'] . '/' . $base62OldId;
		$surlText = wfMessage('surl-link')->text();

        $out->addHTML('<a id="surl-link" href="#" class="styled-link">' . htmlspecialchars($surlText) . '</a>');
        $out->addJsConfigVars('surlOldId', $urlOldId);
        $out->addJsConfigVars('surlBase62OldId', $urlBase62OldId);

        return true;
    }

    private static function base_convert_arbitrary($number, $fromBase, $toBase) {
        $digits = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $number = strval($number);
        $length = strlen($number);
        $result = '';
        $nibbles = [];

        for ($i = 0; $i < $length; ++$i) {
            $nibbles[] = strpos($digits, $number[$i]);
        }

        do {
            $value = 0;
            $newlen = 0;

            for ($i = 0; $i < $length; ++$i) {
                if (isset($nibbles[$i])) {
                    $value = $value * $fromBase + $nibbles[$i];
                }

                if ($value >= $toBase) {
                    $nibbles[$newlen++] = (int) ($value / $toBase);
                    $value %= $toBase;
                } else if ($newlen > 0) {
                    $nibbles[$newlen++] = 0;
                }
            }

            $length = $newlen;
            $result = $digits[$value] . $result;
        } while ($newlen != 0);

        return $result;
    }
}

class SpecialSURL extends SpecialPage {
    public function __construct() {
        parent::__construct('SURL');
    }

    public function execute($par) {
        $this->setHeaders();
        $oldId = intval($par);
        $rev = Revision::newFromId($oldId);

        if ($rev) {
            $this->getOutput()->redirect($rev->getTitle()->getFullURL());
        } else {
            $invalidIdText = wfMessage('surl-invalid-id')->text();
            $this->getOutput()->addHTML(htmlspecialchars($invalidIdText));
        }
    }
}

$wgSpecialPages['SURL'] = 'SpecialSURL';
