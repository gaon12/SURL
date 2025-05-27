<?php use MediaWiki\MediaWikiServices;
class SURL extends SpecialPage
{
    public function __construct()
    {
        parent::__construct("SURL");
    }
    public function execute($sub)
    {
        $output = $this->getOutput();
        $this->setHeaders();
        $request = $this->getRequest();
        $path = "";
        $id = "";
        $pathId = explode("/", $sub);
        if (count($pathId) == 2) {
            list($path, $id) = $pathId;
        } elseif ($request->getVal("a")) {
            $path = "a";
            $id = $request->getVal("a");
        }
        if (empty($path) || empty($id)) {
            $output->addWikiTextAsContent("Invalid URL format.");
            return;
        }
        $oldid = null;
        if ($path === "a") {
            $oldid = $this->base_convert_arbitrary($id, 62, 10);
        }
        if ($oldid) {
            $revisionRecord = MediaWikiServices::getInstance()
                ->getRevisionLookup()
                ->getRevisionById($oldid);
            if ($revisionRecord) {
                $title = Title::newFromLinkTarget(
                    $revisionRecord->getPageAsLinkTarget()
                );
                $url = $title->getFullURL();
                $output->redirect($url);
            } else {
                $notFoundText = wfMessage("surl-not-found")->text();
                $output->addWikiTextAsContent($notFoundText);
            }
        } else {
            $provideOldidText = wfMessage("surl-provide-oldid")->text();
            $output->addWikiTextAsContent($provideOldidText);
        }
    }
    private function base_convert_arbitrary($number, $fromBase, $toBase)
    {
        $digits =
            "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $length = strlen($number);
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result = bcadd(
                $result,
                bcmul(
                    strpos($digits, $number[$i]),
                    bcpow($fromBase, $length - $i - 1)
                )
            );
        }
        return intval($result);
    }
}
