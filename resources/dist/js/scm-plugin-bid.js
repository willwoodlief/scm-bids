jQuery(function($) {

    $(`body`).on('click',`.scm-plugin-bid-action`,function() {
        let spinner = findSpinner(this);

        spinner?.classList.remove("d-none");
        let url = this.dataset.url;
        let method = this.dataset.method;
        let outer = this.closest(`.scm-plugin-bid-action-parent`);
        scm_do_ajax(url,method,{},
            function(data) {
                spinner?.classList.add("d-none");
                if (data['html'] && outer) {
                    outer.innerHTML = data.html;
                }
            }
        ,
        function (err) {
                    spinner?.classList.add("d-none");
                    Swal.fire({
                        icon: 'error',
                        title: 'Bid plugin had problem',
                        text: err.message,
                    })
                }
        );

    });

    $(`body`).on('click',`.scm-plugin-bid-fail-action`,function() {
        let title = this.title;
        Swal.fire({
            title: `${title} ?`,
            icon: 'question',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                let url = this.dataset.url;
                let method = this.dataset.method;
                scm_do_ajax(url,method,{},
                    /**
                                 * @param {object} data
                                 * @param {string} data.bid_list_url
                                 * */
                    function(data) {
                        location.href = data.bid_list_url;
                    }
                );
            }
        })

    })

    $(`body`).on('click',`.scm-plugin-bid-success-action`,function() {
        let title = this.title;
        Swal.fire({
            title: `${title} ?`,
            icon: 'question',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {

                let url = this.dataset.url;
                let method = this.dataset.method;
                scm_do_ajax(url,method,{},
                    /**
                                 * @param {object} data
                                 * @param {string} data.project_edit_url
                                 * */
                    function(data) {
                        location.href = data.project_edit_url;
                    }
                );
            }
        })

    })



    /**
     * spinners can be inside the clicked area or outside of it, find the one inside first, then find the one closest to this
     * @returns {Element}
     */
    function findSpinner(dommy) {
        let spinner = dommy.querySelector(`.scm-plugin-bid-spinner`);
        if (spinner) {
            return spinner;
        } else {
            let spinner_arr =  dommy.closest(`.scm-plugin-bid-action-parent`)?.querySelectorAll(`.scm-plugin-bid-spinner`);
            if (spinner_arr.length) {
                return spinner_arr[0];
            }
        }
        return null;
    }
})


