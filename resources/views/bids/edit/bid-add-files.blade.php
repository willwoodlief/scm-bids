@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp



<form action="{{route('scm-bid.files.add',['single_bid'=>$bid->id])}}" method="post" enctype="multipart/form-data" id="bid-dropzone" class="dropzone">
    @csrf
    <div>
        <h4 class="text-center">Upload multiple files By dragging or clicking</h4>
    </div>
</form>
<script>
    jQuery(function() {
        new Dropzone("form#bid-dropzone", { url: "{{route('scm-bid.files.add',['single_bid'=>$bid->id])}}"});
    });
</script>
