@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp


<div class="d-flex flex-wrap mt-4 sp4 @if(count($photos = $bid->get_images())) scm-lightgallery @endif" style="align-self: center">
    @forelse($photos as $photo)

        <a href="{{$photo->getFileUrl()}}"
           data-src="{{$photo->getFileUrl()}}"
           data-exthumbimage="{{$photo->getFileUrl(thumbnail: true)}}"
           class="rounded-lg overflow-hidden m-1"
           style="width: 100px; height: 100px;"
        >
            <img src="{{$photo->getFileUrl(thumbnail: true)}}" alt="" class="img-fluid rounded">
        </a>
    @empty
        <span>
            No photos for this bid.
        </span>
    @endforelse

</div>


