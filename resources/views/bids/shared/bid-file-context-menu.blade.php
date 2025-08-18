@php
    /**  @var \Scm\PluginBid\Models\ScmPluginBidFile $bid_file */
@endphp

<div class="dropdown">
    <button
            data-dropdown-trigger
            class="btn btn-icon btn-ghost"
            aria-haspopup="true"
            aria-expanded="false"
    >
        <x-icons.more style="width: 16px; height: 16px;"/>
    </button>

    <div data-dropdown-menu class="dropdown-menu dropdown-menu-end">
        <a href="{{ $bid_file->getFileUrl() }}"
           target="_blank"
           class="dropdown-item">
            <x-icons.eye-duotone class="me-2" style="width: 16px; height: 16px;"/>
            View
        </a>


        <a href="{{ route('scm-bid.files.download',['single_bid'=>$bid_file->owning_bid_id,'bid_file'=>$bid_file]) }}"
           target="_blank"
           download="{{ $bid_file->getFileName() }}"
           class="dropdown-item">
            <x-icons.filedownload-duotone class="me-2" style="width: 16px; height: 16px;"/>
            Download
        </a>
        <div class="divider"></div>
        <form method="POST"
              class="will-ask-first-before-deleting-file"
              action="{{ route('scm-bid.files.remove', ['single_bid'=>$bid_file->owning_bid_id,'bid_file'=>$bid_file]) }}"
              data-file_name="{{ $bid_file->getFileHumanName() }}">
            @csrf @method('delete')
            <button type="submit" class="dropdown-item text-danger">
                <x-icons.trash-duotone class="me-2" style="width: 16px; height: 16px;"/>
                Delete
            </button>
        </form>
    </div>
</div>
