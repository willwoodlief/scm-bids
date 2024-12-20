@php
/**
 * @var \Scm\PluginBid\Models\ScmPluginBidFile[] $files
 * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
 */
@endphp
<table  class="table" data-order='[[ 3, "desc" ]]' id="scm-plugin-edit-files">
    <thead>
    <tr>

        <th>File Name</th>
        <th>File Type</th>
        <th>Added By</th>
        <th>When</th>
        <th></th>

    </tr>
    </thead>
    <tbody>
    @foreach($files as $file)
        <tr >
            <td data-order="{{$file->bid_file_human_name}}" data-sort="{{$file->bid_file_human_name}}" >
                <a href="{{route('scm-bid.admin.bids.show',['bid_id'=>$bid->id])}}">
                    {{$bid->getName()}}
                </a>

            </td>
        </tr>
@endforeach
