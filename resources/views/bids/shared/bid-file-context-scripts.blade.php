@php
    /**
     * @var bool $b_edit
     */
@endphp

<script>
    jQuery(function($) {
        @if($b_edit)
        $('body').on('click', ".scm-plugin-bid-remove-file-action", function () {

            let title = this.dataset.file_name;
            Swal.fire({
                title: `Delete ${title} ?`,
                icon: 'question',
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = this.dataset.url;
                    let method = this.dataset.method;
                    let outer = this.closest(`tr`);
                    scm_do_ajax(url, method, {},
                        function () {
                            outer.remove();
                        }
                    );
                }
            })
        });
    });
    @endif
</script>
