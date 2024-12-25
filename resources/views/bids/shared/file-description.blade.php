@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidFile $bid_file
     */
@endphp

@if($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::IMAGE)
    Image
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::COMPRESSED)
    Zip
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::PDF)
    PDF
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::SPREADSHEET)
    Spreadsheet
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::DOCUMENT)
    Document
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::PRESENTATION)
    Presentation
@elseif($bid_file->bid_file_category === \Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::UNKNOWN)
    Unknown
@endif
