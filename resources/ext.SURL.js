$(function () {
    // 1) HTML 동적 삽입 (모달)
    const modalHtml = `
    <div id="surl-modal-overlay" class="surl-modal-overlay" hidden>
      <div class="surl-modal-dialog">
        <header class="surl-modal-header">
          <span class="surl-modal-icon">🔗</span>
          <h2 class="surl-modal-title">${mw.message('surl-title').escaped()}</h2>
        </header>
        <div class="surl-modal-body">
          <input type="text" id="short-url" class="surl-modal-input" readonly>
        </div>
        <footer class="surl-modal-footer">
          <button class="surl-btn surl-btn-confirm">${mw.message('surl-copy').escaped()}</button>
          <button class="surl-btn surl-btn-cancel">${mw.message('surl-btn-close').escaped()}</button>
        </footer>
      </div>
    </div>`;
    $('body').append(modalHtml);

    // 2) 모달 엘리먼트 참조
    const $overlay = $('#surl-modal-overlay');
    const $input   = $overlay.find('#short-url');
    const $btnOk   = $overlay.find('.surl-btn-confirm');
    const $btnNo   = $overlay.find('.surl-btn-cancel');

    // 3) 토스트 함수 정의
    function showToast(message) {
        let $container = $('#surl-toast-container');
        if (!$container.length) {
            $container = $('<div id="surl-toast-container"></div>').appendTo('body');
        }
        const $toast = $('<div class="surl-toast"></div>').text(message).appendTo($container);
        setTimeout(() => { $toast.remove(); }, 3000);
    }

    // 4) 푸터에 삽입된 #surl-link 클릭 핸들러
    $('#surl-link').on('click', function (e) {
        e.preventDefault();
        $input.val( mw.config.get('surlBase62OldId') );
        $overlay.removeAttr('hidden');
    });

    // 5) 모달 닫기 함수
    function closeModal() {
        $overlay.attr('hidden', '');
    }

    // 6) 오버레이·취소 클릭 시 닫기
    $overlay.on('click', function (e) {
        if (e.target === this) closeModal();
    });
    $btnNo.on('click', closeModal);

    // 7) 복사 버튼 클릭 → 클립보드 복사 → 토스트 띄우기 → 모달 닫기
    $btnOk.on('click', function () {
        $input[0].select();
        document.execCommand('copy');
        const msg = mw.message('surl-copy-success').escaped();
        showToast(msg);
        closeModal();
    });
});
