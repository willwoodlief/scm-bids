<style>
    .new-contractor-tingle .tingle-modal-box {
        width: auto;
    }
</style>



<script>
    jQuery(function($){

        $(`.scm-bid-plugin-new-contractor-action`).on('click',function() {
            showContractorForm();
        });

        function scm_ajax_get_form( success) {
            const URL_TEMPLATE = `{{route('scm-bid.get_new_contactor_form')}}`;
            let url = URL_TEMPLATE;
            scm_do_ajax(url, 'get', {}, success, null);
        }


        let modal;


        function showContractorForm()
        {

            if (modal) {return;}

            function scm_ajax_create_contractor(form_data,success,fail) {
                const URL_TEMPLATE = `{{route('scm-bid.create_contractor')}}`;
                let url = URL_TEMPLATE;
                scm_do_ajax(url,'post',form_data,success,fail);
            }



            scm_ajax_get_form(
                /**
                 *  @param {object} data
                 *  @param {string} data.html
                 * */
                function(data) {
                    let edit_div = $(data.html);

                    // noinspection JSPotentiallyInvalidConstructorUsage
                    modal = new tingle.modal({
                        footer: true,
                        stickyFooter: false,
                        closeMethods: ['overlay', 'button', 'escape'],
                        closeLabel: "Close",
                        cssClass: ['new-contractor-tingle'],
                        onOpen: function () {},
                        onClose: function () {this.destroy(); modal = null},

                        beforeClose: function () {return true;}
                    });

                    modal.setContent(edit_div[0]);


                    // open modal
                    modal.open();

                    edit_div.on('submit', 'form', function (e) {

                        let that = $(this);
                        let spinner = that.closest('.scm-spinner-area').find(`.scm-spinner`);
                        spinner.show();

                        e.preventDefault();
                        var formData = new FormData(this);


                        scm_ajax_create_contractor(formData,
                            /**
                             *  @param {object} data
                             *  @param {object} data.contractor
                             * */
                            function(data) {
                                debugger;
                                let select = $(`select[name="bid_contractor_id"]`);
                                select.append(`<option selected value="${data.contractor.id}">${data.contractor.name}</option>`);
                                spinner.hide();
                                modal.close();
                            },
                            function(data) {
                                spinner.hide();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Cannot create contractor',
                                    text: data.message,
                                })
                            }
                        ) //end ajax call
                        return false;
                    })
                }) //end scm_ajax_get_form

        }//end show new resource form
    }) //end jquery ready
</script>
