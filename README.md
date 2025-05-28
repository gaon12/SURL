# 🔗 MediaWiki ShortURL Extension

긴 문서명을 직접 입력하기 어려울 때, 문서 편집 기록 ID(`oldid`)를 기반으로 **짧은 URL**을 생성해주는 MediaWiki 확장기능입니다.  
예:  
- 긴 기본 URL: `https://test.wiki.com/w/Pneumonoultramicroscopicsilicovolcanoconiosis`  
- 짧은 URL: `https://test.wiki.com/a/abcD1` 또는 `https://test.wiki.com/s/12345`

---

## 🖼️ 예시

| 짧은 URL       | 해석 방식        | 리디렉션 대상      |
|----------------|------------------|---------------------|
| `/s/12345`     | 숫자 ID          | `?oldid=12345`      |
| `/a/abcD1`     | base62 → `12345` | `?oldid=12345`      |

### 🔍 모달 및 버튼 UI

![ShortURL Button](README/example.png)<br>
단축 URL 생성 메뉴


![ShortURL Modal](README/example_modal.png)<br>
단축 URL 생성 결과 모달

---

## 📦 설치 방법

1. 확장기능을 MediaWiki `extensions` 폴더에 클론하거나 복사합니다:
   ```bash
   git clone https://github.com/gaon12/SURL extensions/SURL
````

2. `.htaccess` 파일을 **도메인 루트 경로**에 복사합니다.
   이미 `.htaccess` 파일이 존재하는 경우, 기존 내용 뒤에 덧붙여주세요.

3. `LocalSettings.php`에 다음 줄을 추가하여 확장기능을 활성화합니다:

   ```php
   wfLoadExtension( 'SURL' );
   ```

4. 선택적으로 다음 설정을 통해 URL 스타일을 조정할 수 있습니다:

   ```php
   $wgShortURLoldIDPath = "s";          // 숫자 ID 기반 단축 URL (예: /s/12345)
   $wgShortURLbase62OldIdPath = "a";    // base62 인코딩 URL (예: /a/abcD1)
   ```

---

## ⚙️ 동작 원리

MediaWiki는 문서가 편집될 때마다 고유 번호인 `oldid`를 부여합니다. 이 번호는 수정될 때마다 1씩 증가하며, 각 문서 편집 버전을 고유하게 식별할 수 있습니다.

이 확장기능은 이 `oldid` 값을 기반으로 두 가지 단축 URL 방식을 제공합니다:

* `/s/12345`: `oldid=12345`로 직접 이동
* `/a/abcD1`: `12345`를 base62로 인코딩한 문자열 → 디코딩 후 `?oldid=12345`로 이동

이를 통해 매우 긴 문서명 없이도 간편하게 특정 버전의 문서에 접근할 수 있습니다.

> ℹ️ `oldid` 개념이 익숙하지 않다면 [미디어위키/oldid](https://www.mediawiki.org/wiki/Help:Page_history/ko) 문서를 참고하세요.

---

## ⚠️ 주의사항

* `oldid` 값은 편집할 때마다 바뀌므로, 과거에 생성된 단축 URL이 항상 최신 문서 버전을 가리키진 않습니다.
* 하지만 단축 URL은 항상 해당 `oldid`에 해당하는 문서로 정확하게 이동합니다.
* 특수 문서, 미리보기, 편집 화면 등 `oldid`가 존재하지 않는 페이지에는 단축 URL 메뉴가 표시되지 않습니다.

---

## 🪪 라이선스

이 프로젝트는 [MIT License](LICENSE)를 따릅니다.
