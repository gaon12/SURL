$(function () {
    var $SURLShowLocate = $('#surl-link');

    $SURLShowLocate.detach().appendTo('.footer-info-copyright');

    $SURLShowLocate.on('click', function (e) {
        e.preventDefault();

        var surlTitle = mw.message('surl-title').text();
        var surlCopy = mw.message('surl-copy').text();
        var surlClose = mw.message('surl-btn-close').text();

        Swal.fire({
            title: surlTitle,
            html: `
				<input type="text" id="short-url" class="swal2-input" readonly>
            `,
            showCancelButton: true,
            confirmButtonText: surlCopy,
            cancelButtonText: surlClose,
            didOpen: function() {
                // Show the Base62 Old ID value in text field by default
                document.getElementById('short-url').value = mw.config.get('surlBase62OldId');
            },
            preConfirm: function() {
                var selectedUrl = document.getElementById('short-url').value;
                return { selectedUrl: selectedUrl };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var copyText = document.getElementById("short-url");
                copyText.select();
                document.execCommand("copy");
            }
        })
    })

    const container = document.getElementById('swal2-html-container');
    const targetElement = document.getElementById('surl_type_select');
  
    if (container && targetElement) {
        container.style.overflow = 'hidden';
    }
})
