@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidFile $bid_file
     */
@endphp

@if($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::IMAGE)
    <i class="bi bi-image"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::COMPRESSED)
    <i class="bi bi-file-zip"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::PDF)
    <i class="bi bi-file-pdf"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::SPREADSHEET)
    <i class="bi bi-file-spreadsheet"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::DOCUMENT)
    <i class="bi bi-file-word"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::PRESENTATION)
    <i class="bi bi-file-slides"></i>
@elseif($bid_file->bid_file_category === \App\Models\Enums\TypeOfAcceptedFile::UNKNOWN)
    <i class="bi bi-question-square"></i>
@endif
