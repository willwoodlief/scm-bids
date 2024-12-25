@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp


<div class="row mt-4 sp4 scm-plugin-bid-files-lightgallery" >
    @forelse($bid->get_images() as $photo)

        <a href="{{$photo->get_url()}}"
           data-src="{{$photo->get_url()}}"
           class="mb-1 col-lg-4 col-xl-4 col-sm-4 col-6"
        >
            <img src="{{$photo->get_url()}}" alt="" class="img-fluid rounded">
        </a>
    @empty
        <span>
                No photos for this bid.
            </span>
    @endforelse

</div>

<script>
    jQuery(function() {
        $('.scm-plugin-bid-files-lightgallery').lightGallery({
            loop:true,
            thumbnail:true,
            exThumbImage: 'data-exthumbimage'
        });
    });
</script>

<script src="{{asset('app-assets/vendor/lightgallery/js/lightgallery-all.min.js')}}"></script>
