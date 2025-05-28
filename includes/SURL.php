<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class SURL extends SpecialPage
{
    private const BASE62_DIGITS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    public function __construct()
    {
        parent::__construct('SURL');
    }

    public function execute($sub)
    {
        $output = $this->getOutput();
        $this->setHeaders();

        $request = $this->getRequest();
        $path = '';
        $id = '';

        // 서브 경로 또는 쿼리 매개변수 파싱
        $pathId = explode('/', trim($sub ?? '', '/'));
        if (count($pathId) === 2) {
            [$path, $id] = $pathId;
        } elseif ($request->getCheck('a')) {
            $path = 'a';
            $id = $request->getVal('a');
        }

        // 필수값 검증
        if (!$this->isValidId($path, $id)) {
            $output->addWikiTextAsContent("Invalid URL format.");
            return;
        }

        // 리비전 ID 디코딩
        $oldid = ($path === 'a') ? $this->baseConvertArbitrary($id, 62, 10) : null;
        if (!$oldid || !is_numeric($oldid)) {
            $output->addWikiTextAsContent(wfMessage('surl-provide-oldid')->text());
            return;
        }

        // 리비전 조회 및 리다이렉션
        $revisionRecord = MediaWikiServices::getInstance()
            ->getRevisionLookup()
            ->getRevisionById((int)$oldid);

        if ($revisionRecord) {
            $title = Title::newFromLinkTarget($revisionRecord->getPageAsLinkTarget());
            $url = $title->getFullURL();
            $output->redirect($url);
        } else {
            $output->addWikiTextAsContent(wfMessage('surl-not-found')->text());
        }
    }

    private function isValidId(string $path, string $id): bool
    {
        return $path === 'a' && preg_match('/^[0-9a-zA-Z]+$/', $id);
    }

    private function baseConvertArbitrary(string $number, int $fromBase, int $toBase): string
    {
        $digits = self::BASE62_DIGITS;
        $length = strlen($number);
        $result = '0';

        for ($i = 0; $i < $length; $i++) {
            $digitValue = strpos($digits, $number[$i]);
            if ($digitValue === false) {
                return '0'; // Invalid character
            }

            $power = bcpow((string)$fromBase, (string)($length - $i - 1));
            $result = bcadd($result, bcmul((string)$digitValue, $power));
        }

        return $result;
    }
}
