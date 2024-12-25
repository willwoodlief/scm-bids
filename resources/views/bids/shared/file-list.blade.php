@php
/**
 * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
 * @var bool $b_edit
 */
@endphp
<table  class="table" data-order='[[ 3, "desc" ]]' id="scm-plugin-edit-files-list">
    <thead>
    <tr>

        <th>File Name</th>
        <th>File Type</th>
        <th>Added By</th>
        <th>When</th>
        @if($b_edit)
        <th></th>
        @endif

    </tr>
    </thead>
    <tbody>
    @foreach($bid->bid_files as $file)
        <tr >
            <td data-order="{{$file->bid_file_human_name}}" data-sort="{{$file->bid_file_human_name}}" >
                <a href="{{route('scm-bid.admin.bids.download_file',['bid_id'=>$bid->id,'file_id'=>$file->id])}}" style="white-space: normal">
                    <i class="bi bi-file-arrow-down"></i>
                    {{$file->bid_file_human_name}}
                </a>
            </td>

            <td data-order="{{$file->bid_file_category}}" data-sort="{{$file->bid_file_category}}">
                @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/file-icon',['bid_file'=>$file ])
                <span class="ms-1">
                    @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/file-description',['bid_file'=>$file ])
                </span>
            </td>
            <td>
                <span>
                    {{$file->file_user->getName()}}
                </span>
            </td>

            @php
                $tz_ts = \Carbon\Carbon::createFromTimestamp($bid->created_at_ts,config('app.timezone'))->getTimestamp();
            @endphp

            <td data-order="{{$tz_ts}}" data-sort="{{$tz_ts}}" >
                <span class="will-show-long-date-time" data-ts="{{$tz_ts}}"></span>
            </td>

            @if($b_edit)
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger scm-plugin-bid-remove-file-action"
                        data-url="{{route('scm-bid.admin.bids.remove_file',['bid_id'=>$bid->id,'file_id'=>$file->id])}}"
                        data-method="delete"
                        data-file_name="{{$file->bid_file_human_name}}"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            @endif

        </tr>
    @endforeach
    </tbody>
</table>

<script>
    jQuery(function($) {
        @if($b_edit)
        $('body').on('click',".scm-plugin-bid-remove-file-action",function() {

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
                    scm_do_ajax(url,method,{},
                        function() {
                            outer.remove();
                        }
                    );
                }
            })
        });
        @endif

        function make_datatable() {
            let table = $('#scm-plugin-edit-files-list').DataTable({

                'dom': 'ZBfrltip',
                buttons: [],
                searching: true,
                select: false,
                responsive: true,
                pageLength:12,
                lengthChange:false ,
                language: {
                    paginate: {
                        next: '<i class="fa-solid fa-angle-right"></i>',
                        previous: '<i class="fa-solid fa-angle-left"></i>'
                    }

                },

            });
            table.on('draw', function () {
                will_refresh_times();
            });

            table.on('responsive-display', function (/*e, datatable, row, showHide, update*/) {
                will_refresh_times();
            });
        }
        make_datatable();

    });
</script>
