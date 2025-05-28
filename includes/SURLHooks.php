<?php
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

class SURLHooks {
    public static function onBeforePageDisplay(OutputPage &$out, Skin &$skin) {
        $out->addModules('ext.SURL');

        $title = $out->getTitle();
        $oldId = $out->getRevisionId();

        if (is_null($oldId)) {
            return true;
        }

        global $wgServer, $wgShortURLoldIDPath, $wgShortURLbase62OldIdPath;

        $baseUrl = rtrim($wgServer, '/');
        $base62OldId = self::base_convert_arbitrary($oldId, 10, 62);

        $urlOldId = $baseUrl . '/' . $wgShortURLoldIDPath . '/' . $oldId;
        $urlBase62OldId = $baseUrl . '/' . $wgShortURLbase62OldIdPath . '/' . $base62OldId;

        $surlText = wfMessage('surl-link')->text();

        // HTML 링크
        $surlLink = '<a id="surl-link" href="#" class="styled-link" data-surl-oldid="' .
                    htmlspecialchars($urlOldId) . '" data-surl-base62="' .
                    htmlspecialchars($urlBase62OldId) . '">' .
                    htmlspecialchars($surlText) . '</a>';

        // <template>로 HTML 삽입 및 JS 분리
        $templateId = 'surl-link-template';
        $out->addHTML('<template id="' . $templateId . '">' . $surlLink . '</template>');

        // 안전하게 JavaScript 삽입
        $out->addInlineScript(<<<EOT
document.addEventListener("DOMContentLoaded", function() {
    var copyrightElement = document.querySelector(".footer-info-copyright");
    var template = document.getElementById("$templateId");
    if (copyrightElement && template) {
        copyrightElement.innerHTML += " | " + template.innerHTML;
    }
});
EOT);

        // JS 변수로 URL 전달
        $out->addJsConfigVars('surlOldId', $urlOldId);
        $out->addJsConfigVars('surlBase62OldId', $urlBase62OldId);
        return true;
    }

    // GMP를 이용한 base 변환
    private static function base_convert_arbitrary($number, $fromBase, $toBase) {
        $digits = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = gmp_init($number, $fromBase);
        $result = '';
        while (gmp_cmp($num, 0) > 0) {
            list($num, $rem) = gmp_div_qr($num, $toBase);
            $result = $digits[gmp_intval($rem)] . $result;
        }
        return $result ?: '0';
    }
}

class SpecialSURL extends SpecialPage {
    public function __construct() {
        parent::__construct('SURL');
    }

    public function execute($par) {
        $this->setHeaders();
        $out = $this->getOutput();

        $oldId = intval($par);
        if ($oldId <= 0) {
            $out->addHTML(htmlspecialchars(wfMessage('surl-invalid-id')->text()));
            return;
        }

        $services = MediaWikiServices::getInstance();
        $revLookup = $services->getRevisionLookup();
        $rev = $revLookup->getRevisionById($oldId);

        if ($rev instanceof RevisionRecord) {
            $title = $rev->getPage()->getTitle();
            $out->redirect($title->getFullURL());
        } else {
            $invalidIdText = wfMessage('surl-invalid-id')->text();
            $out->addHTML(htmlspecialchars($invalidIdText));
        }
    }
}

// SpecialPage 등록
$wgSpecialPages['SURL'] = 'SpecialSURL';
