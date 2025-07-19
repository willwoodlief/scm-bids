@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp


<div class="d-flex flex-wrap mt-4 sp4 @if(count($photos = $bid->get_images())) scm-lightgallery @endif" >
    @forelse($photos as $photo)

        <a href="{{$photo->get_url()}}"
           data-src="{{$photo->get_url()}}"
           data-exthumbimage="{{asset($photo->getRelativePath())}}"
           class="rounded-lg overflow-hidden m-1"
           style="width: 100px; height: 100px;"
        >
            <img src="{{$photo->get_url()}}" alt="" class="img-fluid rounded">
        </a>
    @empty
        <span>
            No photos for this bid.
        </span>
    @endforelse

</div>


